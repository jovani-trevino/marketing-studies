(() => {
    'use strict';
    document.addEventListener('DOMContentLoaded', () => {
        const dirty = new Set();
        const forms = [...document.querySelectorAll('form[data-admin-unsaved]')];
        forms.forEach(form => {
            const note = document.createElement('p');
            note.className = 'admin-form-status';
            note.setAttribute('role', 'status');
            note.textContent = 'Sin cambios pendientes.';
            form.append(note);
            const changed = event => {
                // Editor formatting selectors do not change saved values themselves.
                if (event.type !== 'admin:content-change' && !event.target.name && !event.target.closest('[contenteditable]')) return;
                dirty.add(form);
                note.textContent = 'Tienes cambios sin guardar.';
                note.classList.add('has-changes');
            };
            ['input','change','admin:content-change'].forEach(type => form.addEventListener(type, changed));
            form.addEventListener('submit', event => {
                // Wait until editor/upload validation and all submit listeners run.
                queueMicrotask(() => {
                    if (event.defaultPrevented) return;
                    dirty.delete(form);
                    note.textContent = 'Guardando…';
                });
            });
        });
        window.addEventListener('beforeunload', event => {
            if (!dirty.size) return;
            event.preventDefault();
            event.returnValue = '';
        });
        window.addEventListener('pageshow', event => {
            if (!event.persisted) return;
            forms.forEach(form => form.dispatchEvent(new CustomEvent('admin:content-change')));
        });

        document.querySelectorAll('[data-workspace-search]').forEach(input => {
            const region = document.getElementById(input.dataset.workspaceSearch);
            if (!region) return;
            const items = [...region.querySelectorAll('[data-workspace-item]')];
            const status = region.querySelector('[data-workspace-results]');
            input.addEventListener('input', () => {
                const query = input.value.trim().toLocaleLowerCase('es');
                let visible = 0;
                items.forEach(item => {
                    item.hidden = !item.textContent.toLocaleLowerCase('es').includes(query);
                    if (!item.hidden) visible++;
                });
                region.querySelectorAll('[data-workspace-group]').forEach(group => {
                    group.hidden = ![...group.querySelectorAll('[data-workspace-item]')].some(item => !item.hidden);
                });
                if (status) status.textContent = `${visible} de ${items.length} resultados`;
            });
        });
    });
})();
