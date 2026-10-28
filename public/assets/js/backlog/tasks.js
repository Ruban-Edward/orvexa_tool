if (typeof m_tasks !== 'undefined') {

    var taskId = 0;
    const esc = Ox.esc;
    const filterSidebar = document.getElementById('filterSidebar');
    const filterBtn = document.getElementById('filter_btn');
    const searchInput = document.getElementById('backlogSearchInput');
    const prioritySelect = document.getElementById('filterPriority');
    const statusSelect = document.getElementById('filterstatus');
    const taskForm = document.getElementById('taskDetailsForm');
    const taskModalEl = document.getElementById('addTaskModal');
    const taskModal = () => bootstrap.Modal.getOrCreateInstance(taskModalEl);
    // Task filters are remembered per user, across all user stories
    const filterStore = Ox.store('filters:tasks');

    const startDatePicker = flatpickr('#start_date', {
        onChange: function (selectedDates, dateStr) {
            endDatePicker.set('minDate', dateStr);
        }
    });
    const endDatePicker = flatpickr('#end_date', {});

    // -----------------------------------------------------------------
    // Filter panel
    // -----------------------------------------------------------------
    function setFilterPanel(open) {
        filterSidebar.classList.toggle('open', open);
        filterBtn.setAttribute('aria-expanded', String(open));
        if (open) setTimeout(() => filterSidebar.querySelector('.multiselect-dropdown, select')?.focus(), 50);
    }
    filterBtn.addEventListener('click', event => {
        event.stopPropagation();
        setFilterPanel(!filterSidebar.classList.contains('open'));
    });
    document.getElementById('closeFilterSidebarBtn').addEventListener('click', () => {
        setFilterPanel(false);
        filterBtn.focus();
    });
    document.addEventListener('click', event => {
        if (filterSidebar.classList.contains('open') && !filterSidebar.contains(event.target) && !event.target.closest('#filter_btn')) {
            setFilterPanel(false);
        }
    });

    // Restore the last filters before the first load
    const saved = filterStore.load();
    if (saved) {
        Ox.setSelectValues(prioritySelect, saved.priority || []);
        Ox.setSelectValues(statusSelect, saved.status || []);
        searchInput.value = saved.search || '';
    }

    const pager = Ox.pager(document.getElementById('taskPager'), {
        scope: 'tasks',
        onChange: () => loadTasks(),
    });

    const selectedValues = select => [...select.selectedOptions].map(o => o.value).filter(Boolean);
    const hasFilters = () => selectedValues(prioritySelect).length > 0 || selectedValues(statusSelect).length > 0 || searchInput.value.trim() !== '';

    function clearFilters() {
        Ox.setSelectValues(prioritySelect, []);
        Ox.setSelectValues(statusSelect, []);
        searchInput.value = '';
        pager.reset();
        loadTasks();
    }

    function renderFilterState() {
        const count = selectedValues(prioritySelect).length + selectedValues(statusSelect).length;
        const badge = document.getElementById('noti');
        badge.textContent = count;
        badge.hidden = count === 0;
        Ox.chips(document.getElementById('taskChips'), Ox.chipsFor([
            { el: prioritySelect, group: 'Priority', onRemove: reload },
            { el: statusSelect, group: 'Status', onRemove: reload },
        ], { el: searchInput, onRemove: reload }), clearFilters);
        filterStore.save({ priority: selectedValues(prioritySelect), status: selectedValues(statusSelect), search: searchInput.value.trim() });
    }

    function reload() {
        pager.reset();
        loadTasks();
    }

    // -----------------------------------------------------------------
    // List
    // -----------------------------------------------------------------
    function createTaskCard(task) {
        const date = value => value && value.trim() !== '-' ? esc(value.trim().split(' ')[0]) : '—';
        const done = Math.min(100, Math.max(0, Number(task.completed_percentage) || 0));
        return `
            <div class="col-md-12" data-task="${Number(task.task_id)}">
                <div class="card cls-task-card">
                    <div class="card-header cls-task-header">
                        <span class="ox-id-chip" title="Task ID">#${Number(task.task_id)}</span>
                        <div class="card-title cls-task-header-details">${esc(task.task_title)}</div>
                        <span class="ox-status-pill">${esc(task.task_status)}</span>
                        <span class="cls-priority priority-box ${esc(String(task.priority).toLowerCase())}">${esc(task.priority)}</span>
                        <div class="cls-action-buttons">
                            ${userPermissions.updateTask ? `
                            <button type="button" class="btn btn-link ox-icon-action" data-edit="${Number(task.task_id)}" title="Edit task" aria-label="Edit task ${esc(task.task_title)}">
                                <i class="icon-edit-2" aria-hidden="true"></i>
                            </button>` : ''}
                            ${userPermissions.deleteTask ? `
                            <button type="button" class="btn btn-link ox-icon-action is-danger" data-delete="${Number(task.task_id)}" title="Delete task" aria-label="Delete task ${esc(task.task_title)}">
                                <i class="icon-trash-2" aria-hidden="true"></i>
                            </button>` : ''}
                        </div>
                    </div>
                    <div class="card-body-content cls-task-body">
                        <dl class="ox-fields">
                            <div><dt>Assignee</dt><dd>${esc(task.first_name || 'Unassigned')}</dd></div>
                            <div><dt>Tracker</dt><dd>${esc(task.tracker_name)}</dd></div>
                            <div><dt>Start date</dt><dd>${date(task.start_date)}</dd></div>
                            <div><dt>End date</dt><dd>${date(task.end_date)}</dd></div>
                            <div><dt>Estimated hours</dt><dd>${esc(task.estimated_hours)}</dd></div>
                            <div><dt>Completed</dt><dd>
                                <span class="ox-mini-progress"><span style="width:${done}%"></span></span> ${done}%
                            </dd></div>
                        </dl>
                        <p class="ox-task-desc">${esc(task.task_description)}</p>
                    </div>
                </div>
            </div>`;
    }

    function showEmpty() {
        const filtered = hasFilters();
        Ox.empty(document.getElementById('empty'), filtered ? {
            icon: 'filter',
            title: 'No tasks match your filters',
            text: 'Try removing a filter or changing your search.',
            actions: [{ label: 'Clear filters', icon: 'x', onClick: clearFilters }],
        } : {
            icon: 'check-square',
            title: 'No tasks yet',
            text: 'Break this user story into tasks so the team can pick them up.',
            actions: userPermissions.addTask ? [{ label: 'Create first task', icon: 'plus', primary: true, onClick: () => addform('add') }] : [],
        });
        document.getElementById('taskTools').hidden = !filtered;
    }

    let request = null;
    function loadTasks() {
        request?.abort();
        renderFilterState();
        request = $.ajax({
            url: assertPath + 'backlog/filterTasks',
            type: 'POST',
            data: JSON.stringify({
                filter: {
                    UId: userStoryId,
                    priorityFilter: selectedValues(prioritySelect),
                    statusFilter: selectedValues(statusSelect),
                    search: searchInput.value.trim(),
                    limit: pager.size,
                    offset: pager.offset,
                }
            }),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                const tasks = response?.data || [];
                const total = Number(response?.total) || 0;
                if (!hasFilters()) document.getElementById('taskTotal').textContent = total;
                document.getElementById('taskList').innerHTML = tasks.map(createTaskCard).join('');
                pager.render(total);
                document.getElementById('main-page').hidden = total === 0;
                document.getElementById('empty').hidden = total !== 0;
                if (total === 0) showEmpty(); else document.getElementById('taskTools').hidden = false;
            },
            error: function (xhr, status) {
                if (status !== 'abort') Ox.toast('Tasks could not be loaded. Please try again.', { type: 'error' });
            },
        });
    }

    let searchTimer;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(reload, 250);
    });
    document.getElementById('filterformAction').addEventListener('submit', event => {
        event.preventDefault();
        setFilterPanel(false);
        reload();
    });
    document.getElementById('resetFiltersBtn').addEventListener('click', () => {
        setFilterPanel(false);
        clearFilters();
    });

    document.getElementById('taskList').addEventListener('click', event => {
        const edit = event.target.closest('[data-edit]');
        const remove = event.target.closest('[data-delete]');
        if (edit) addform('edit', Number(edit.dataset.edit));
        if (remove) deleteTask(Number(remove.dataset.delete));
    });

    // -----------------------------------------------------------------
    // Add / edit modal
    // -----------------------------------------------------------------
    Ox.guardModal(taskModalEl, taskForm);

    function resetTaskForm() {
        taskForm.reset();
        taskForm.classList.remove('was-validated');
        startDatePicker.clear();
        endDatePicker.clear();
    }

    function addform(name, id) {
        resetTaskForm();
        taskId = id || 0;
        const adding = name === 'add';
        document.getElementById('add_tasks').textContent = adding ? 'Add task' : 'Edit task';
        document.getElementById('submitTaskBtn').textContent = adding ? 'Add task' : 'Update task';
        document.getElementById('submitAddAnother').hidden = !adding;
        document.getElementById('formOperation').value = adding ? 'insertTask' : 'updateTask';
        taskModal().show();
        if (adding) {
            setTimeout(() => document.getElementById('task_title').focus(), 200);
            return;
        }

        $.ajax({
            url: assertPath + 'backlog/getTaskById/' + taskId,
            method: 'POST',
            dataType: 'json',
            success: function (response) {
                const task = response && response[0];
                if (!task) {
                    taskModal().hide();
                    Ox.toast('This task could not be found. It may have been deleted.', { type: 'error' });
                    return;
                }
                $('#task_title').val(task.task_title);
                $('#task_description').val(task.task_description);
                $('#task_statuses').val(task.task_status);
                $('#task_priority').val(task.priority);
                $('#task_assignee').val(task.assignee_id ?? '');
                startDatePicker.setDate(task.start_date ? task.start_date.split(' ')[0] : null, false);
                endDatePicker.setDate(task.end_date ? task.end_date.split(' ')[0] : null, false);
                $('#completed_percentage').val(task.completed_percentage);
                $('#estimated_time').val(task.estimated_hours);
                taskForm.oxMarkClean();
                document.getElementById('task_title').focus();
            },
            error: function () {
                Ox.toast('The task could not be loaded. Please try again.', { type: 'error' });
            },
        });
    }
    window.addform = addform;

    function saveTask(formData, operation) {
        const url = operation === 'insertTask'
            ? assertPath + 'backlog/addTasks/' + pid + '/' + pblid + '/' + userStoryId
            : assertPath + 'backlog/updateTaskById/' + pid + '/' + pblid + '/' + taskId;
        return $.ajax({ url, method: 'POST', data: formData + '&taskbutton=1', dataType: 'json' });
    }

    function showValidationErrors(errors) {
        const list = Object.values(errors).map(message => `<li>${esc(message)}</li>`).join('');
        Swal.fire({ title: 'Please check the form', html: `<ul class="text-start mb-0">${list}</ul>`, icon: 'warning' });
    }

    taskForm.addEventListener('submit', event => {
        event.preventDefault();
        taskForm.classList.add('was-validated');
        if (!taskForm.checkValidity()) {
            taskForm.querySelector(':invalid')?.focus();
            return;
        }
        const operation = document.getElementById('formOperation').value;
        const another = event.submitter?.value === 'another';
        const buttons = taskForm.querySelectorAll('[type="submit"]');
        buttons.forEach(b => { b.disabled = true; });

        saveTask($(taskForm).serialize(), operation)
            .done(response => {
                if (response.success) {
                    Ox.toast(operation === 'insertTask' ? 'Task added' : 'Task updated');
                    loadTasks();
                    if (another) {
                        const keep = { status: $('#task_statuses').val(), priority: $('#task_priority').val(), assignee: $('#task_assignee').val() };
                        resetTaskForm();
                        $('#task_statuses').val(keep.status);
                        $('#task_priority').val(keep.priority);
                        $('#task_assignee').val(keep.assignee);
                        taskForm.oxMarkClean();
                        document.getElementById('task_title').focus();
                    } else {
                        taskForm.dataset.saved = '1';
                        taskModal().hide();
                    }
                } else if (response.error) {
                    showValidationErrors(response.error);
                } else {
                    Ox.toast(response.message || 'The task could not be saved.', { type: 'error' });
                }
            })
            .fail(() => Ox.toast('The task could not be saved. Please try again.', { type: 'error' }))
            .always(() => buttons.forEach(b => { b.disabled = false; }));
    });

    document.getElementById('completed_percentage').addEventListener('input', function () {
        this.setCustomValidity(this.value > 100 || this.value < 0 ? 'Use a value from 0 to 100' : '');
    });

    // Quick add: a title is enough; the rest can be filled in later
    document.getElementById('quickAddTask')?.addEventListener('submit', event => {
        event.preventDefault();
        const input = document.getElementById('quickAddTitle');
        const title = input.value.trim();
        if (title.length < 3) {
            Ox.toast('A task title needs at least 3 characters.', { type: 'error' });
            input.focus();
            return;
        }
        input.disabled = true;
        const data = $.param({ task_title: title, task_description: title, task_status: 1, priority: 'L', assignee_id: '' });
        saveTask(data, 'insertTask')
            .done(response => {
                if (response.success) {
                    input.value = '';
                    Ox.toast('Task added — open it to add details');
                    loadTasks();
                } else if (response.error) {
                    showValidationErrors(response.error);
                }
            })
            .fail(() => Ox.toast('The task could not be added. Please try again.', { type: 'error' }))
            .always(() => { input.disabled = false; input.focus(); });
    });

    // -----------------------------------------------------------------
    // Delete with Undo
    // -----------------------------------------------------------------
    function deleteTask(id) {
        document.querySelector(`[data-task="${id}"]`)?.classList.add('is-removing');
        Ox.undoableDelete({
            label: 'Task',
            deleteUrl: `${assertPath}backlog/deletetask/${pid}/${pblid}/${id}`,
            restoreUrl: `${assertPath}backlog/restoretask/${pid}/${pblid}/${id}`,
            onChange: loadTasks,
        }).then(ok => {
            if (!ok) document.querySelector(`[data-task="${id}"]`)?.classList.remove('is-removing');
        });
    }
    window.deleteTask = deleteTask;

    loadTasks();
}
