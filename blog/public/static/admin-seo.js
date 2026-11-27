(() => {
    const preview = document.querySelector('[data-seo-preview]');
    const form = preview?.closest('form');
    if (!form) return;
    const field = name => form.elements.namedItem(name)?.value.trim() || '';
    const plain = html => {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        doc.querySelectorAll('script,style').forEach(node => node.remove());
        doc.querySelectorAll('p,div,li,h1,h2,h3,br').forEach(node => node.append(' '));
        return (doc.body.textContent || '').replace(/\s+/g, ' ').trim();
    };
    const shorten = (text, limit) => {
        if (Array.from(text).length <= limit) return text;
        let cut = Array.from(text).slice(0, limit - 1).join('');
        const space = cut.lastIndexOf(' ');
        if (space > limit * .65) cut = cut.slice(0, space);
        return cut.replace(/[ ,.;:]+$/, '') + '…';
    };
    const update = () => {
        const html = field('content_html');
        const text = plain(html) || field('content_markdown').replace(/[#*_`>]/g, '').replace(/\s+/g, ' ');
        const description = shorten(plain(field('seo_description') || field('excerpt')) || text, 160);
        preview.querySelector('[data-seo-title]').textContent = (field('seo_title') || field('title') || 'El título de tu próximo artículo') + ' | ' + preview.dataset.siteTitle;
        preview.querySelector('[data-seo-description]').textContent = description || 'Tu descripción se generará con el extracto o el contenido del artículo.';
        preview.querySelector('[data-seo-url]').textContent = preview.dataset.origin + ' / … / ' + (field('slug') || 'tu-articulo');
        const checks = [
            [!!field('title'), 'Título del artículo', 'Escribe el título del artículo'],
            [!!description, 'Descripción disponible', 'Añade contenido para generar la descripción'],
            [!!field('cover_image') || !!form.elements.namedItem('cover_image_file')?.files?.length, 'Portada seleccionada', 'Añade una portada propia'],
            [!!field('category_id'), 'Categoría seleccionada', 'Selecciona una categoría'],
        ];
        const list = preview.querySelector('[data-seo-checklist]');
        list.replaceChildren(...checks.map(([ready, yes, no]) => {
            const li = document.createElement('li');
            const icon = document.createElement('i');
            icon.className = 'bi ' + (ready ? 'bi-check-circle' : 'bi-circle');
            icon.setAttribute('aria-hidden', 'true');
            li.append(icon, document.createTextNode(ready ? yes : no));
            return li;
        }));
    };
    form.addEventListener('input', update);
    form.addEventListener('change', update);
    form.addEventListener('admin:content-change', update);
    document.addEventListener('keydown', event => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') {
            event.preventDefault();
            if (form.getAttribute('aria-busy') !== 'true') form.requestSubmit();
        }
    });
    update();
})();
