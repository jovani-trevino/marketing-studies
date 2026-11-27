(() => {
  const filters = document.querySelectorAll('[data-case-filter]');
  const cards = document.querySelectorAll('[data-case-kind]');
  const search = document.querySelector('[data-case-search]');
  const normalize = text => text.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es');
  let selected = 'all';
  const readFilters = () => {
    const params = new URLSearchParams(location.search);
    const area = params.get('area') || 'all';
    selected = [...filters].some(button => button.dataset.caseFilter === area) ? area : 'all';
    if (search) search.value = params.get('q') || '';
    filters.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.caseFilter === selected)));
  };
  const update = () => {
    const query = normalize(search?.value.trim() || '');
    let visible = 0;
    cards.forEach(card => {
      card.hidden = (selected !== 'all' && selected !== card.dataset.caseKind) || !normalize(card.textContent).includes(query);
      if (!card.hidden) visible++;
    });
    const count = document.querySelector('[data-case-count]');
    if (count) count.textContent = `${visible} ${visible === 1 ? 'caso' : 'casos'}`;
    const empty = document.querySelector('[data-case-empty]');
    if (empty) empty.hidden = visible !== 0;
  };
  const saveFilters = () => {
    if (!cards.length) return;
    const url = new URL(location.href);
    if (selected === 'all') url.searchParams.delete('area'); else url.searchParams.set('area', selected);
    if (search?.value.trim()) url.searchParams.set('q', search.value.trim()); else url.searchParams.delete('q');
    history.replaceState(null, '', url);
  };
  filters.forEach(button => button.addEventListener('click', () => {
    selected = button.dataset.caseFilter;
    filters.forEach(item => item.setAttribute('aria-pressed', String(item === button)));
    update();
    saveFilters();
  }));
  search?.addEventListener('input', () => { update(); saveFilters(); });
  document.querySelector('[data-case-reset]')?.addEventListener('click', () => {
    selected = 'all';
    if (search) search.value = '';
    filters.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.caseFilter === 'all')));
    update(); saveFilters(); search?.focus();
  });
  if (cards.length) { readFilters(); update(); }
  window.addEventListener('popstate', () => { if (cards.length) { readFilters(); update(); } });
  const sections = document.querySelectorAll('.mi-editorial section[id]');
  const sectionLinks = document.querySelectorAll('.mi-case-nav a');
  if (sections.length && 'IntersectionObserver' in window) {
    const sectionObserver = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        sectionLinks.forEach(link => {
          if (link.hash === `#${entry.target.id}`) link.setAttribute('aria-current', 'location');
          else link.removeAttribute('aria-current');
        });
      });
    }, { rootMargin: '-15% 0px -60% 0px', threshold: 0 });
    sections.forEach(section => sectionObserver.observe(section));
  }
  const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const replays = document.querySelectorAll('[data-chart-replay]');
  replays.forEach(button => {
    button.hidden = motion.matches;
    button.addEventListener('click', () => {
      if (motion.matches) return;
      const chart = button.closest('[data-case-chart]');
      chart.classList.remove('is-visible');
      requestAnimationFrame(() => requestAnimationFrame(() => chart.classList.add('is-visible')));
    });
  });
  motion.addEventListener('change', () => replays.forEach(button => { button.hidden = motion.matches; }));
  if (!motion.matches && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver(entries => entries.forEach(entry => {
      if (entry.isIntersecting) { entry.target.classList.add('is-visible'); observer.unobserve(entry.target); }
    }), { threshold: .2 });
    document.querySelectorAll('[data-case-chart]').forEach(chart => observer.observe(chart));
  }
})();
