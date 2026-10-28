// Sprint board: drag task cards between status columns (pointer events, so it
// works with mouse, pen and touch), or focus a card and press ← / →.
document.addEventListener('DOMContentLoaded', () => {
    const board = document.getElementById('sprintBoard');
    if (!board || typeof sprintId === 'undefined') return;

    const base = ASSERT_PATH;
    const esc = Ox.esc;
    const live = document.getElementById('boardStatus');
    const onlyMine = document.getElementById('boardOnlyMine');
    const prefs = Ox.store('sprint-board');
    let columns = [];
    let tasks = [];
    let canMove = false;

    // -----------------------------------------------------------------
    // Board / table switch
    // -----------------------------------------------------------------
    function setLayout(layout) {
        const isBoard = layout === 'board';
        board.hidden = !isBoard;
        document.getElementById('sprintTable').hidden = isBoard;
        document.querySelector('.ox-board-bar .ox-board-mine').hidden = !isBoard;
        document.getElementById('boardHint').hidden = !isBoard || !canMove;
        const nav = document.getElementById('myNav');
        if (nav) nav.style.visibility = isBoard ? 'hidden' : '';
        if (isBoard) document.getElementById('errormsg').style.display = 'none';
    }
    const saved = prefs.load() || {};
    document.querySelectorAll('input[name="sprintLayout"]').forEach(input => {
        input.checked = input.value === (saved.layout || 'board');
        input.addEventListener('change', () => {
            prefs.save({ ...prefs.load(), layout: input.value });
            setLayout(input.value);
        });
    });
    onlyMine.checked = !!saved.mine;
    onlyMine.addEventListener('change', () => {
        prefs.save({ ...prefs.load(), mine: onlyMine.checked });
        render();
    });

    // -----------------------------------------------------------------
    // Rendering
    // -----------------------------------------------------------------
    const columnOf = statusId => columns.find(col => col.statuses.includes(Number(statusId)));

    function card(task) {
        const done = Math.min(100, Math.max(0, Number(task.completed_percentage) || 0));
        const url = `${base}backlog/tasks?pid=${task.product_id}&pblid=${task.backlog_item_id}&usid=${task.user_story_id}`;
        return `<li class="ox-card" data-id="${Number(task.task_id)}" tabindex="0"
                    aria-roledescription="${canMove ? 'Draggable task card' : 'Task card'}"
                    aria-label="${esc(task.task_title)}, ${esc(task.status_name)}">
            <div class="ox-card-top">
                <span class="priority-${esc(task.priority)}" title="Priority">${esc(task.priority)}</span>
                <span class="ox-card-id">US_${Number(task.user_story_id)} · #${Number(task.task_id)}</span>
            </div>
            <a class="ox-card-title" href="${url}" draggable="false">${esc(task.task_title)}</a>
            <div class="ox-card-foot">
                <span class="ox-card-who" title="Assignee">
                    <span class="ox-card-avatar" aria-hidden="true">${esc((task.assignee || '?').charAt(0).toUpperCase())}</span>
                    ${esc(task.assignee || 'Unassigned')}
                </span>
                <span class="ox-card-status">${esc(task.status_name)}</span>
            </div>
            <span class="ox-task-progress" title="${done}% complete"><span style="width:${done}%"></span></span>
        </li>`;
    }

    function render() {
        const visible = onlyMine.checked ? tasks.filter(t => Number(t.assignee_id) === Number(window.oxUserId)) : tasks;
        board.innerHTML = columns.map(col => {
            const items = visible.filter(t => columnOf(t.status_id) === col);
            return `<section class="ox-col" data-col="${esc(col.key)}" aria-label="${esc(col.label)}">
                <header class="ox-col-head"><span class="ox-col-dot is-${esc(col.key)}"></span>${esc(col.label)}<span class="ox-badge-count">${items.length}</span></header>
                <ul class="ox-col-list">${items.map(card).join('') || '<li class="ox-col-empty">No tasks</li>'}</ul>
            </section>`;
        }).join('');
        board.classList.toggle('is-readonly', !canMove);
    }

    function load() {
        fetch(`${base}sprint/board/${sprintId}`, { headers: { Accept: 'application/json' } })
            .then(r => r.ok ? r.json() : Promise.reject(new Error(r.status)))
            .then(data => {
                columns = data.columns || [];
                tasks = data.tasks || [];
                canMove = !!data.canMove;
                render();
                setLayout(document.querySelector('input[name="sprintLayout"]:checked').value);
            })
            .catch(() => {
                board.innerHTML = '<div class="ox-empty-inline"><i class="icon-alert-circle" aria-hidden="true"></i><span>The board could not be loaded.</span></div>';
            });
    }

    // -----------------------------------------------------------------
    // Moving cards
    // -----------------------------------------------------------------
    function move(taskId, column, { undoable = true, statusId = column?.drop, percentage = null } = {}) {
        const task = tasks.find(t => Number(t.task_id) === Number(taskId));
        // Dropping a card back into its own column keeps its exact status
        if (!task || !column || (undoable && columnOf(task.status_id) === column)) return;
        const previous = { status_id: task.status_id, status_name: task.status_name, completed_percentage: task.completed_percentage };

        task.status_id = statusId;
        task.status_name = column.label;
        if (percentage !== null) task.completed_percentage = percentage;
        else if (statusId === 7) task.completed_percentage = 100;
        render();
        board.querySelector(`.ox-card[data-id="${taskId}"]`)?.focus();
        live.textContent = `${task.task_title} moved to ${column.label}`;

        const body = new URLSearchParams({ sprint_id: sprintId, task_id: taskId, status_id: statusId });
        if (percentage !== null) body.set('completed_percentage', percentage);
        fetch(`${base}sprint/moveTask`, { method: 'POST', body, headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (!data.success) throw new Error(data.message);
                if (undoable) {
                    Ox.toast(`Moved to ${column.label}`, {
                        action: {
                            label: 'Undo',
                            onClick: () => move(taskId, columnOf(previous.status_id), {
                                undoable: false,
                                statusId: Number(previous.status_id),
                                percentage: Number(previous.completed_percentage) || 0,
                            }),
                        },
                    });
                }
                // Refresh so the exact status names come from the server
                load();
            })
            .catch(error => {
                Object.assign(task, previous);
                render();
                Ox.toast(error.message || 'The task could not be moved.', { type: 'error' });
            });
    }

    board.addEventListener('keydown', event => {
        const el = event.target.closest('.ox-card');
        if (!el || !canMove || !['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
        event.preventDefault();
        const task = tasks.find(t => Number(t.task_id) === Number(el.dataset.id));
        const index = columns.indexOf(columnOf(task.status_id)) + (event.key === 'ArrowRight' ? 1 : -1);
        if (columns[index]) move(task.task_id, columns[index]);
    });

    board.addEventListener('keydown', event => {
        const el = event.target.closest('.ox-card');
        if (el && event.key === 'Enter' && event.target === el) el.querySelector('.ox-card-title').click();
    });

    // Pointer drag with a small threshold so clicks still open the task
    let drag = null;
    board.addEventListener('pointerdown', event => {
        const el = event.target.closest('.ox-card');
        if (!el || !canMove || event.button !== 0) return;
        drag = { el, id: el.dataset.id, x: event.clientX, y: event.clientY, ghost: null, pointerId: event.pointerId };
    });

    document.addEventListener('pointermove', event => {
        if (!drag) return;
        if (!drag.ghost) {
            if (Math.hypot(event.clientX - drag.x, event.clientY - drag.y) < 6) return;
            const rect = drag.el.getBoundingClientRect();
            drag.offsetX = drag.x - rect.left;
            drag.offsetY = drag.y - rect.top;
            drag.ghost = drag.el.cloneNode(true);
            drag.ghost.classList.add('is-ghost');
            drag.ghost.style.width = rect.width + 'px';
            document.body.appendChild(drag.ghost);
            drag.el.classList.add('is-dragging');
            document.body.classList.add('ox-dragging');
        }
        event.preventDefault();
        drag.ghost.style.transform = `translate(${event.clientX - drag.offsetX}px, ${event.clientY - drag.offsetY}px) rotate(2deg)`;
        const over = document.elementFromPoint(event.clientX, event.clientY)?.closest('.ox-col');
        board.querySelectorAll('.ox-col').forEach(col => col.classList.toggle('is-over', col === over));
    }, { passive: false });

    function endDrag(event) {
        if (!drag) return;
        const { ghost, id } = drag;
        drag.el.classList.remove('is-dragging');
        document.body.classList.remove('ox-dragging');
        board.querySelectorAll('.ox-col.is-over').forEach(col => col.classList.remove('is-over'));
        if (ghost) {
            ghost.remove();
            const over = event && document.elementFromPoint(event.clientX, event.clientY)?.closest('.ox-col');
            const column = over && columns.find(col => col.key === over.dataset.col);
            if (column) move(id, column);
            // Swallow the click that follows a drag
            board.addEventListener('click', e => e.preventDefault(), { capture: true, once: true });
        }
        drag = null;
    }
    document.addEventListener('pointerup', endDrag);
    document.addEventListener('pointercancel', () => endDrag(null));

    load();
});
