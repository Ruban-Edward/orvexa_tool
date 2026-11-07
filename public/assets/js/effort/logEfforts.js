/**
 * Log efforts page: log, edit and delete the user's efforts (one modal for create and edit).
 * The list is server-rendered, so the page reloads after a change.
 */
(function () {
    const modalEl = document.getElementById('logEffortModal');
    const form = document.getElementById('logEffortForm');
    if (!modalEl || !form) return;

    const modal = () => bootstrap.Modal.getOrCreateInstance(modalEl);
    const $id = id => document.getElementById(id);
    const fields = {
        id: $id('effortId'),
        task: $id('effortTask'),
        spentOn: $id('effortSpentOn'),
        hours: $id('effortHours'),
        comments: $id('effortComments'),
        activity: $id('effortActivity'),
        mainActivity: $id('effortMainActivity'),
        logType: $id('effortLogType'),
    };
    const defaults = {
        spentOn: fields.spentOn.value,
        logType: fields.logType.value,
    };
    let changed = false;

    Ox.guardModal(modalEl, form);

    // Full comment on hover/focus, only for comments cut off at two lines
    document.querySelectorAll('.ox-efforts-comment-text').forEach(el => {
        const isCut = () => el.scrollHeight > el.clientHeight + 1;
        const mark = () => {
            const cut = isCut();
            el.classList.toggle('is-truncated', cut);
            el.tabIndex = cut ? 0 : -1;
        };
        mark();
        window.addEventListener('resize', mark);
        bootstrap.Tooltip.getOrCreateInstance(el, { placement: 'top', customClass: 'ox-efforts-tip', container: 'body' });
        el.addEventListener('show.bs.tooltip', event => {
            if (!isCut()) event.preventDefault();
        });
    });

    // Selects a value; adds a placeholder option first when it is no longer in the list
    // (a task no longer assigned to the user, or a removed activity)
    function selectValue(select, value, label) {
        value = value === '' || value == null ? '' : String(value);
        if (value !== '' && ![...select.options].some(option => option.value === value)) {
            select.add(new Option(label || `#${value} (no longer listed)`, value));
        }
        select.value = value;
    }

    function setMode(editing, meeting) {
        $id('logEffortModalTitle').textContent = editing ? 'Edit effort' : 'Log effort';
        $id('effortSubmit').textContent = editing ? 'Save changes' : 'Create';
        $id('effortAddAnother').hidden = editing;
        $id('effortMeetingRow').hidden = !meeting;
        $id('effortTaskRow').hidden = !!meeting;
        form.classList.remove('was-validated');
    }

    $id('openLogEffort').addEventListener('click', () => {
        form.reset();
        fields.id.value = '';
        fields.spentOn.value = defaults.spentOn;
        fields.logType.value = defaults.logType;
        setMode(false, false);
        modal().show();
    });

    function openEdit(effort) {
        form.reset();
        fields.id.value = effort.id;
        $id('effortMeeting').value = effort.meeting || '';
        selectValue(fields.task, effort.task, effort.taskLabel);
        fields.spentOn.value = effort.spentOn;
        fields.hours.value = effort.hours;
        fields.comments.value = effort.comments;
        selectValue(fields.activity, effort.activity);
        selectValue(fields.mainActivity, effort.mainActivity);
        if (effort.logType !== '') selectValue(fields.logType, effort.logType);
        setMode(true, effort.meeting !== '');
        modal().show();
    }

    // Created entries with "Create and add another" show up once the modal closes
    modalEl.addEventListener('hidden.bs.modal', () => {
        if (changed) window.location.reload();
    });

    function fail(message) {
        Swal.fire({ title: 'Unable to save effort', text: message, icon: 'error' });
    }

    form.addEventListener('submit', event => {
        event.preventDefault();
        const mode = event.submitter?.dataset.mode || 'close';
        form.classList.add('was-validated');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const buttons = form.querySelectorAll('button[type=submit]');
        buttons.forEach(button => { button.disabled = true; });
        $.ajax({
            url: `${baseUrl}efforts/save`,
            method: 'POST',
            data: $(form).serialize(),
            dataType: 'json',
            success: response => {
                if (!response.success) {
                    fail(response.message || 'Effort could not be saved.');
                    return;
                }
                changed = true;
                if (mode === 'another') {
                    // Keep date, task and activities; clear what changes per entry
                    fields.hours.value = '';
                    fields.comments.value = '';
                    form.classList.remove('was-validated');
                    form.oxMarkClean?.();
                    Ox.toast(response.message || 'Effort logged.');
                    fields.hours.focus();
                } else {
                    form.dataset.saved = '1';
                    modal().hide();
                }
            },
            error: xhr => fail(xhr.responseJSON?.message || 'Effort could not be saved.'),
            complete: () => buttons.forEach(button => { button.disabled = false; }),
        });
    });

    document.addEventListener('click', event => {
        const editButton = event.target.closest('[data-edit-effort]');
        if (editButton) {
            try {
                openEdit(JSON.parse(editButton.dataset.editEffort));
            } catch (e) {
                fail('This effort could not be opened for editing.');
            }
            return;
        }

        const button = event.target.closest('[data-delete-effort]');
        if (!button) return;

        Swal.fire({
            title: 'Delete this effort?',
            text: button.dataset.meeting === '1'
                ? 'Your logged time for this meeting will be removed.'
                : 'The logged time will be removed.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            focusCancel: true,
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: `${baseUrl}efforts/delete`,
                method: 'POST',
                data: { effort_id: button.dataset.deleteEffort },
                dataType: 'json',
                success: response => {
                    if (response.success) window.location.reload();
                    else Swal.fire({ title: 'Unable to delete', text: response.message, icon: 'error' });
                },
                error: xhr => Swal.fire({ title: 'Unable to delete', text: xhr.responseJSON?.message || 'This effort could not be deleted.', icon: 'error' }),
            });
        });
    });
})();
