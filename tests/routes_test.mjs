import assert from 'node:assert/strict';
import { mkdtempSync, rmSync, readFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { resolve, join as hostJoin, posix } from 'node:path';
import { PHP, PHPRequestHandler, FileLockManagerInMemory, setPhpIniEntries } from '@php-wasm/universal';
import { loadNodeRuntime, createNodeFsMountHandler } from '@php-wasm/node';
// Explicit mounts keep host drive letters out of the POSIX WASM filesystem.
const hostRoot=resolve('.');const hostDir=mkdtempSync(hostJoin(tmpdir(),'ms-routes-'));
const root='/workspace';const dir='/fixtures';const join=posix.join;
const env={MS_UPLOADS_PATH:join(dir,'uploads'),MS_DB_DRIVER:'sqlite',MS_SQLITE_PATH:join(dir,'test.sqlite'),MS_STORAGE_PATH:dir,MS_SESSION_PATH:join(dir,'sessions'),MS_RATE_LIMIT_PATH:join(dir,'limits'),MS_CONFIG_FILE:join(dir,'no-config.php'),MS_APP_URL:'http://localhost',MS_ADMIN_USER:'',MS_ADMIN_PASSWORD:'',MS_ADMIN_PASSWORD_HASH:''};
let php;
try {
 php=new PHP(await loadNodeRuntime('8.3',{fileLockManager:new FileLockManagerInMemory(),emscriptenOptions:{processId:1,ENV:env}}));
 php.mkdirTree(root);php.mkdirTree(dir);
 await php.mount(root,createNodeFsMountHandler(hostRoot));
 await php.mount(dir,createNodeFsMountHandler(hostDir));
 php.chdir(root);
 await setPhpIniEntries(php,{upload_max_filesize:'15M',post_max_size:'20M',display_errors:'0'});
 const handler=new PHPRequestHandler({php,documentRoot:join(root,'public'),absoluteUrl:'http://localhost',getFileNotFoundAction:()=>({type:'internal-redirect',uri:'/index.php'})});
 php.mkdirTree(join(dir,'uploads'));php.writeFile(join(dir,'uploads/persistent.csv'),'name,value\nexisting,1\n');
 const persistent=await handler.request({url:'/uploads/persistent.csv',method:'GET'});assert.equal(persistent.httpStatusCode,200);assert.equal(persistent.text,'name,value\nexisting,1\n');
 let checks=1;
 for(const [url,status] of [['/etica',200],['/ethics',200],['/etica/documento.pdf',200],['/casos-anonimos',200],['/anonymous-case-studies',200],['/jovani-trevino',301],['/Jovani/casos/demanda-local',301],['/admin/cv',302],['/es',200],['/es/blog?q=unavailable',200],['/es/lab',200],['/es/privacy',200],['/jovani',200],['/es/jovani',200],['/about',200],['/es/about',200],['/portfolio/',200],['/portfolio/cv',200],['/cv',200],['/admin',302],['/admin/login',200],['/blog/app/config.php',404],['/static/../../blog/app/config.php',404],['/portfolio/bootstrap.php',404],['/missing-page',404],['/es/lab/download/9999',404],['/static/editorial.css',200],['/cv/site.js',200]]) {
  const response=await handler.request({url,method:'GET'});assert.equal(response.httpStatusCode,status,`${url} status; ${response.errors}`);assert.ok(!response.errors,`${url} PHP error ${response.errors}`);console.log(`PASS ${status} ${url}`);
  if (/(?:jovani|about)$/.test(url)) {
   assert.equal((response.text.match(/<h1\b/g)??[]).length,1,`${url} has one main heading`);
   assert.ok(response.text.includes('href="/portfolio/"'),`${url} links to the unified portfolio`);
  }
  checks++;
 }
 for (const [url,status] of [['/portfolio/casos/demanda-local',200],['/portfolio/casos/portada-sencillo-musical',200],['/portfolio/casos/manual-identidad-institucional',200],['/portfolio/casos/programa-locucion-comunicacion',200],['/portfolio/casos/canales-distribucion-franquicia',200],['/portfolio/casos/experiencia-web/',200],['/portfolio/casos/no-existe',404],['/portfolio/case-detail.php',404],['/portfolio/?caso=demanda-local',200],['/portfolio/?caso=unknown',404],['/portfolio/?caso[]=demanda-local',404],['/portfolio/assets/downloads/jovani-cv.pdf',200],['/admin/professional',302]]) {
  const response=await handler.request({url,method:'GET'});
  assert.equal(response.httpStatusCode,status,url);assert.equal(response.errors,'',url);
  if(url.includes('caso=demanda-local')) {
   assert.ok(response.text.includes('73') && response.text.includes('Alcance de la evidencia'));
   assert.ok(!/Esquivel|Treviñosa|Plomería García|Heaven|iRealty|Publilum/i.test(response.text),'Client identity leaked in public case');
  }
  if(status===200 && url.includes('/casos/')) {
   const canonical=url.replace(/\/$/,'');
   assert.ok(response.text.includes(`rel="canonical" href="http://localhost${canonical}"`),'Canonical uses the dedicated case route');
   assert.equal((response.text.match(/<h1\b/g)??[]).length,1,'One case heading');
   for(const section of ['contexto','proceso','evidencia','aprendizaje']) assert.ok(response.text.includes(`id="${section}"`),`Missing case section ${section}`);
   assert.ok(response.text.includes('href="/portfolio/casos/'),'Related cases use the unified mount');
  }
  console.log(`PASS professional route ${url}`);checks++;
 }
 // Native video playback and seeking work through the unified PHP mount too.
 const videoPath='/portfolio/assets/case-work/marca-en-movimiento/logotipo-3d.mp4';
 const videoBytes=readFileSync(hostJoin(hostRoot,'Jovani/assets/case-work/marca-en-movimiento/logotipo-3d.mp4'));
 for(const [range,status,expected] of [
  ['bytes=0-31',206,videoBytes.subarray(0,32)],
  ['bytes=-16',206,videoBytes.subarray(-16)],
  [`bytes=${videoBytes.length-10}-`,206,videoBytes.subarray(-10)],
  [`bytes=${videoBytes.length}-`,416,Buffer.alloc(0)],
  ['bytes=50-10',416,Buffer.alloc(0)],
  ['bytes=-0',416,Buffer.alloc(0)]
 ]) {
  const response=await handler.request({url:videoPath,method:'GET',headers:{Range:range}});
  assert.equal(response.httpStatusCode,status,range);
  assert.equal(response.errors,'',range);
  assert.deepEqual(Buffer.from(response.bytes),expected,range);
  checks++;
 }
 const videoHead=await handler.request({url:videoPath,method:'HEAD'});
 assert.equal(videoHead.httpStatusCode,200); assert.equal(videoHead.bytes.length,0); checks++;
 const motionPage=await handler.request({url:'/portfolio/casos/marca-en-movimiento',method:'GET'});
 assert.equal(motionPage.httpStatusCode,200);
 assert.ok(motionPage.text.includes('<video controls playsinline preload="none"'));
 assert.ok(motionPage.text.includes('id="entregables"'));
 if (motionPage.text.includes('href="#galeria"')) assert.ok(motionPage.text.includes('id="galeria"'),'Gallery link has a target');
 assert.ok(!motionPage.text.includes('representación conceptual anonimizada'),'Original is not labelled a reconstruction');
 checks++;
 console.log('PASS original gallery, video ranges, invalid ranges and HEAD');
 // The historical blog can live at /blog or inside a larger site directory.
 for (const path of ['/cv/brand.css','/cv/brand-theme.js']) {
  const response=await handler.request({url:path,method:'GET'});assert.equal(response.httpStatusCode,200,path);assert.equal(response.errors,'',path);checks++;
 }
 for (const [scriptName,prefix] of [['/index.php',''],['/blog/public/index.php',''],['/sub/blog/public/index.php','/sub']]) {
  const response=await php.run({code:`<?php require ${JSON.stringify(join(root,'blog/app/helpers.php'))}; $_SERVER['SCRIPT_NAME']=${JSON.stringify(scriptName)}; echo json_encode([brand_asset_url('brand.css'),brand_asset_url('brand-theme.js')]);`});
  assert.equal(response.errors,'');const paths=JSON.parse(response.text).map(value=>value.split('?')[0]);
  assert.deepEqual(paths,[`${prefix}/cv/brand.css`,`${prefix}/cv/brand-theme.js`]);checks++;
 }
 for (const [scriptName,portfolio,cv] of [
  ['/index.php','/portfolio/','/portfolio/cv'],
  ['/blog/index.php','/Jovani/','/Jovani/cv/'],
  ['/blog/public/index.php','/Jovani/','/Jovani/cv/'],
  ['/sub/blog/public/index.php','/sub/Jovani/','/sub/Jovani/cv/'],
 ]) {
  const response=await php.run({code:`<?php require ${JSON.stringify(join(root,'blog/app/helpers.php'))}; $_SERVER['SCRIPT_NAME']=${JSON.stringify(scriptName)}; echo json_encode([portfolio_url(), portfolio_url('cv')]);`});
  assert.equal(response.errors,'',`${scriptName} helper error`);
  assert.deepEqual(JSON.parse(response.text),[portfolio,cv],`${scriptName} sibling links`);
  console.log(`PASS portfolio/CV links from ${scriptName}`);checks++;
 }
 for (const [requestUri,scriptName,portfolio] of [
  ['/blog/jovani','/blog/public/index.php','/Jovani/'],
  ['/blog/es/jovani','/blog/public/index.php','/Jovani/'],
  ['/sub/blog/es/jovani','/sub/blog/public/index.php','/sub/Jovani/'],
 ]) {
  const response=await php.run({scriptPath:join(root,'blog/public/index.php'),relativeUri:requestUri,method:'GET',$_SERVER:{SCRIPT_NAME:scriptName,REQUEST_URI:requestUri,HTTP_HOST:'localhost'}});
  assert.equal(response.httpStatusCode,200,`${requestUri} status`);
  assert.equal(response.errors,'',`${requestUri} PHP error`);
  assert.equal((response.text.match(/<h1\b/g)??[]).length,1,`${requestUri} has one main heading`);
  assert.ok(response.text.includes(`href="${portfolio}"`),`${requestUri} links to the sibling portfolio`);
  assert.ok(!response.text.includes('/blog/portfolio/'),`${requestUri} avoids nonexistent portfolio route`);
  console.log(`PASS legacy profile mount ${requestUri}`);checks++;
 }
 for (const prefix of ['', '/sub']) {
  const requestUri=`${prefix}/Jovani/cv/`;
  const response=await php.run({scriptPath:join(root,'Jovani/cv/index.php'),relativeUri:requestUri,method:'GET',$_SERVER:{SCRIPT_NAME:`${requestUri}index.php`,REQUEST_URI:requestUri,HTTP_HOST:'localhost'}});
  assert.equal(response.httpStatusCode,200,`${requestUri} status`);
  assert.equal(response.errors,'',`${requestUri} PHP error`);
  for (const expected of [`href="${prefix}/Jovani/"`,`href="${prefix}/blog/"`,`href="${prefix}/cv/site.css"`,`src="${prefix}/cv/site.js"`]) {
   assert.ok(response.text.includes(expected),`${requestUri} contains ${expected}`);
  }
  console.log(`PASS legacy CV navigation ${requestUri}`);checks++;
 }
 // Return destinations are chosen from known page names, never a posted URL.
 // These controller requests use only the temporary DB and disable campaigns.
 const phpString=(value)=>`'${String(value).replaceAll('\\','\\\\').replaceAll("'","\\'")}'`;
 for (const [form,controller] of [['newsletter','subscribe_newsletter'],['contact','submit_contact']]) {
  for (const scenario of [
   {label:'expired root',script:'/index.php',locale:'en',source:'author-profile',token:false,target:'/jovani'},
   {label:'expired legacy',script:'/blog/public/index.php',locale:'es',source:'author-profile',token:false,target:'/blog/es/jovani'},
   {label:'unrecognized source',script:'/blog/public/index.php',locale:'es',source:'https://outside.example.test',token:false,target:'/blog/es/'},
   {label:'invalid fields',script:'/blog/public/index.php',locale:'es',source:'author-profile',token:true,invalid:true,target:'/blog/es/jovani'},
   {label:'valid fields',script:'/blog/public/index.php',locale:'es',source:'author-profile',token:true,target:'/blog/es/jovani'},
  ]) {
   const fields={source:scenario.source,csrf_token:scenario.token?'a'.repeat(64):'expired',email:scenario.invalid?'invalid':`${form}@example.test`,name:'Route fixture',topic:'Local test',message:'Temporary fixture only.'};
   const response=await php.run({code:`<?php
    require ${phpString(join(root,'blog/app/i18n.php'))};
    require ${phpString(join(root,'blog/app/helpers.php'))};
    require ${phpString(join(root,'blog/app/models.php'))};
    require ${phpString(join(root,'blog/app/controllers.php'))};
    $_SERVER['SCRIPT_NAME']=${phpString(scenario.script)};
    $_SERVER['HTTP_HOST']='localhost'; $_SERVER['REQUEST_METHOD']='POST';
    $_SESSION=['csrf_token'=>str_repeat('a',64)];
    $_POST=json_decode(${phpString(JSON.stringify(fields))},true);
    $GLOBALS['locale']=${phpString(scenario.locale)};
    ensure_runtime_schema(); save_setting('newsletter_enabled','0');
    $GLOBALS['site']=settings(); ${controller}();
   `});
   assert.equal(response.errors,'',`${form} ${scenario.label} PHP error`);
   assert.equal(response.httpStatusCode,303,`${form} ${scenario.label} redirects with GET`);
   assert.deepEqual(response.headers.location,[`${scenario.target}#${form}`],`${form} ${scenario.label} return destination`);
   console.log(`PASS ${form} return: ${scenario.label}`);checks++;
  }
 }
 console.log(`${checks} route and mount checks passed on a fresh isolated database.`);
} finally {php?.exit();rmSync(hostDir,{recursive:true,force:true});}
