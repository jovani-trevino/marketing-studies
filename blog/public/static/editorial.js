(() => {
  'use strict';
  const es=document.documentElement.lang==='es';
  const menu=document.getElementById('ms-primary-nav');
  const toggle=document.querySelector('.ms-menu-toggle');
  const closeMenu=()=>{menu?.classList.remove('is-open');toggle?.setAttribute('aria-expanded','false');toggle?.setAttribute('aria-label',es?'Abrir menú':'Open menu');};
  toggle?.addEventListener('click',()=>{const opened=menu.classList.toggle('is-open');toggle.setAttribute('aria-expanded',String(opened));toggle.setAttribute('aria-label',opened?(es?'Cerrar menú':'Close menu'):(es?'Abrir menú':'Open menu'));});
  menu?.querySelectorAll('a').forEach(a=>a.addEventListener('click',closeMenu));
  document.addEventListener('click',event=>{if(!event.target.closest('.ms-header'))closeMenu();});
  matchMedia('(min-width:992px)').addEventListener('change',closeMenu);
  const search=document.getElementById('ms-search-dialog');
  const openSearch=()=>{closeMenu();search?.showModal();search?.querySelector('input')?.focus();};
  document.querySelector('[data-open-search]')?.addEventListener('click',openSearch);
  document.querySelector('[data-close-search]')?.addEventListener('click',()=>search.close());
  search?.addEventListener('click',event=>{if(event.target===search){const box=search.getBoundingClientRect();if(event.clientX<box.left||event.clientX>box.right||event.clientY<box.top||event.clientY>box.bottom)search.close();}});
  document.addEventListener('keydown',event=>{const typing=/INPUT|TEXTAREA|SELECT/.test(document.activeElement?.tagName||'')||document.activeElement?.isContentEditable;if((event.key==='k'&&(event.ctrlKey||event.metaKey))||(event.key==='/'&&!typing)){event.preventDefault();if(!search?.open)openSearch();}if(event.key==='Escape'&&menu?.classList.contains('is-open')){closeMenu();toggle?.focus();}});
  const toc=document.querySelector('.ms-article-sidebar details');
  if(toc&&matchMedia('(max-width:640px)').matches)toc.open=false;
  const links=[...document.querySelectorAll('.ms-article-sidebar nav a')];
  if('IntersectionObserver' in window&&links.length){const observer=new IntersectionObserver(entries=>{for(const entry of entries){if(entry.isIntersecting)links.forEach(link=>link.classList.toggle('active',link.hash===`#${entry.target.id}`));}},{rootMargin:'-110px 0px -60% 0px'});document.querySelectorAll('.article-body h2[id],.article-body h3[id]').forEach(heading=>observer.observe(heading));}
  let timer;
  const notify=message=>{const toast=document.getElementById('ms-toast');if(!toast)return;clearTimeout(timer);toast.textContent=message;toast.hidden=false;timer=setTimeout(()=>toast.hidden=true,3500);};
  document.querySelector('[data-copy-citation]')?.addEventListener('click',async event=>{const button=event.currentTarget;const text=`${button.dataset.author} (${button.dataset.year}). ${button.dataset.title}. Marketing Studies. ${location.origin+location.pathname}`;try{await navigator.clipboard.writeText(text);notify(es?'Referencia copiada al portapapeles.':'Citation copied to clipboard.');}catch{notify(es?'No se pudo copiar. Puedes usar la dirección del navegador.':'Could not copy. You can use the address in your browser.');}});
})();
