import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import vm from 'node:vm';

const source=readFileSync(new URL('../blog/public/static/admin-workspace.js',import.meta.url),'utf8');
function fixture() {
    const form=new EventTarget();
    let note;
    form.append=value=>{note=value;};
    const document=new EventTarget();
    document.createElement=()=>({setAttribute(){},classList:{add(){}},textContent:''});
    document.querySelectorAll=selector=>selector==='form[data-admin-unsaved]'?[form]:[];
    const window=new EventTarget();
    const jobs=[];
    vm.runInNewContext(source,{document,window,CustomEvent,queueMicrotask:job=>jobs.push(job)});
    document.dispatchEvent(new Event('DOMContentLoaded'));
    const unload=()=>{const event=new Event('beforeunload',{cancelable:true});window.dispatchEvent(event);return event.defaultPrevented;};
    return {form,window,unload,note:()=>note.textContent,flush:()=>{while(jobs.length) jobs.shift()();}};
}
test('pristine forms can navigate; edited rich content asks before losing work',()=>{
    const app=fixture();assert.equal(app.unload(),false);
    app.form.dispatchEvent(new CustomEvent('admin:content-change'));
    assert.equal(app.unload(),true);assert.equal(app.note(),'Tienes cambios sin guardar.');
});
test('an upload or validation that cancels submission keeps the unsaved warning',()=>{
    const app=fixture();app.form.dispatchEvent(new CustomEvent('admin:content-change'));
    app.form.addEventListener('submit',event=>event.preventDefault());
    app.form.dispatchEvent(new Event('submit',{cancelable:true}));app.flush();
    assert.equal(app.unload(),true);assert.equal(app.note(),'Tienes cambios sin guardar.');
});
test('successful submission can navigate without asking to discard saved changes',()=>{
    const app=fixture();app.form.dispatchEvent(new CustomEvent('admin:content-change'));
    app.form.dispatchEvent(new Event('submit',{cancelable:true}));app.flush();
    assert.equal(app.unload(),false);assert.equal(app.note(),'Guardando…');
});
test('returning through browser history protects restored form values',()=>{
    const app=fixture();const event=new Event('pageshow');event.persisted=true;
    app.window.dispatchEvent(event);assert.equal(app.unload(),true);
});
