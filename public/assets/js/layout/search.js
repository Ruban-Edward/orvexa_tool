// Global search palette: Ctrl/⌘ K or "/" opens it from anywhere
(function () {
    const dialog = document.getElementById('oxSearch');
    const trigger = document.getElementById('oxSearchTrigger');
    if (!dialog || !trigger) return;

    const input = document.getElementById('oxSearchInput');
    const list = document.getElementById('oxSearchResults');
    const isMac = /Mac|iPhone|iPad/.test(navigator.platform);
    const base = window.baseUrl || '/';
    const cache = new Map();
    const TYPES = {
        product: { label: 'Products', icon: 'package' },
        backlog: { label: 'Backlog items', icon: 'layers' },
        story: { label: 'User stories', icon: 'file-text' },
        task: { label: 'Tasks', icon: 'check-square' },
        sprint: { label: 'Sprints', icon: 'zap' },
    };
    let controller = null;
    let timer = null;
    let active = -1;

    trigger.querySelector('[data-shortcut]').textContent = isMac ? '⌘ K' : 'Ctrl K';
    trigger.setAttribute('aria-label', `Search (${isMac ? '⌘' : 'Ctrl'}+K)`);

    const esc = value => String(value ?? '').replace(/[&<>"']/g, ch => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[ch]);

    function highlight(text, term) {
        const safe = esc(text);
        if (!term) return safe;
        const pattern = esc(term).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        return safe.replace(new RegExp(pattern, 'ig'), match => `<mark>${match}</mark>`);
    }

    function message(icon, text) {
        list.innerHTML = `<div class="ox-search-state"><i class="icon-${icon}" aria-hidden="true"></i><span>${esc(text)}</span></div>`;
        input.setAttribute('aria-expanded', 'false');
        active = -1;
    }

    function options() {
        return [...list.querySelectorAll('.ox-search-item')];
    }

    function setActive(index) {
        const items = options();
        if (!items.length) return;
        active = (index + items.length) % items.length;
        items.forEach((item, i) => item.setAttribute('aria-selected', String(i === active)));
        items[active].scrollIntoView({ block: 'nearest' });
        input.setAttribute('aria-activedescendant', items[active].id);
    }

    function render(results, term) {
        if (!results.length) {
            message('search', `No results for “${term}”`);
            return;
        }
        let html = '';
        let index = 0;
        Object.keys(TYPES).forEach(type => {
            const rows = results.filter(row => row.type === type);
            if (!rows.length) return;
            html += `<div class="ox-search-group" role="presentation">${TYPES[type].label}</div>`;
            rows.forEach(row => {
                html += `<a class="ox-search-item" role="option" id="oxSearchOpt${index++}" aria-selected="false" href="${esc(base + row.url)}">
                    <span class="ox-search-icon"><i class="icon-${TYPES[type].icon}" aria-hidden="true"></i></span>
                    <span class="ox-search-text">
                        <span class="ox-search-title">${highlight(row.title, term)}</span>
                        ${row.subtitle ? `<span class="ox-search-sub">${esc(row.subtitle)}</span>` : ''}
                    </span>
                    <span class="ox-search-id">#${esc(row.id)}</span>
                </a>`;
            });
        });
        list.innerHTML = html;
        input.setAttribute('aria-expanded', 'true');
        setActive(0);
    }

    function search(term) {
        if (cache.has(term)) {
            render(cache.get(term), term);
            return;
        }
        controller?.abort();
        controller = new AbortController();
        list.setAttribute('aria-busy', 'true');
        fetch(`${base}search?q=${encodeURIComponent(term)}`, {
            signal: controller.signal,
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
        })
            .then(response => {
                if (!response.ok) throw new Error(response.status);
                return response.json();
            })
            .then(data => {
                const results = data.results || [];
                cache.set(term, results);
                if (input.value.trim() === term) render(results, term);
            })
            .catch(error => {
                if (error.name !== 'AbortError') message('alert-circle', 'Search is unavailable right now. Please try again.');
            })
            .finally(() => list.removeAttribute('aria-busy'));
    }

    function onInput() {
        const term = input.value.trim();
        clearTimeout(timer);
        if (term.length < 2 && !/^#?\d+$/.test(term)) {
            controller?.abort();
            message('search', 'Type at least 2 characters to search');
            return;
        }
        timer = setTimeout(() => search(term), 180);
    }

    function open() {
        if (!dialog.hidden) return;
        dialog.hidden = false;
        document.body.classList.add('ox-search-open');
        trigger.setAttribute('aria-expanded', 'true');
        input.select();
        input.focus();
        if (!input.value.trim()) message('search', 'Type at least 2 characters to search');
    }

    function close() {
        if (dialog.hidden) return;
        dialog.hidden = true;
        document.body.classList.remove('ox-search-open');
        trigger.setAttribute('aria-expanded', 'false');
        controller?.abort();
        trigger.focus();
    }

    trigger.addEventListener('click', open);
    input.addEventListener('input', onInput);
    dialog.addEventListener('mousedown', event => {
        if (event.target === dialog) close();
    });
    list.addEventListener('mousemove', event => {
        const item = event.target.closest('.ox-search-item');
        if (item) setActive(options().indexOf(item));
    });

    input.addEventListener('keydown', event => {
        if (event.key === 'ArrowDown') { event.preventDefault(); setActive(active + 1); }
        else if (event.key === 'ArrowUp') { event.preventDefault(); setActive(active - 1); }
        else if (event.key === 'Enter') {
            const item = options()[active];
            if (item) { event.preventDefault(); window.location.href = item.href; }
        }
    });

    document.addEventListener('keydown', event => {
        const typing = /^(INPUT|TEXTAREA|SELECT)$/.test(event.target.tagName) || event.target.isContentEditable;
        if ((event.key === 'k' || event.key === 'K') && (event.ctrlKey || event.metaKey)) {
            event.preventDefault();
            dialog.hidden ? open() : close();
        } else if (event.key === '/' && !typing && dialog.hidden) {
            event.preventDefault();
            open();
        } else if (event.key === 'Escape' && !dialog.hidden) {
            event.preventDefault();
            close();
        } else if (event.key === 'Tab' && !dialog.hidden) {
            // Keep focus inside the dialog
            event.preventDefault();
            input.focus();
        }
    });
})();
