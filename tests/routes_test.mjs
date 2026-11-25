import assert from 'node:assert/strict';
import { mkdtempSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { resolve, join } from 'node:path';
import { PHP, PHPRequestHandler, FileLockManagerInMemory, setPhpIniEntries } from '@php-wasm/universal';
import { loadNodeRuntime, useHostFilesystem } from '@php-wasm/node';
const root=resolve('.');const dir=mkdtempSync(join(tmpdir(),'ms-routes-'));
const env={MS_DB_DRIVER:'sqlite',MS_SQLITE_PATH:join(dir,'test.sqlite'),MS_STORAGE_PATH:dir,MS_SESSION_PATH:join(dir,'sessions'),MS_RATE_LIMIT_PATH:join(dir,'limits'),MS_CONFIG_FILE:join(dir,'no-config.php'),MS_APP_URL:'http://localhost',MS_ADMIN_USER:'',MS_ADMIN_PASSWORD:'',MS_ADMIN_PASSWORD_HASH:''};
let php;
try {
 php=new PHP(await loadNodeRuntime('8.3',{fileLockManager:new FileLockManagerInMemory(),emscriptenOptions:{processId:1,ENV:env}}));useHostFilesystem(php);
 await setPhpIniEntries(php,{upload_max_filesize:'15M',post_max_size:'20M',display_errors:'0'});
 const handler=new PHPRequestHandler({php,documentRoot:join(root,'public'),absoluteUrl:'http://localhost',getFileNotFoundAction:()=>({type:'internal-redirect',uri:'/index.php'})});
 for(const [url,status] of [['/es',200],['/es/blog?q=unavailable',200],['/es/lab',200],['/es/privacy',200],['/portfolio/',200],['/portfolio/cv',200],['/cv',200],['/admin',302],['/admin/login',200],['/blog/app/config.php',404],['/static/../../blog/app/config.php',404],['/portfolio/bootstrap.php',404],['/missing-page',404],['/es/lab/download/9999',404],['/static/editorial.css',200],['/cv/site.js',200]]) {
  const response=await handler.request({url,method:'GET'});assert.equal(response.httpStatusCode,status,`${url} status; ${response.errors}`);assert.ok(!response.errors,`${url} PHP error ${response.errors}`);console.log(`PASS ${status} ${url}`);
 }
 console.log('16 route checks passed on a fresh isolated database.');
} finally {php?.exit();rmSync(dir,{recursive:true,force:true});}
