(() => {
  'use strict';
  const page = document.body;
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  document.documentElement.classList.add('jt-enhanced');

  const nav = document.querySelector('[data-nav]');
  const toggle = document.querySelector('[data-nav-toggle]');
  const setNav = (open, restoreFocus = false) => {
    if (!nav || !toggle) return;
    nav.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
    if (restoreFocus) toggle.focus();
  };
  toggle?.addEventListener('click', () => setNav(toggle.getAttribute('aria-expanded') !== 'true'));
  nav?.querySelectorAll('a').forEach(link => link.addEventListener('click', () => setNav(false)));
  document.addEventListener('click', event => {
    if (toggle?.getAttribute('aria-expanded') === 'true' && !event.target.closest('.jt-header')) setNav(false);
  });
  window.matchMedia('(max-width: 960px)').addEventListener('change', () => setNav(false));

  // Native anchors retain browser history and work when JavaScript is unavailable.
  const navLinks = Array.from(nav?.querySelectorAll('a[href^="#"]') || []);
  const updateCurrent = id => navLinks.forEach(link => {
    const active = link.hash === `#${id}`;
    link.classList.toggle('is-active', active);
    if (active) link.setAttribute('aria-current', 'location');
    else link.removeAttribute('aria-current');
  });
  updateCurrent(location.hash.slice(1) || 'perfil');
  if ('IntersectionObserver' in window) {
    const sectionObserver = new IntersectionObserver(entries => {
      const visible = entries.filter(entry => entry.isIntersecting).sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
      if (visible[0]) updateCurrent(visible[0].target.id);
    }, { rootMargin: '-15% 0px -60% 0px', threshold: 0 });
    navLinks.forEach(link => { const section = document.getElementById(link.hash.slice(1)); if (section) sectionObserver.observe(section); });
  }

  const tabs = Array.from(document.querySelectorAll('[data-experience-tab]'));
  const panels = Array.from(document.querySelectorAll('[data-experience-panel]'));
  const activateTab = (tab, focus = false) => {
    tabs.forEach(item => {
      const active = item === tab;
      item.setAttribute('aria-selected', String(active));
      item.tabIndex = active ? 0 : -1;
      item.classList.toggle('is-active', active);
    });
    panels.forEach(panel => {
      const active = panel.dataset.experiencePanel === tab.dataset.experienceTab;
      panel.hidden = !active;
      panel.classList.toggle('is-active', active);
      if (active) panel.querySelectorAll('.is-pending').forEach(node => node.classList.remove('is-pending'));
    });
    if (focus) tab.focus();
  };
  tabs.forEach((tab, index) => {
    tab.addEventListener('click', () => activateTab(tab));
    tab.addEventListener('keydown', event => {
      let target;
      if (event.key === 'ArrowRight') target = (index + 1) % tabs.length;
      if (event.key === 'ArrowLeft') target = (index - 1 + tabs.length) % tabs.length;
      if (event.key === 'Home') target = 0;
      if (event.key === 'End') target = tabs.length - 1;
      if (target !== undefined) { event.preventDefault(); activateTab(tabs[target], true); }
    });
  });
  if (tabs[0]) activateTab(tabs.find(tab => tab.getAttribute('aria-selected') === 'true') || tabs[0]);

  // One focus-managed dialog controller for both kinds of detail.
  const modals = Array.from(document.querySelectorAll('[data-experience-modal], [data-portfolio-modal]'));
  let currentModal = null;
  let lastTrigger = null;
  let inertStates = [];
  const focusables = modal => Array.from(modal.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])')).filter(node => !node.hidden && node.getAttribute('aria-hidden') !== 'true' && node.getClientRects().length);
  const closeModal = (restoreFocus = true) => {
    if (!currentModal) return;
    currentModal.hidden = true;
    currentModal.classList.remove('is-open');
    currentModal.setAttribute('aria-hidden', 'true');
    inertStates.forEach(([node, inert]) => { node.inert = inert; });
    inertStates = [];
    page.classList.remove('jt-modal-open');
    currentModal = null;
    if (restoreFocus && lastTrigger?.isConnected) lastTrigger.focus({ preventScroll: true });
  };
  const openModal = (modal, trigger) => {
    if (!modal) return;
    closeModal(false);
    lastTrigger = trigger;
    currentModal = modal;
    inertStates = Array.from(page.children).filter(node => node !== modal && node.tagName !== 'SCRIPT').map(node => [node, node.inert]);
    inertStates.forEach(([node]) => { node.inert = true; });
    modal.hidden = false;
    modal.setAttribute('aria-hidden', 'false');
    modal.classList.add('is-open');
    page.classList.add('jt-modal-open');
    (focusables(modal)[0] || modal.querySelector('[role="dialog"]'))?.focus();
  };
  modals.forEach(modal => {
    page.appendChild(modal);
    modal.hidden = true;
    modal.querySelectorAll('[data-experience-close], [data-portfolio-close]').forEach(button => button.addEventListener('click', () => closeModal()));
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
      if (currentModal) { event.preventDefault(); closeModal(); }
      else if (toggle?.getAttribute('aria-expanded') === 'true') { event.preventDefault(); setNav(false, true); }
    }
    if (event.key !== 'Tab' || !currentModal) return;
    const nodes = focusables(currentModal);
    if (!nodes.length) { event.preventDefault(); return; }
    const first = nodes[0], last = nodes[nodes.length - 1];
    if (event.shiftKey && (document.activeElement === first || !currentModal.contains(document.activeElement))) {
      event.preventDefault(); last.focus();
    } else if (!event.shiftKey && (document.activeElement === last || !currentModal.contains(document.activeElement))) {
      event.preventDefault(); first.focus();
    }
  });

  const text = (parent, selector, value) => { const node = parent?.querySelector(selector); if (node) node.textContent = value || ''; };
  const list = (parent, selector, items) => {
    const container = parent?.querySelector(selector);
    if (!container) return;
    container.replaceChildren(...items.map(value => { const li = document.createElement('li'); li.textContent = value; return li; }));
  };
  const parseList = value => { try { const values = JSON.parse(value || '[]'); return Array.isArray(values) ? values.filter(item => typeof item === 'string') : []; } catch { return []; } };
  const bindCards = (selector, handler) => document.querySelectorAll(selector).forEach(card => {
    card.addEventListener('click', () => handler(card));
    card.addEventListener('keydown', event => { if (event.target === card && (event.key === 'Enter' || event.key === ' ')) { event.preventDefault(); handler(card); } });
  });
  const experienceModal = document.querySelector('[data-experience-modal]');
  bindCards('[data-experience-card]', card => {
    ['type', 'title', 'company', 'period', 'summary', 'objective', 'intro'].forEach(key => text(experienceModal, `[data-experience-modal-${key}]`, card.dataset[key]));
    list(experienceModal, '[data-experience-modal-functions]', parseList(card.dataset.functions));
    list(experienceModal, '[data-experience-modal-wins]', parseList(card.dataset.wins));
    openModal(experienceModal, card);
  });
  const portfolioModal = document.querySelector('[data-portfolio-modal]');
  bindCards('[data-portfolio-card]', card => {
    ['title', 'category', 'focus', 'copy', 'meta', 'role', 'deliverables', 'outcome'].forEach(key => text(portfolioModal, `[data-portfolio-modal-${key}]`, card.dataset[key]));
    const img = portfolioModal?.querySelector('[data-portfolio-modal-image]');
    if (img) { img.src = card.dataset.image || ''; img.alt = card.dataset.title || 'Proyecto del portafolio'; }
    const tags = portfolioModal?.querySelector('[data-portfolio-modal-tags]');
    tags?.replaceChildren(...(card.dataset.tags || '').split(',').map(value => value.trim()).filter(Boolean).map(value => { const span = document.createElement('span'); span.textContent = value; return span; }));
    openModal(portfolioModal, card);
  });

  // Content is visible first. Only below-the-fold elements opt into a subtle reveal.
  if ('IntersectionObserver' in window && !reducedMotion.matches) {
    const revealObserver = new IntersectionObserver(entries => entries.forEach(entry => {
      if (entry.isIntersecting) { entry.target.classList.remove('is-pending'); revealObserver.unobserve(entry.target); }
    }), { rootMargin: '0px 0px 50px 0px', threshold: 0.04 });
    document.querySelectorAll('[data-reveal]').forEach(node => {
      if (!node.closest('[hidden]') && node.getBoundingClientRect().top > window.innerHeight + 40) { node.classList.add('is-pending'); revealObserver.observe(node); }
    });
    reducedMotion.addEventListener('change', event => { if (event.matches) document.querySelectorAll('.is-pending').forEach(node => node.classList.remove('is-pending')); });
  }
})();
