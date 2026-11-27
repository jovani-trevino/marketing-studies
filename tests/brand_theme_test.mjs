import test from 'node:test';
import assert from 'node:assert/strict';
import vm from 'node:vm';
import {readFileSync} from 'node:fs';
const source=readFileSync(new URL('../cv/brand-theme.js',import.meta.url),'utf8');
function page({preference=null,dark=false,blocked=false,lang='es'}={}) {
 const storage=new Map(preference?[['gb-theme',preference]]:[]),attrs=new Map(),events={},windowEvents={};
 const root={lang,setAttribute:(k,v)=>attrs.set(k,v),getAttribute:k=>attrs.get(k)};
 const label={textContent:''},buttonAttrs=new Map();let click;
 const button={setAttribute:(k,v)=>buttonAttrs.set(k,v),querySelector:s=>s.includes('label')?label:null,addEventListener:(_event,fn)=>click=fn};
 const media={matches:dark,addEventListener:(_event,fn)=>media.change=fn};
 const document={documentElement:root,querySelectorAll:()=>[button],querySelector:()=>null,dispatchEvent(){},addEventListener:(name,fn)=>events[name]=fn};
 const window={matchMedia:()=>media,addEventListener:(name,fn)=>windowEvents[name]=fn};
 const localStorage={getItem:k=>{if(blocked)throw Error();return storage.get(k)??null;},setItem:(k,v)=>{if(blocked)throw Error();storage.set(k,v);}};
 vm.runInNewContext(source,{window,document,localStorage,CustomEvent:class{}});
 events.DOMContentLoaded();
 return {attrs,storage,media,windowEvents,label,buttonAttrs,click:()=>click()};
}
test('saved theme survives page changes and overrides the system preference',()=>{
 const portfolio=page({preference:'light',dark:true});assert.equal(portfolio.attrs.get('data-theme-mode'),'light');
 portfolio.click();assert.equal(portfolio.storage.get('gb-theme'),'dark');
 const cms=page({preference:portfolio.storage.get('gb-theme')});assert.equal(cms.attrs.get('data-bs-theme'),'dark');assert.equal(cms.buttonAttrs.get('aria-pressed'),'true');
});
test('a theme change in another tab updates the visible page',()=>{
 const p=page();p.storage.set('gb-theme','dark');p.windowEvents.storage({key:'gb-theme'});assert.equal(p.attrs.get('data-theme-mode'),'dark');
 p.storage.clear();p.windowEvents.storage({key:null});assert.equal(p.attrs.get('data-theme-mode'),'light');
});
test('theme switch still works when browser storage is blocked',()=>{
 const p=page({blocked:true});p.click();assert.equal(p.attrs.get('data-theme-mode'),'dark');assert.equal(p.label.textContent,'Activar modo claro');
});
test('system theme updates only apply when no explicit preference was saved',()=>{
 const p=page();p.media.matches=true;p.media.change();assert.equal(p.attrs.get('data-theme-mode'),'dark');
 p.click();p.media.change();assert.equal(p.attrs.get('data-theme-mode'),'light');
});
test('button labels describe the next action in the page language',()=>{
 const p=page({lang:'en'});assert.equal(p.label.textContent,'Switch to dark mode');p.click();assert.equal(p.label.textContent,'Switch to light mode');
});
