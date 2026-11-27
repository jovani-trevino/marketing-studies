import assert from 'node:assert/strict';
import {spawn, spawnSync} from 'node:child_process';
import {mkdtempSync, rmSync} from 'node:fs';
import {tmpdir} from 'node:os';
import {join} from 'node:path';
import {createServer} from 'node:net';
import {once} from 'node:events';
import {root, phpBinary} from '../scripts/php-runner.mjs';

const dir=mkdtempSync(join(tmpdir(),'ms-cms-routes-'));
const listener=createServer(); listener.listen(0,'127.0.0.1'); await once(listener,'listening');
const port=listener.address().port; await new Promise(resolve=>listener.close(resolve));
const origin=`http://127.0.0.1:${port}`;
const env={...process.env,MS_DB_DRIVER:'sqlite',MS_SQLITE_PATH:join(dir,'test.sqlite'),MS_STORAGE_PATH:dir,MS_SESSION_PATH:join(dir,'sessions'),MS_RATE_LIMIT_PATH:join(dir,'limits'),MS_CONFIG_FILE:join(dir,'none.php'),MS_APP_URL:origin,MS_ADMIN_USER:'',MS_ADMIN_PASSWORD:'',MS_ADMIN_PASSWORD_HASH:''};
const phpString=value=>`'${String(value).replaceAll('\\','/').replaceAll("'","\\'")}'`;
const bootstrap=`require ${phpString(join(root,'blog/app/i18n.php'))}; require ${phpString(join(root,'blog/app/helpers.php'))}; require ${phpString(join(root,'blog/app/models.php'))}; require ${phpString(join(root,'blog/app/controllers.php'))}; ensure_runtime_schema();`;
const seed=spawnSync(phpBinary,['-r',bootstrap+`save_setting('newsletter_enabled','0');create_initial_admin('fixture-editor','Local fixture password only!');`],{env,encoding:'utf8'});
assert.equal(seed.status,0,seed.stderr);
const server=spawn(phpBinary,['-S',`127.0.0.1:${port}`,'-t','public','public/router.php'],{cwd:root,env,stdio:['ignore','ignore','pipe']});
let logs='';server.stderr.on('data',chunk=>{logs+=chunk;});
let cookie='',checks=0;
const request=async(path, fields)=>{
 const response=await fetch(origin+path,{method:fields?'POST':'GET',headers:{...(cookie?{Cookie:cookie}:{}),...(fields?{'Content-Type':'application/x-www-form-urlencoded',Origin:origin}:{})},body:fields?new URLSearchParams(fields):undefined,redirect:'manual'});
 const cookies=response.headers.getSetCookie();if(cookies.length) cookie=cookies.map(value=>value.split(';')[0]).join('; ');
 return {status:response.status,html:await response.text(),location:response.headers.get('location')};
};
const token=html=>html.match(/name="csrf_token" value="([^"]+)"/)?.[1];
const pass=label=>{checks++;console.log('PASS CMS '+label);};
try {
 for(let i=0;i<80;i++){try{await fetch(origin+'/admin/login');break;}catch{await new Promise(resolve=>setTimeout(resolve,50));}}
 assert.equal((await request('/admin/seo')).status,302);pass('SEO requires authentication');
 const login=await request('/admin/login');
 const signed=await request('/admin/login',{username:'fixture-editor',password:'Local fixture password only!',csrf_token:token(login.html)});
 assert.equal(signed.status,303);pass('login');
 for(const route of ['/admin','/admin/posts','/admin/posts/new','/admin/seo','/admin/categories','/admin/authors','/admin/comments','/admin/contacts','/admin/newsletter','/admin/lab-resources','/admin/settings','/admin/professional']) {
  const response=await request(route);assert.equal(response.status,200,route);assert.match(response.html,/name="robots" content="noindex,nofollow"/);assert.match(response.html,/admin-studio.css/);pass(route);
 }
 const editor=await request('/admin/posts/new');
 const fields={csrf_token:token(editor.html),title:'A measurable strategy',excerpt:'',content_html:'<h2>Learn from evidence</h2><p>Start with a clear question. Define your metric and document what you learn before you publish.</p>',content_markdown:'',status:'draft',seo_title:'',seo_description:''};
 const saved=await request('/admin/posts/new',fields);assert.equal(saved.status,303);pass('create draft with automatic excerpt');
 let edit=await request('/admin/posts/1/edit');
 assert.match(edit.html,/Start with a clear question/);assert.match(edit.html,/value="a-measurable-strategy"/);
 assert.ok(!(await request('/sitemap.xml')).html.includes('a-measurable-strategy'));pass('draft excluded');
 fields.csrf_token=token(edit.html);fields.status='published';fields.title='A better title';
 fields['translations[es][title]']='Una estrategia medible';fields['translations[es][content_html]']='<p>Aprender con evidencia para mejorar las decisiones de marketing.</p>';
 const published=await request('/admin/posts/1/edit',fields);assert.equal(published.status,303);pass('publish and preserve URL');
 const sitemap=await request('/sitemap.xml');assert.match(sitemap.html,/a-measurable-strategy/);assert.match(sitemap.html,/<lastmod>/);
 const publicPath=new URL(sitemap.html.match(/<loc>([^<]*a-measurable-strategy)<\/loc>/)[1]).pathname;
 const article=await request(publicPath+'?utm_source=fixture');assert.equal(article.status,200);
 const schema=JSON.parse(article.html.match(/<script type="application\/ld\+json">([\s\S]*?)<\/script>/)[1]);
 assert.equal(schema.headline,'A better title');assert.equal(schema.url,origin+publicPath);assert.equal(schema.author['@type'],'Person');pass('canonical and structured article');
 edit=await request('/admin/posts/1/edit');fields.csrf_token=token(edit.html);fields.seo_title='Custom search title';fields.seo_description='A custom search description.';
 assert.equal((await request('/admin/posts/1/edit',fields)).status,303);
 const custom=await request(publicPath);assert.match(custom.html,/<title>Custom search title \| Marketing Studies<\/title>/);assert.match(custom.html,/name="description" content="A custom search description\."/);pass('custom metadata');
 const search=await request('/blog?q=strategy');assert.match(search.html,/name="robots" content="noindex,follow"/);pass('search noindex');
 edit=await request('/admin/posts/1/edit');fields.csrf_token=token(edit.html);fields.status='draft';
 assert.equal((await request('/admin/posts/1/edit',fields)).status,303);assert.equal((await request(publicPath)).status,404);assert.ok(!(await request('/sitemap.xml')).html.includes('a-measurable-strategy'));pass('unpublish removes public article and sitemap entry');
 const caseEditor=await request('/admin/posts/new?category=casos-de-estudio');
 assert.match(caseEditor.html, /Un nuevo caso/);
 const caseCategory=caseEditor.html.match(/<option value="(\d+)"\s+selected[^>]*>Case Studies<\/option>/)?.[1];
 assert.ok(caseCategory, 'Case category must be preselected even when labels are in English');
 assert.match(caseEditor.html, /href="\/admin\/case-studies" class="btn btn-outline-dark/);
 const caseSaved=await request('/admin/posts/new?category=casos-de-estudio', {...fields, csrf_token:token(caseEditor.html),title:'Case workflow fixture',category_id:caseCategory,status:'draft'});
 assert.equal(caseSaved.location,'/admin/case-studies');
 const cases=await request('/admin/case-studies');assert.match(cases.html,/Case workflow fixture/);
 const caseId=cases.html.match(/href="\/admin\/posts\/(\d+)\/edit"[^>]*>Case workflow fixture/)?.[1];
 assert.ok(caseId);assert.match((await request(`/admin/posts/${caseId}/edit`)).html,/Editar caso/);pass('case creation, category, edit and return context');
 const populated=spawnSync(phpBinary,['-r',bootstrap+`
 for ($i=1; $i<=65; $i++) {
  $date='2026-01-01T00:00:00+00:00';
  db_run('INSERT INTO comments(post_id,author_name,author_email,body,rating,status,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?)',[1,'Reader '.$i,'reader'.$i.'@example.test','Comment '.$i,5,'pending',$date,$date]);
  db_run('INSERT INTO lab_resources(title,slug,description,download_url,status,created_at,updated_at) VALUES(?,?,?,?,?,?,?)',['Material '.$i,'material-'.$i,'Description '.$i,'/fixture.pdf','draft',$date,$date]);
 }
 for ($i=1; $i<=305; $i++) db_run('INSERT INTO newsletter_subscribers(email,status,created_at) VALUES(?,?,?)',['person'.$i.'@example.test','unsubscribed','2026-01-01T00:00:00+00:00']);
 db_run('INSERT INTO contact_messages(name,email,message,status,created_at) VALUES(?,?,?,?,?)',['Reader','reader@example.test','A request','new','2026-01-01T00:00:00+00:00']);
 `],{env,encoding:'utf8'});assert.equal(populated.status,0,populated.stderr);
 for (const [route,query,expected] of [['comments','Comment 1','Comment 1'],['lab-resources','Material 1','Material 1'],['newsletter','person1@','person1@example.test'],['seo','A better','A better title']]) {
  const found=await request(`/admin/${route}?q=${encodeURIComponent(query)}`);assert.equal(found.status,200);assert.ok(found.html.includes(expected));
  const empty=await request(`/admin/${route}?q=NoSuchFixture`);assert.equal(empty.status,200);assert.match(empty.html,/0–0 de 0 resultados/);
  const malformed=await request(`/admin/${route}?q[]=bad&status[]=bad&page[]=bad`);assert.equal(malformed.status,200);
  pass(route+' search, empty results and malformed query');
 }
 for (const route of ['comments','lab-resources']) {
  const page2=await request(`/admin/${route}?page=2`);assert.match(page2.html,/31–60 de 65 resultados/);
  assert.match((await request(`/admin/${route}?page=9999`)).html,/61–65 de 65 resultados/);pass(route+' pagination beyond first page and bounds');
 }
 assert.match((await request('/admin/newsletter?page=11')).html,/301–305 de 305 resultados/);pass('all subscribers accessible beyond former 300-row limit');
 for (const [route,status] of [['comments/1/status','approved'],['newsletter/subscribers/1/status','active'],['contacts/1/status','read']]) {
  let page=await request('/admin/comments');const csrf=token(page.html);
  assert.equal((await request('/admin/'+route,{status,csrf_token:'invalid'})).status,403);
  assert.equal((await request('/admin/'+route,{status:'invalid',csrf_token:csrf})).status,422);
  assert.equal((await request('/admin/'+route.replace('/1/','/999999/'),{status,csrf_token:csrf})).status,404);
  const changed=await request('/admin/'+route,{status,csrf_token:csrf,return_query:'q=Reader&status=pending&page=2&redirect=https://example.test'});
  assert.equal(changed.status,303);assert.match(changed.location,/\?q=Reader&status=pending&page=2$/);assert.ok(!changed.location.includes('example.test'));pass(route+' CSRF, invalid status, missing record and safe return');
 }
 const invalidResource=await request('/admin/lab-resources/new',{csrf_token:token((await request('/admin/lab-resources/new')).html),title:'Keep this resource',description:'Keep my text',status:'published',download_url:'javascript:bad()'});
 assert.equal(invalidResource.status,422);assert.match(invalidResource.html,/Keep this resource/);assert.match(invalidResource.html,/Keep my text/);pass('resource validation preserves entered content');
 const upload=new FormData();
 for(const [key,value] of Object.entries({csrf_token:token(invalidResource.html),title:'Rejected upload fixture',description:'This text must survive',status:'draft',download_url:'/existing.pdf'})) upload.set(key,value);
 upload.set('download_file',new Blob(['not an allowed download'],{type:'application/octet-stream'}),'fixture.exe');
 const uploadResponse=await fetch(origin+'/admin/lab-resources/new',{method:'POST',headers:{Cookie:cookie,Origin:origin},body:upload,redirect:'manual'});
 assert.equal(uploadResponse.status,422);const uploadHtml=await uploadResponse.text();assert.match(uploadHtml,/Rejected upload fixture/);assert.match(uploadHtml,/This text must survive/);assert.match(uploadHtml,/value="\/existing.pdf"/);pass('rejected file upload retains the resource form');
 const deletionToken=token((await request('/admin/case-studies')).html);
 assert.equal((await request(`/admin/posts/${caseId}/delete`,{csrf_token:'invalid'})).status,403);
 assert.equal((await request(`/admin/posts/${caseId}/delete`,{csrf_token:deletionToken})).location,'/admin/case-studies');
 assert.equal((await request(`/admin/posts/${caseId}/edit`)).status,404);pass('case deletion keeps module context and requires CSRF');
 const professional=await request('/admin/professional');
 const task=professional.html.match(/name="task" value="([^"]+)"/)[1];
 assert.equal((await request('/admin/professional',{csrf_token:token(professional.html),task,status:'working'})).location,'/admin/professional#progress-'+task);pass('professional progress returns to the edited row');
 for (const route of ['authors','categories']) {
  assert.match((await request(`/admin/${route}?q=NoSuchFixture`)).html,/0–0 de 0 resultados/);pass(route+' filtered empty state');
 }
 assert.ok(!/PHP (?:Warning|Fatal|Parse|Deprecated)/.test(logs),logs);
 console.log(`${checks} authenticated CMS route checks passed; temporary database and newsletter disabled.`);
} finally {
 server.kill();await once(server,'exit');rmSync(dir,{recursive:true,force:true});
}
