// Shared UI helpers used by the list pages: toasts (with Undo), pager, remembered
// filters with chips, empty states, undoable deletes, unsaved-change guard and
// global keyboard shortcuts. Exposed as window.Ox.
window.Ox = (function () {
    // jQuery is loaded after the CSRF helper; make sure its requests carry the token
    window.oxCsrfInstall?.();
    const userId = window.oxUserId || 0;

    const esc = value => String(value ?? '').replace(/[&<>"']/g, ch => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[ch]);

    const typing = target => /^(INPUT|TEXTAREA|SELECT)$/.test(target.tagName) || target.isContentEditable;

    // -----------------------------------------------------------------
    // Toasts
    // -----------------------------------------------------------------
    let toastStack = null;

    function toast(message, { type = 'success', action = null, duration = 5000 } = {}) {
        if (!toastStack) {
            toastStack = document.createElement('div');
            toastStack.className = 'ox-toasts';
            toastStack.setAttribute('aria-live', 'polite');
            document.body.appendChild(toastStack);
        }
        const icons = { success: 'check-circle', error: 'alert-circle', info: 'info' };
        const el = document.createElement('div');
        el.className = `ox-toast is-${type}`;
        el.setAttribute('role', type === 'error' ? 'alert' : 'status');
        el.innerHTML = `<i class="icon-${icons[type] || 'info'}" aria-hidden="true"></i>
            <span class="ox-toast-text">${esc(message)}</span>
            ${action ? `<button type="button" class="ox-toast-action">${esc(action.label)}</button>` : ''}
            <button type="button" class="ox-toast-close" aria-label="Dismiss"><i class="icon-x" aria-hidden="true"></i></button>`;
        toastStack.appendChild(el);

        let timer;
        const close = () => {
            clearTimeout(timer);
            el.classList.add('is-leaving');
            setTimeout(() => el.remove(), 180);
        };
        const start = () => { timer = setTimeout(close, duration); };
        el.addEventListener('mouseenter', () => clearTimeout(timer));
        el.addEventListener('mouseleave', start);
        el.querySelector('.ox-toast-close').addEventListener('click', close);
        if (action) {
            el.querySelector('.ox-toast-action').addEventListener('click', () => {
                close();
                action.onClick();
            });
        }
        start();
        return { close };
    }

    // -----------------------------------------------------------------
    // Remembered state (per user, per page scope)
    // -----------------------------------------------------------------
    function store(scope) {
        const key = `ox:${userId}:${scope}`;
        return {
            load() {
                try { return JSON.parse(localStorage.getItem(key) || 'null'); } catch (e) { return null; }
            },
            save(value) {
                try { localStorage.setItem(key, JSON.stringify(value)); } catch (e) {}
            },
            clear() {
                try { localStorage.removeItem(key); } catch (e) {}
            },
        };
    }

    // Set the selected values of a (multi)select and refresh the multiselect widget if present
    function setSelectValues(select, values) {
        if (!select) return;
        const wanted = new Set((Array.isArray(values) ? values : [values]).filter(v => v !== null && v !== undefined).map(String));
        [...select.options].forEach(option => { option.selected = wanted.has(option.value); });
        if (typeof select.loadOptions === 'function') select.loadOptions();
    }

    const selectedOptions = select => select ? [...select.selectedOptions].filter(o => o.value !== '') : [];

    // -----------------------------------------------------------------
    // Filter chips
    // -----------------------------------------------------------------
    function chips(container, items, onClearAll) {
        if (!container) return;
        if (!items.length) {
            container.hidden = true;
            container.innerHTML = '';
            return;
        }
        container.hidden = false;
        container.innerHTML = `<span class="ox-chips-label">Filtered by</span>` + items.map((item, i) =>
            `<span class="ox-chip"><span><b>${esc(item.group)}:</b> ${esc(item.label)}</span>
                <button type="button" data-chip="${i}" aria-label="Remove filter ${esc(item.group)} ${esc(item.label)}"><i class="icon-x" aria-hidden="true"></i></button>
            </span>`).join('') +
            (items.length > 1 ? `<button type="button" class="ox-link-btn" data-chip-clear>Clear all</button>` : '');
        container.querySelectorAll('[data-chip]').forEach(button => {
            button.addEventListener('click', () => items[+button.dataset.chip].onRemove());
        });
        container.querySelector('[data-chip-clear]')?.addEventListener('click', onClearAll);
    }

    // Build chips for a set of selects + a search input
    function chipsFor(selects, search) {
        const items = [];
        selects.forEach(({ el, group, onRemove }) => {
            selectedOptions(el).forEach(option => items.push({
                group,
                label: option.textContent.trim(),
                onRemove: () => {
                    option.selected = false;
                    if (typeof el.loadOptions === 'function') el.loadOptions();
                    onRemove();
                },
            }));
        });
        if (search && search.el && search.el.value.trim()) {
            items.push({ group: 'Search', label: search.el.value.trim(), onRemove: () => { search.el.value = ''; search.onRemove(); } });
        }
        return items;
    }

    // -----------------------------------------------------------------
    // Pager: "Showing 21–40 of 132", numbered pages, page-size choice
    // -----------------------------------------------------------------
    function pager(container, { scope, sizes = [10, 25, 50], onChange }) {
        const prefs = store(`pagesize:${scope}`);
        let size = sizes.includes(prefs.load()) ? prefs.load() : sizes[0];
        let page = 1;
        let total = 0;

        container.classList.add('ox-pager');
        container.addEventListener('click', event => {
            const button = event.target.closest('[data-page]');
            if (!button || button.disabled) return;
            page = +button.dataset.page;
            onChange({ page, size, offset: (page - 1) * size });
        });
        container.addEventListener('change', event => {
            if (!event.target.matches('[data-size]')) return;
            size = +event.target.value;
            prefs.save(size);
            page = 1;
            onChange({ page, size, offset: 0 });
        });

        function pages(totalPages) {
            const list = new Set([1, totalPages, page - 1, page, page + 1]);
            const sorted = [...list].filter(p => p >= 1 && p <= totalPages).sort((a, b) => a - b);
            const out = [];
            sorted.forEach((p, i) => {
                if (i && p - sorted[i - 1] > 1) out.push('…');
                out.push(p);
            });
            return out;
        }

        function render(newTotal) {
            total = Number(newTotal) || 0;
            const totalPages = Math.max(1, Math.ceil(total / size));
            if (page > totalPages) page = totalPages;
            const from = total ? (page - 1) * size + 1 : 0;
            const to = Math.min(total, page * size);
            container.hidden = total === 0;
            container.innerHTML = `
                <span class="ox-pager-info">Showing <b>${from}–${to}</b> of <b>${total}</b></span>
                <nav class="ox-pager-nav" aria-label="Pagination">
                    <button type="button" class="ox-pager-btn" data-page="${page - 1}" ${page <= 1 ? 'disabled' : ''} aria-label="Previous page"><i class="icon-chevron-left" aria-hidden="true"></i></button>
                    ${pages(totalPages).map(p => p === '…'
                        ? '<span class="ox-pager-gap" aria-hidden="true">…</span>'
                        : `<button type="button" class="ox-pager-btn${p === page ? ' is-current' : ''}" data-page="${p}" ${p === page ? 'aria-current="page"' : ''} aria-label="Page ${p}">${p}</button>`).join('')}
                    <button type="button" class="ox-pager-btn" data-page="${page + 1}" ${page >= totalPages ? 'disabled' : ''} aria-label="Next page"><i class="icon-chevron-right" aria-hidden="true"></i></button>
                </nav>
                <label class="ox-pager-size">Rows per page
                    <select class="form-select form-select-sm" data-size>${sizes.map(s => `<option value="${s}" ${s === size ? 'selected' : ''}>${s}</option>`).join('')}</select>
                </label>`;
        }

        return {
            render,
            reset() { page = 1; },
            get page() { return page; },
            get size() { return size; },
            get offset() { return (page - 1) * size; },
        };
    }

    // -----------------------------------------------------------------
    // Empty states that explain why and offer the next step
    // -----------------------------------------------------------------
    function empty(container, { icon = 'inbox', title, text, actions = [] }) {
        if (!container) return;
        container.innerHTML = `<div class="ox-empty ox-empty-page">
            <i class="icon-${esc(icon)}" aria-hidden="true"></i>
            <h4>${esc(title)}</h4>
            ${text ? `<p>${esc(text)}</p>` : ''}
            ${actions.length ? `<div class="ox-empty-actions">${actions.map((a, i) =>
                `<button type="button" class="btn ${a.primary ? 'btn-primary' : 'btn-secondary'}" data-empty-action="${i}">${a.icon ? `<i class="icon-${esc(a.icon)}" aria-hidden="true"></i> ` : ''}${esc(a.label)}</button>`).join('')}</div>` : ''}
        </div>`;
        container.querySelectorAll('[data-empty-action]').forEach(button => {
            button.addEventListener('click', () => actions[+button.dataset.emptyAction].onClick());
        });
    }

    // -----------------------------------------------------------------
    // Undoable delete: the server soft-deletes, Undo restores
    // -----------------------------------------------------------------
    function post(url) {
        return fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' } })
            .then(response => response.ok ? response.json() : Promise.reject(new Error(response.status)));
    }

    function undoableDelete({ label, deleteUrl, restoreUrl, onChange }) {
        return post(deleteUrl).then(response => {
            if (!response.success) {
                toast(response.message || `${label} could not be deleted.`, { type: 'error' });
                return false;
            }
            onChange();
            toast(`${label} deleted`, {
                duration: 8000,
                action: {
                    label: 'Undo',
                    onClick: () => post(restoreUrl)
                        .then(result => {
                            if (!result.success) throw new Error(result.message);
                            onChange();
                            toast(`${label} restored`);
                        })
                        .catch(() => toast(`${label} could not be restored.`, { type: 'error' })),
                },
            });
            return true;
        }).catch(() => {
            toast(`${label} could not be deleted. Please try again.`, { type: 'error' });
            return false;
        });
    }

    // -----------------------------------------------------------------
    // Warn before closing a modal form with unsaved changes
    // -----------------------------------------------------------------
    const snapshot = form => {
        if (window.tinymce) window.tinymce.triggerSave();
        return new URLSearchParams(new FormData(form)).toString();
    };

    function guardModal(modalEl, form) {
        if (!modalEl || !form) return;
        let clean = '';
        let discarding = false;
        form.oxMarkClean = () => { clean = snapshot(form); };
        modalEl.addEventListener('shown.bs.modal', () => form.oxMarkClean());
        modalEl.addEventListener('hide.bs.modal', event => {
            if (discarding || form.dataset.saved === '1' || snapshot(form) === clean) {
                discarding = false;
                delete form.dataset.saved;
                return;
            }
            event.preventDefault();
            Swal.fire({
                title: 'Discard unsaved changes?',
                text: 'The changes you made in this form will be lost.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Discard',
                cancelButtonText: 'Keep editing',
                reverseButtons: true,
                focusCancel: true,
            }).then(result => {
                if (result.isConfirmed) {
                    discarding = true;
                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                }
            });
        });
        window.addEventListener('beforeunload', event => {
            if (modalEl.classList.contains('show') && snapshot(form) !== clean && form.dataset.saved !== '1') {
                event.preventDefault();
                event.returnValue = '';
            }
        });
    }

    // -----------------------------------------------------------------
    // Keyboard shortcuts
    // -----------------------------------------------------------------
    const GO = { d: 'dashboard/dashboardView', m: 'mywork', p: 'backlog/productbacklogs', s: 'sprint/sprintlist', c: 'meeting/calendar' };
    let pendingG = 0;

    function visible(el) {
        return el && el.offsetParent !== null && !el.disabled;
    }

    function showShortcuts() {
        const row = (keys, text) => `<tr><td>${keys.map(k => `<kbd class="ox-kbd">${k}</kbd>`).join(' ')}</td><td>${text}</td></tr>`;
        Swal.fire({
            title: 'Keyboard shortcuts',
            html: `<table class="ox-shortcuts">
                ${row([navigator.platform.includes('Mac') ? '⌘' : 'Ctrl', 'K'], 'Search everything')}
                ${row(['/'], 'Search everything')}
                ${row(['N'], 'Create a new item on this page')}
                ${row(['F'], 'Open filters on this page')}
                ${row(['Esc'], 'Close panels and dialogs')}
                ${row(['G', 'D'], 'Go to dashboard')}
                ${row(['G', 'M'], 'Go to my work')}
                ${row(['G', 'P'], 'Go to products')}
                ${row(['G', 'S'], 'Go to sprints')}
                ${row(['G', 'C'], 'Go to calendar')}
                ${row(['?'], 'Show this help')}
            </table>`,
            showConfirmButton: false,
            showCloseButton: true,
        });
    }

    document.addEventListener('keydown', event => {
        if (event.defaultPrevented || event.ctrlKey || event.metaKey || event.altKey || typing(event.target)) return;
        if (document.querySelector('.modal.show, .swal2-container, .ox-search-dialog:not([hidden])')) {
            return;
        }
        const key = event.key.toLowerCase();

        if (pendingG && Date.now() - pendingG < 1200 && GO[key]) {
            event.preventDefault();
            pendingG = 0;
            window.location.href = (window.baseUrl || '/') + GO[key];
            return;
        }
        pendingG = key === 'g' ? Date.now() : 0;

        if (key === 'n') {
            const target = [...document.querySelectorAll('[data-shortcut="new"]')].find(visible);
            if (target) { event.preventDefault(); target.click(); }
        } else if (key === 'f') {
            const target = [...document.querySelectorAll('[data-shortcut="filter"]')].find(visible);
            if (target) { event.preventDefault(); target.click(); }
        } else if (event.key === '?') {
            event.preventDefault();
            showShortcuts();
        }
    });

    // -----------------------------------------------------------------
    // Accessible names for legacy forms: link a field to the visible label
    // right before it, or fall back to its placeholder text
    // -----------------------------------------------------------------
    let fieldSeq = 0;
    // A label is free when it has no "for", or its "for" points at nothing (a broken reference)
    const freeLabel = label => label && label.tagName === 'LABEL' && (!label.htmlFor || !document.getElementById(label.htmlFor));
    function labelFields(root = document) {
        root.querySelectorAll('input:not([type=hidden]):not([type=submit]):not([type=button]), select, textarea').forEach(field => {
            if (field.labels?.length || field.getAttribute('aria-label') || field.getAttribute('aria-labelledby')) return;
            let label = null;
            for (let node = field.previousElementSibling; node && !label; node = node.previousElementSibling) {
                if (freeLabel(node)) label = node;
                else if (node.matches('input, select, textarea')) break;
            }
            if (!label) {
                const holder = field.parentElement?.previousElementSibling;
                const candidate = holder?.tagName === 'LABEL' ? holder : holder?.querySelector?.(':scope > label:only-child');
                if (freeLabel(candidate)) label = candidate;
            }
            if (label && label.textContent.trim()) {
                if (!field.id) field.id = `ox-field-${++fieldSeq}`;
                label.htmlFor = field.id;
            } else if (field.placeholder) {
                field.setAttribute('aria-label', field.placeholder);
            }
        });
    }
    document.addEventListener('DOMContentLoaded', () => labelFields());
    document.addEventListener('shown.bs.modal', event => labelFields(event.target));

    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape') return;
        document.querySelectorAll('.filter-sidebar.open').forEach(panel => panel.classList.remove('open'));
    });

    return { esc, toast, store, setSelectValues, chips, chipsFor, pager, empty, undoableDelete, guardModal, showShortcuts, labelFields };
})();
