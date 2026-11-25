// Development only: PHP 8.3 runs through the WordPress Playground runtime when
// native PHP is unavailable. Production uses PHP-FPM/Apache, never this server.
import http from 'node:http';
import { resolve } from 'node:path';
import { PHP, PHPRequestHandler, FileLockManagerInMemory, setPhpIniEntries } from '@php-wasm/universal';
import { loadNodeRuntime, useHostFilesystem } from '@php-wasm/node';

export async function startWasmPreview({root,host,port,env}) {
  const runtimeEnv=Object.fromEntries(Object.entries(env).filter(([key])=>key.startsWith('MS_')||['TZ','PATH','TMPDIR'].includes(key)));
  const php=new PHP(await loadNodeRuntime('8.3',{fileLockManager:new FileLockManagerInMemory(),emscriptenOptions:{processId:1,ENV:runtimeEnv}}));
  useHostFilesystem(php);
  await setPhpIniEntries(php, {upload_max_filesize:'15M', post_max_size:'20M', memory_limit:'256M', display_errors:'0'});
  const handler=new PHPRequestHandler({php,documentRoot:resolve(root,'public'),absoluteUrl:env.MS_APP_URL,cookieStore:false,getFileNotFoundAction:()=>({type:'internal-redirect',uri:'/index.php'})});
  let queue=Promise.resolve();
  const server=http.createServer((request,response)=>{
    const work=async()=>{
      try {
        const chunks=[];let size=0;
        for await(const chunk of request){size+=chunk.length;if(size>20*1024*1024){response.writeHead(413);response.end('Archivo demasiado grande.');return;}chunks.push(chunk);}
        const result=await handler.request({url:request.url,method:request.method,headers:Object.fromEntries(Object.entries(request.headers).map(([k,v])=>[k,Array.isArray(v)?v.join(', '):v??''])),body:chunks.length?Buffer.concat(chunks):undefined});
        response.writeHead(result.httpStatusCode,result.headers);
        response.end(request.method==='HEAD'?undefined:Buffer.from(result.bytes));
        if(result.errors)console.error(result.errors);
      } catch(error) {console.error(error);if(!response.headersSent)response.writeHead(500,{'Content-Type':'text/plain; charset=utf-8'});response.end('No se pudo preparar la vista local. Revisa la consola.');}
    };
    queue=queue.then(work,work);
  });
  server.listen(Number(port),host,()=>console.log(`Marketing Studies · PHP 8.3 (desarrollo) · http://${host}:${port}`));
  for(const signal of ['SIGINT','SIGTERM'])process.on(signal,()=>server.close(()=>process.exit(0)));
  return server;
}
