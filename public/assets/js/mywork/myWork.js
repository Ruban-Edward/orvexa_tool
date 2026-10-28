// My work: client-side filtering of the server-rendered task list (no extra requests)
(function () {
    const root = document.getElementById('myWork');
    if (!root) return;

    const groups = [...root.querySelectorAll('.ox-mywork-group')];
    const search = document.getElementById('myWorkSearch');
    const product = document.getElementById('myWorkProduct');
    const empty = document.getElementById('myWorkEmpty');
    const clear = document.getElementById('myWorkClear');
    const viewKey = 'ox-mywork-view';

    const currentView = () => root.querySelector('input[name="myWorkView"]:checked').value;

    function apply() {
        const view = currentView();
        const term = search.value.trim().toLowerCase();
        const productName = product ? product.value : '';
        let visibleTotal = 0;
        let totalInView = 0;

        groups.forEach(group => {
            const rows = [...group.querySelectorAll('.ox-task-row')];
            const inView = group.dataset.view === view;
            let visible = 0;
            rows.forEach(row => {
                const match = (!term || row.dataset.search.includes(term)) &&
                    (!productName || row.dataset.product === productName);
                row.hidden = !match;
                if (match) visible++;
            });
            if (inView) totalInView += rows.length;
            group.hidden = !inView || visible === 0;
            group.querySelector('[data-count]').textContent = visible;
            if (inView) visibleTotal += visible;
        });

        const filtered = term !== '' || productName !== '';
        empty.hidden = visibleTotal > 0;
        clear.hidden = !(filtered && totalInView > 0);
        empty.querySelector('[data-empty-title]').textContent = filtered && totalInView > 0
            ? 'No tasks match your filters'
            : view === 'open' ? "You're all caught up" : 'Nothing finished recently';
        empty.querySelector('[data-empty-text]').textContent = filtered && totalInView > 0
            ? 'Try a different search or product.'
            : view === 'open' ? 'When someone assigns you a task, it shows up here.' : 'Tasks you finished in the last 30 days appear here.';
    }

    let timer;
    search.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(apply, 120);
    });
    product?.addEventListener('change', apply);
    root.querySelectorAll('input[name="myWorkView"]').forEach(input => input.addEventListener('change', () => {
        try { localStorage.setItem(viewKey, input.value); } catch (e) {}
        apply();
    }));
    clear.addEventListener('click', () => {
        search.value = '';
        if (product) product.value = '';
        apply();
        search.focus();
    });

    try {
        const saved = localStorage.getItem(viewKey);
        const input = saved && root.querySelector(`input[name="myWorkView"][value="${saved}"]`);
        if (input) input.checked = true;
    } catch (e) {}
    apply();
})();
