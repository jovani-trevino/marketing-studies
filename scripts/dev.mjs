import { spawn, spawnSync } from 'node:child_process';
import { existsSync, mkdirSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
const root=resolve(dirname(fileURLToPath(import.meta.url)),'..');
if(existsSync(resolve(root,'.env')))process.loadEnvFile(resolve(root,'.env'));
const args=process.argv.slice(2);
const option=(name,fallback)=>{const i=args.indexOf(name);return i>=0?args[i+1]:fallback;};
const port=String(option('--port',process.env.PORT || '4173'));
const host=option('--host',process.env.HOST || '127.0.0.1');
const php=process.env.MS_PHP_BINARY || (existsSync(resolve(root,'.runtime/php'))?resolve(root,'.runtime/php'):'php');
const storage=resolve(root,'.preview');
mkdirSync(storage,{recursive:true});
const env={...process.env,MS_DB_DRIVER:process.env.MS_DB_DRIVER || 'sqlite',MS_SQLITE_PATH:process.env.MS_SQLITE_PATH || resolve(storage,'blog.sqlite'),MS_STORAGE_PATH:process.env.MS_STORAGE_PATH || storage,MS_SESSION_PATH:process.env.MS_SESSION_PATH || resolve(storage,'sessions'),MS_RATE_LIMIT_PATH:process.env.MS_RATE_LIMIT_PATH || resolve(storage,'rate-limits'),MS_APP_URL:process.env.MS_APP_URL || `http://127.0.0.1:${port}`};
if(process.env.MS_DEV_WASM==='1' || spawnSync(php,['-v'],{stdio:'ignore'}).error) {
  const {startWasmPreview}=await import('./dev-wasm.mjs');
  await startWasmPreview({root,host,port,env});
} else {
  const child=spawn(php,['-S',`${host}:${port}`,'-t',resolve(root,'public'),resolve(root,'public/router.php')],{cwd:root,env,stdio:'inherit'});
  child.on('error',error=>{console.error(`No se pudo iniciar PHP: ${error.message}`);process.exitCode=1;});
  child.on('exit',code=>process.exit(code || 0));
  for(const signal of ['SIGINT','SIGTERM'])process.on(signal,()=>child.kill(signal));
}
