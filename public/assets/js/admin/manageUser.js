// Manage users: list with filters, add/edit (details, role, product access),
// deactivate/reactivate with Undo, and temporary password resets.
if (typeof manageUser !== 'undefined') {
    const esc = Ox.esc;
    const body = document.getElementById('usersBody');
    const searchInput = document.getElementById('userSearchInput');
    const roleFilter = document.getElementById('roleFilter');
    const modalEl = document.getElementById('userModal');
    const form = document.getElementById('userForm');
    const modal = () => bootstrap.Modal.getOrCreateInstance(modalEl);
    const filterStore = Ox.store('filters:users');
    const productNames = Object.fromEntries([...form.querySelectorAll('input[name="products[]"]')]
        .map(input => [input.value, input.nextElementSibling.textContent.trim()]));

    // Normalise the server rows once
    const roleNames = Object.fromEntries([...document.querySelectorAll('#roleId option[value]')].filter(o => o.value).map(o => [o.value, o.textContent.trim()]));
    let users = data.map(user => ({
        ...user,
        product_ids: user.product_ids ? user.product_ids.split(',') : [],
        product_names: user.product_names ? user.product_names.split('||') : [],
        // product id => role id on that product
        product_roles: Object.fromEntries((user.product_roles || '').split(',').filter(Boolean).map(pair => pair.split(':'))),
    }));

    // -----------------------------------------------------------------
    // Filters (remembered) and list
    // -----------------------------------------------------------------
    const saved = filterStore.load();
    if (saved) {
        searchInput.value = saved.search || '';
        roleFilter.value = saved.role || '';
        const status = document.querySelector(`input[name="userStatus"][value="${saved.status}"]`);
        if (status) status.checked = true;
    }
    const status = () => document.querySelector('input[name="userStatus"]:checked').value;

    const pager = Ox.pager(document.getElementById('usersPager'), { scope: 'users', onChange: render });

    function matches(user) {
        const term = searchInput.value.trim().toLowerCase();
        const active = user.is_deleted === 'N';
        if (status() === 'active' && !active) return false;
        if (status() === 'inactive' && active) return false;
        if (roleFilter.value && String(user.role_id) !== roleFilter.value) return false;
        if (!term) return true;
        return `${user.first_name} ${user.last_name} ${user.username} ${user.email_id}`.toLowerCase().includes(term);
    }

    function lastLogin(value) {
        if (!value) return '<span class="ox-muted">Never</span>';
        const date = new Date(value.replace(' ', 'T'));
        const days = Math.floor((Date.now() - date) / 86400000);
        const label = days < 1 ? 'Today' : days === 1 ? 'Yesterday' : days < 30 ? `${days} days ago`
            : date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        return `<span title="${esc(date.toLocaleString())}">${label}</span>`;
    }

    function row(user) {
        const active = user.is_deleted === 'N';
        const isMe = Number(user.employee_id) === Number(window.oxUserId);
        const name = `${user.first_name} ${user.last_name || ''}`.trim();
        const products = user.product_ids.map(pid => {
            const role = user.product_roles[pid];
            const label = productNames[pid] || '';
            return role && String(role) !== String(user.role_id) ? `${label} · ${roleNames[role] || ''}` : label;
        }).filter(Boolean);
        const invite = { sent: ['green', 'Invite sent'], queued: ['blue', 'Invite sending'], failed: ['red', 'Invite failed'] }[user.invite_status];
        return `<tr class="${active ? '' : 'is-inactive'}">
            <td>
                <div class="ox-user-cell">
                    <span class="ox-user-avatar" aria-hidden="true">${esc((user.first_name || '?').charAt(0).toUpperCase())}</span>
                    <div>
                        <div class="ox-user-name-line">${esc(name)}
                            ${isMe ? '<span class="ox-tag ox-tag-green">You</span>' : ''}
                            ${!active ? '<span class="ox-tag ox-tag-gray">Inactive</span>' : ''}
                            ${active && user.password_reset_required === 'Y' ? '<span class="ox-tag ox-tag-amber" title="Must choose a new password at next sign-in">Temporary password</span>' : ''}
                            ${active && invite ? `<span class="ox-tag ox-tag-${invite[0]}" title="${esc(user.invite_date ? 'Last invite: ' + user.invite_date : '')}">${invite[1]}</span>` : ''}
                        </div>
                        <div class="ox-user-meta">@${esc(user.username)} · ID ${Number(user.employee_id)}</div>
                    </div>
                </div>
            </td>
            <td><a href="mailto:${esc(user.email_id)}">${esc(user.email_id)}</a></td>
            <td>${esc(user.role_name || '—')}</td>
            <td>${products.length
                ? `<span class="ox-user-products" title="${esc(products.join(', '))}">${products.slice(0, 2).map(p => `<span class="ox-tag ox-tag-gray">${esc(p)}</span>`).join('')}${products.length > 2 ? `<span class="ox-tag ox-tag-gray">+${products.length - 2}</span>` : ''}</span>`
                : '<span class="ox-tag ox-tag-amber" title="This user cannot see any product">No access</span>'}</td>
            <td>${lastLogin(user.last_login)}</td>
            <td class="ox-user-actions">
                <button type="button" class="btn btn-link ox-icon-action" data-activity="${user.user_id}" title="Activity" aria-label="Activity of ${esc(name)}"><i class="icon-activity" aria-hidden="true"></i></button>
                <button type="button" class="btn btn-link ox-icon-action" data-edit="${user.user_id}" title="Edit user" aria-label="Edit ${esc(name)}"><i class="icon-edit-2" aria-hidden="true"></i></button>
                ${active ? `<button type="button" class="btn btn-link ox-icon-action" data-reset="${user.user_id}" title="Reset password" aria-label="Reset password of ${esc(name)}"><i class="icon-key" aria-hidden="true"></i></button>` : ''}
                ${isMe ? '' : active
                    ? `<button type="button" class="btn btn-link ox-icon-action is-danger" data-status="0" data-user="${user.user_id}" title="Deactivate" aria-label="Deactivate ${esc(name)}"><i class="icon-user-x" aria-hidden="true"></i></button>`
                    : `<button type="button" class="btn btn-link ox-icon-action" data-status="1" data-user="${user.user_id}" title="Reactivate" aria-label="Reactivate ${esc(name)}"><i class="icon-user-check" aria-hidden="true"></i></button>`}
            </td>
        </tr>`;
    }

    function render() {
        filterStore.save({ search: searchInput.value.trim(), role: roleFilter.value, status: status() });
        const list = users.filter(matches);
        pager.render(list.length);
        body.innerHTML = list.slice(pager.offset, pager.offset + pager.size).map(row).join('');
        document.getElementById('activeCount').textContent = users.filter(u => u.is_deleted === 'N').length;

        const empty = document.getElementById('usersEmpty');
        empty.hidden = list.length > 0;
        body.closest('.table-responsive').hidden = list.length === 0;
        if (!list.length) {
            const filtered = searchInput.value.trim() || roleFilter.value || status() !== 'active';
            Ox.empty(empty, filtered ? {
                icon: 'filter',
                title: 'No users match these filters',
                text: 'Try another search, role or status.',
                actions: [{ label: 'Clear filters', icon: 'x', onClick: () => {
                    searchInput.value = '';
                    roleFilter.value = '';
                    document.getElementById('statusActive').checked = true;
                    pager.reset();
                    render();
                } }],
            } : {
                icon: 'users',
                title: 'No users yet',
                text: 'Add the people who plan and deliver work in Orvexa.',
                actions: [{ label: 'Add the first user', icon: 'user-plus', primary: true, onClick: () => openForm() }],
            });
        }
    }

    let searchTimer;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => { pager.reset(); render(); }, 150);
    });
    roleFilter.addEventListener('change', () => { pager.reset(); render(); });
    document.querySelectorAll('input[name="userStatus"]').forEach(input => input.addEventListener('change', () => { pager.reset(); render(); }));

    // -----------------------------------------------------------------
    // Add / edit
    // -----------------------------------------------------------------
    Ox.guardModal(modalEl, form);
    const roleSelect = document.getElementById('roleId');

    const productBoxes = () => [...form.querySelectorAll('input[name="products[]"]')];
    const productRoleSelect = box => form.querySelector(`select[name="product_role[${box.value}]"]`);

    function syncProductRole(box, role) {
        const select = productRoleSelect(box);
        select.disabled = !box.checked;
        if (role) select.value = role;
        else if (box.checked && !select.dataset.touched) select.value = roleSelect.value;
    }
    productBoxes().forEach(box => box.addEventListener('change', () => syncProductRole(box)));
    form.querySelectorAll('select[name^="product_role"]').forEach(select => select.addEventListener('change', () => { select.dataset.touched = '1'; }));
    // Untouched product roles follow the main role
    roleSelect.addEventListener('change', () => productBoxes().forEach(box => {
        const select = productRoleSelect(box);
        if (box.checked && !select.dataset.touched) select.value = roleSelect.value;
    }));

    function clearErrors() {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('[data-error]').forEach(el => { el.textContent = ''; });
    }

    function openForm(user = null) {
        form.reset();
        clearErrors();
        document.getElementById('userId').value = user ? user.user_id : '';
        document.getElementById('userModalLabel').textContent = user ? 'Edit user' : 'Add user';
        document.getElementById('saveUserBtn').textContent = user ? 'Save changes' : 'Create user';
        document.getElementById('employeeIdField').hidden = !!user;
        document.getElementById('inviteField').hidden = !!user;
        form.querySelectorAll('select[name^="product_role"]').forEach(select => { delete select.dataset.touched; });
        if (user) {
            document.getElementById('firstName').value = user.first_name;
            document.getElementById('lastName').value = user.last_name || '';
            document.getElementById('username').value = user.username;
            document.getElementById('emailId').value = user.email_id;
            roleSelect.value = user.role_id;
            productBoxes().forEach(box => {
                box.checked = user.product_ids.includes(box.value);
                syncProductRole(box, user.product_roles[box.value] || user.role_id);
                if (box.checked && String(user.product_roles[box.value]) !== String(user.role_id)) productRoleSelect(box).dataset.touched = '1';
            });
        } else {
            roleSelect.value = roleSelect.querySelector('[data-default]')?.value || '';
            productBoxes().forEach(box => syncProductRole(box));
        }
        // Without the role permission, the role is fixed for existing users
        roleSelect.disabled = !!user && !userCapabilities.editRole;
        modal().show();
        setTimeout(() => {
            form.oxMarkClean();
            document.getElementById('firstName').focus();
        }, 250);
    }

    document.getElementById('addUserBtn').addEventListener('click', () => openForm());
    form.querySelectorAll('[data-products]').forEach(button => button.addEventListener('click', () => {
        productBoxes().forEach(box => { box.checked = button.dataset.products === 'all'; syncProductRole(box); });
    }));

    function showCredentials(title, username, password) {
        return Swal.fire({
            title,
            icon: 'success',
            html: `<p class="mb-2">Share these details with the user. They will be asked to choose their own password when they sign in.</p>
                <div class="ox-credentials">
                    <div><span>Username</span><code>${esc(username)}</code></div>
                    <div><span>Temporary password</span><code id="tempPassword">${esc(password)}</code></div>
                </div>
                <p class="ox-muted small mt-2 mb-0">This password is shown only once.</p>`,
            showCancelButton: true,
            confirmButtonText: 'Copy details',
            cancelButtonText: 'Done',
            preConfirm: () => navigator.clipboard?.writeText(`Username: ${username}\nTemporary password: ${password}`)
                .then(() => Ox.toast('Copied to clipboard'))
                .catch(() => Ox.toast('Copy failed. Select the text and copy it instead.', { type: 'error' })),
        });
    }

    form.addEventListener('submit', event => {
        event.preventDefault();
        clearErrors();
        form.classList.add('was-validated');
        if (!form.checkValidity()) {
            form.querySelector(':invalid')?.focus();
            return;
        }
        form.classList.remove('was-validated');
        const saveBtn = document.getElementById('saveUserBtn');
        saveBtn.disabled = true;
        const payload = new FormData(form);
        if (roleSelect.disabled) payload.set('role_id', roleSelect.value);

        $.ajax({ url: baseUrl + 'admin/users/save', method: 'POST', data: new URLSearchParams(payload).toString(), dataType: 'json' })
            .done(response => {
                if (response.errors) {
                    Object.entries(response.errors).forEach(([field, message]) => {
                        const input = form.querySelector(`[name="${field}"]`);
                        input?.classList.add('is-invalid');
                        const box = form.querySelector(`[data-error="${field}"]`);
                        if (box) box.textContent = message;
                    });
                    form.querySelector('.is-invalid')?.focus();
                    return;
                }
                if (!response.success) {
                    Ox.toast(response.message || 'The user could not be saved.', { type: 'error' });
                    return;
                }
                form.dataset.saved = '1';
                modal().hide();
                const userId = payload.get('user_id');
                if (!userId && response.invited) {
                    Ox.toast(response.message);
                    setTimeout(() => window.location.reload(), 1500);
                    return;
                }
                if (!userId) {
                    showCredentials('User created', response.username, response.password).then(() => window.location.reload());
                    return;
                }
                const user = users.find(u => String(u.user_id) === userId);
                const products = payload.getAll('products[]');
                Object.assign(user, {
                    first_name: payload.get('first_name'),
                    last_name: payload.get('last_name'),
                    username: payload.get('username'),
                    email_id: payload.get('email_id'),
                    role_id: payload.get('role_id'),
                    role_name: roleSelect.selectedOptions[0]?.textContent.trim(),
                    product_ids: products,
                    product_names: products.map(id => productNames[id]),
                    product_roles: Object.fromEntries(products.map(id => [id, payload.get(`product_role[${id}]`) || payload.get('role_id')])),
                });
                render();
                Ox.toast(response.message || 'User updated');
            })
            .fail(() => Ox.toast('The user could not be saved. Please try again.', { type: 'error' }))
            .always(() => { saveBtn.disabled = false; });
    });

    // -----------------------------------------------------------------
    // Row actions
    // -----------------------------------------------------------------
    function setStatus(user, active, undoable = true) {
        return $.post(baseUrl + 'admin/users/status', { user_id: user.user_id, active: active ? 1 : 0 }, null, 'json')
            .done(response => {
                if (!response.success) {
                    Ox.toast(response.message, { type: 'error' });
                    return;
                }
                user.is_deleted = active ? 'N' : 'Y';
                render();
                Ox.toast(response.message, undoable ? { action: { label: 'Undo', onClick: () => setStatus(user, !active, false) } } : {});
            })
            .fail(() => Ox.toast('The status could not be changed. Please try again.', { type: 'error' }));
    }

    body.addEventListener('click', event => {
        const button = event.target.closest('button');
        if (!button) return;
        const user = users.find(u => String(u.user_id) === (button.dataset.edit || button.dataset.reset || button.dataset.user || button.dataset.activity));
        if (!user) return;

        if (button.dataset.edit) {
            openForm(user);
        } else if (button.dataset.status) {
            setStatus(user, button.dataset.status === '1');
        } else if (button.dataset.activity) {
            openActivity(user);
        } else if (button.dataset.reset) {
            Swal.fire({
                title: `Reset the password of ${user.first_name}?`,
                text: 'Their current password stops working once the new one is issued.',
                icon: 'warning',
                showCancelButton: true,
                showDenyButton: userCapabilities.mailReady,
                confirmButtonText: userCapabilities.mailReady ? `Email new details to ${user.email_id}` : 'Reset password',
                denyButtonText: 'Show me a password instead',
                reverseButtons: true,
                focusCancel: true,
            }).then(result => {
                if (!result.isConfirmed && !result.isDenied) return;
                const email = userCapabilities.mailReady && result.isConfirmed;
                $.post(baseUrl + 'admin/users/resetPassword', { user_id: user.user_id, send_email: email ? 1 : '' }, null, 'json')
                    .done(response => {
                        if (!response.success) {
                            Ox.toast(response.message, { type: 'error' });
                            return;
                        }
                        if (response.invited) {
                            user.invite_status = 'queued';
                            render();
                            Ox.toast(response.message);
                            return;
                        }
                        user.password_reset_required = 'Y';
                        render();
                        showCredentials('Password reset', response.username, response.password);
                    })
                    .fail(() => Ox.toast('The password could not be reset. Please try again.', { type: 'error' }));
            });
        }
    });

    // -----------------------------------------------------------------
    // Bulk import (upload → preview → import)
    // -----------------------------------------------------------------
    const importModalEl = document.getElementById('importModal');
    const importFile = document.getElementById('importFile');
    const nextBtn = document.getElementById('importNext');
    const backBtn = document.getElementById('importBack');
    let importToken = null;
    let importStep = 'upload';
    let importChanged = false;

    function setImportStep(step) {
        importStep = step;
        importModalEl.querySelectorAll('[data-step]').forEach(section => { section.hidden = section.dataset.step !== step; });
        backBtn.hidden = step !== 'preview';
        document.getElementById('importCancel').textContent = step === 'done' ? 'Close' : 'Cancel';
        nextBtn.hidden = step === 'done';
    }

    document.getElementById('importUsersBtn').addEventListener('click', () => {
        document.getElementById('importForm').reset();
        importToken = null;
        nextBtn.disabled = false;
        nextBtn.textContent = 'Check file';
        setImportStep('upload');
        bootstrap.Modal.getOrCreateInstance(importModalEl).show();
    });
    backBtn.addEventListener('click', () => {
        document.getElementById('importForm').reset();
        nextBtn.textContent = 'Check file';
        nextBtn.disabled = false;
        setImportStep('upload');
    });
    importModalEl.addEventListener('hidden.bs.modal', () => {
        if (importChanged) window.location.reload();
    });

    function renderPreview(response) {
        const ok = response.valid;
        const bad = response.invalid;
        document.getElementById('importSummary').innerHTML = `<b>${ok}</b> ${ok === 1 ? 'user is' : 'users are'} ready to import`
            + (bad ? ` · <span class="text-danger"><b>${bad}</b> ${bad === 1 ? 'row has' : 'rows have'} problems and will be skipped</span>` : '');
        document.getElementById('importRows').innerHTML = response.rows.map(row => `<tr class="${row.errors.length ? 'is-invalid-row' : ''}">
            <td>${row.line}</td>
            <td>${esc(row.name)}</td>
            <td>${esc(row.username)}</td>
            <td>${esc(row.email)}</td>
            <td>${esc(row.role)}</td>
            <td>${row.products.length ? row.products.map(p => `<span class="ox-tag ox-tag-gray">${esc(p)}</span>`).join(' ') : '<span class="ox-muted">None</span>'}</td>
            <td>${row.errors.length
                ? `<ul class="ox-import-errors">${row.errors.map(e => `<li>${esc(e)}</li>`).join('')}</ul>`
                : '<span class="ox-tag ox-tag-green"><i class="icon-check" aria-hidden="true"></i> Ready</span>'}</td>
        </tr>`).join('');
        nextBtn.textContent = ok ? `Import ${ok} ${ok === 1 ? 'user' : 'users'}` : 'Nothing to import';
        nextBtn.disabled = ok === 0;
    }

    function credentialsCsv(created) {
        const cell = value => `"${String(value ?? '').replace(/"/g, '""')}"`;
        const lines = [['name', 'username', 'email', 'temporary_password'].join(',')]
            .concat(created.map(u => [u.name, u.username, u.email, u.password].map(cell).join(',')));
        return URL.createObjectURL(new Blob([lines.join('\n')], { type: 'text/csv' }));
    }

    function renderResult(response) {
        const created = response.created;
        const withPasswords = created.filter(u => u.password);
        document.getElementById('importResult').innerHTML = `
            <div class="ox-empty ox-empty-page">
                <i class="icon-check-circle" aria-hidden="true"></i>
                <h4>${created.length} ${created.length === 1 ? 'user' : 'users'} imported</h4>
                <p>${response.invited
                    ? 'Each new user is being emailed their sign-in details.'
                    : 'Share the sign-in details below. Everyone must choose their own password when they first sign in.'}</p>
                ${withPasswords.length ? `<a class="btn btn-primary mt-3" download="orvexa-sign-in-details.csv" href="${credentialsCsv(withPasswords)}"><i class="icon-download" aria-hidden="true"></i> Download sign-in details (CSV)</a>
                    <p class="ox-muted small mt-2 mb-0">The passwords are only available now. Store or share the file securely and delete it afterwards.</p>` : ''}
                ${response.skipped.length ? `<p class="text-danger mt-3 mb-0">Skipped because they were added meanwhile: ${response.skipped.map(esc).join(', ')}</p>` : ''}
            </div>`;
    }

    nextBtn.addEventListener('click', () => {
        if (importStep === 'upload') {
            if (!importFile.files.length) {
                Ox.toast('Choose a CSV file first.', { type: 'error' });
                importFile.focus();
                return;
            }
            nextBtn.disabled = true;
            nextBtn.textContent = 'Checking…';
            const body = new FormData();
            body.append('file', importFile.files[0]);
            fetch(baseUrl + 'admin/users/import/preview', { method: 'POST', body, headers: { Accept: 'application/json' } })
                .then(r => r.json())
                .then(response => {
                    if (!response.success) throw new Error(response.message);
                    importToken = response.token;
                    renderPreview(response);
                    setImportStep('preview');
                })
                .catch(error => {
                    Ox.toast(error.message || 'The file could not be checked.', { type: 'error' });
                    nextBtn.disabled = false;
                    nextBtn.textContent = 'Check file';
                });
        } else if (importStep === 'preview') {
            nextBtn.disabled = true;
            nextBtn.textContent = 'Importing…';
            const invite = document.getElementById('importInvite')?.checked ? 1 : '';
            $.post(baseUrl + 'admin/users/import/commit', { token: importToken, send_invites: invite }, null, 'json')
                .done(response => {
                    if (!response.success) {
                        Ox.toast(response.message, { type: 'error' });
                        backBtn.click();
                        return;
                    }
                    importChanged = response.created.length > 0;
                    renderResult(response);
                    setImportStep('done');
                })
                .fail(() => {
                    Ox.toast('The import failed. Please try again.', { type: 'error' });
                    nextBtn.disabled = false;
                });
        }
    });

    // -----------------------------------------------------------------
    // Per-user activity
    // -----------------------------------------------------------------
    const activityPanel = document.getElementById('activityPanel');
    const activityList = document.getElementById('activityList');
    const activityModule = document.getElementById('activityModule');
    const activityMore = document.getElementById('activityMore');
    let activityUser = null;
    let oldestId = 0;

    function activityItem(item) {
        const date = new Date(item.date.replace(' ', 'T'));
        return `<li class="ox-timeline-item">
            <div class="ox-timeline-head">
                <span class="ox-tag ox-tag-gray">${esc(item.module)}</span>
                ${item.product ? `<span class="ox-muted">${esc(item.product)}</span>` : ''}
                <time datetime="${esc(item.date)}" title="${esc(date.toLocaleString())}">${esc(date.toLocaleString('en-GB', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }))}</time>
            </div>
            <div class="ox-timeline-text">${item.html}</div>
        </li>`;
    }

    function loadActivity(reset) {
        if (reset) {
            oldestId = 0;
            activityList.innerHTML = '<li class="ox-empty-inline"><span>Loading…</span></li>';
        }
        activityMore.disabled = true;
        const params = new URLSearchParams({ module: activityModule.value });
        if (oldestId) params.set('before', oldestId);
        fetch(`${baseUrl}admin/users/activity/${activityUser.user_id}?${params}`, { headers: { Accept: 'application/json' } })
            .then(r => r.ok ? r.json() : Promise.reject(new Error(r.status)))
            .then(response => {
                if (reset) activityList.innerHTML = '';
                if (response.modules && activityModule.options.length === 1) {
                    activityModule.insertAdjacentHTML('beforeend', response.modules
                        .map(m => `<option value="${m.id}">${esc(m.name)} (${m.total})</option>`).join(''));
                }
                if (reset && !response.items.length) {
                    activityList.innerHTML = '<li class="ox-empty-inline"><i class="icon-clock" aria-hidden="true"></i><span>No activity recorded yet</span></li>';
                }
                activityList.insertAdjacentHTML('beforeend', response.items.map(activityItem).join(''));
                if (response.items.length) oldestId = response.items[response.items.length - 1].id;
                activityMore.hidden = !response.hasMore;
            })
            .catch(() => {
                activityList.innerHTML = '<li class="ox-empty-inline"><i class="icon-alert-circle" aria-hidden="true"></i><span>Activity could not be loaded.</span></li>';
            })
            .finally(() => { activityMore.disabled = false; });
    }

    function openActivity(user) {
        activityUser = user;
        document.getElementById('activityTitle').textContent = `${user.first_name} ${user.last_name || ''}`.trim();
        document.getElementById('activitySubtitle').textContent = `@${user.username} · ${user.role_name || ''}`;
        activityModule.innerHTML = '<option value="0">All areas</option>';
        bootstrap.Offcanvas.getOrCreateInstance(activityPanel).show();
        loadActivity(true);
    }
    activityModule.addEventListener('change', () => loadActivity(true));
    activityMore.addEventListener('click', () => loadActivity(false));

    render();
}
