<!--
    manageUser.php

    @category   View
    @purpose    List, add and edit users: details, role, product access, status and password reset
    @created    9 July 2024
    @autor      Ruban Edward
-->
<?php
$canEditRole = has_permission('admin/updaterole');
$users = $data['showUser'];
$activeCount = count(array_filter($users, static fn ($u) => $u['is_deleted'] === 'N'));
?>
<div class="ox-users" id="manageUsers">
    <div class="ox-pagebar">
        <div class="ox-pagebar-actions">
            <button type="button" class="btn btn-primary" id="addUserBtn" data-shortcut="new" aria-keyshortcuts="n" title="Add user (N)">
                <i class="icon-user-plus" aria-hidden="true"></i> Add user
            </button>
            <button type="button" class="btn btn-secondary" id="importUsersBtn">
                <i class="icon-upload" aria-hidden="true"></i> Import CSV
            </button>
            <span class="ox-users-summary"><b id="activeCount"><?= $activeCount ?></b> active · <?= count($users) - $activeCount ?> inactive</span>
        </div>
        <div class="ox-pagebar-actions ox-users-filters">
            <div class="ox-segmented" role="radiogroup" aria-label="Status">
                <input type="radio" name="userStatus" id="statusActive" value="active" checked>
                <label for="statusActive">Active</label>
                <input type="radio" name="userStatus" id="statusInactive" value="inactive">
                <label for="statusInactive">Inactive</label>
                <input type="radio" name="userStatus" id="statusAll" value="all">
                <label for="statusAll">All</label>
            </div>
            <select class="form-select" id="roleFilter" aria-label="Filter by role">
                <option value="">All roles</option>
                <?php foreach ($data['roles'] as $role): ?>
                    <option value="<?= (int) $role['role_id'] ?>"><?= esc($role['role_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="ox-search">
                <i class="icon-search" aria-hidden="true"></i>
                <label class="visually-hidden" for="userSearchInput">Search users</label>
                <input type="search" id="userSearchInput" class="form-control" placeholder="Name, username or email">
            </div>
        </div>
    </div>

    <section class="ox-panel">
        <div class="table-responsive">
            <table class="table ox-users-table mb-0">
                <thead>
                    <tr>
                        <th scope="col">User</th>
                        <th scope="col">Email</th>
                        <th scope="col">Role</th>
                        <th scope="col">Products</th>
                        <th scope="col">Last sign-in</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody id="usersBody"></tbody>
            </table>
        </div>
        <div id="usersEmpty" hidden></div>
    </section>
    <div id="usersPager"></div>
</div>

<!-- Add / edit user -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content" id="userForm" novalidate>
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Add user</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="user_id" id="userId">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="firstName">First name</label>
                        <input type="text" class="form-control" id="firstName" name="first_name" maxlength="50" required autocomplete="off">
                        <div class="invalid-feedback" data-error="first_name"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="lastName">Last name</label>
                        <input type="text" class="form-control" id="lastName" name="last_name" maxlength="50" autocomplete="off">
                        <div class="invalid-feedback" data-error="last_name"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" minlength="3" maxlength="50" required
                               pattern="[A-Za-z0-9._\-]+" autocomplete="off" aria-describedby="usernameHelp">
                        <div class="form-text" id="usernameHelp">Used to sign in. Letters, numbers, dots, dashes and underscores.</div>
                        <div class="invalid-feedback" data-error="username"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="emailId">Email</label>
                        <input type="email" class="form-control" id="emailId" name="email_id" maxlength="50" required autocomplete="off">
                        <div class="invalid-feedback" data-error="email_id"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="roleId">Role</label>
                        <select class="form-select" id="roleId" name="role_id" required <?= $canEditRole ? '' : 'data-locked="1"' ?>>
                            <option value="" disabled selected>Select a role</option>
                            <?php foreach ($data['roles'] as $role): ?>
                                <option value="<?= (int) $role['role_id'] ?>" <?= (int) $role['role_id'] === 8 ? 'data-default="1"' : '' ?>><?= esc($role['role_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback" data-error="role_id"></div>
                    </div>
                    <div class="col-md-6" id="employeeIdField">
                        <label class="form-label" for="employeeId">Employee ID <span class="ox-optional">(optional)</span></label>
                        <input type="number" class="form-control" id="employeeId" name="employee_id" min="1" step="1" aria-describedby="employeeIdHelp">
                        <div class="form-text" id="employeeIdHelp">Leave empty to assign the next free ID. It can't be changed later.</div>
                        <div class="invalid-feedback" data-error="employee_id"></div>
                    </div>
                    <div class="col-12">
                        <fieldset>
                            <legend class="form-label d-flex align-items-center justify-content-between">
                                <span>Product access</span>
                                <span class="ox-product-picks-tools">
                                    <button type="button" class="ox-link-btn" data-products="all">Select all</button>
                                    <button type="button" class="ox-link-btn" data-products="none">Clear</button>
                                </span>
                            </legend>
                            <?php if ($data['products'] === []): ?>
                                <p class="form-text">There are no products yet.</p>
                            <?php else: ?>
                                <div class="ox-product-picks">
                                    <?php foreach ($data['products'] as $product): ?>
                                        <?php $pid = (int) $product['product_id']; ?>
                                        <div class="ox-product-pick">
                                            <label class="ox-product-pick-name">
                                                <input type="checkbox" class="form-check-input" name="products[]" value="<?= $pid ?>">
                                                <span><?= esc($product['product_name']) ?></span>
                                            </label>
                                            <select class="form-select form-select-sm" name="product_role[<?= $pid ?>]" aria-label="Role on <?= esc($product['product_name'], 'attr') ?>" disabled>
                                                <?php foreach ($data['roles'] as $role): ?>
                                                    <option value="<?= (int) $role['role_id'] ?>"><?= esc($role['role_name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="form-text">The user only sees backlogs, sprints and meetings of these products. The role can differ per product, for example Product Owner on one and Developer on another.</div>
                            <?php endif; ?>
                        </fieldset>
                    </div>
                    <div class="col-12" id="inviteField">
                        <?php if ($data['mailReady']): ?>
                            <label class="form-check">
                                <input type="checkbox" class="form-check-input" name="send_invite" value="1" checked>
                                <span class="form-check-label">Email the sign-in details to the user</span>
                            </label>
                        <?php else: ?>
                            <p class="form-text mb-0"><i class="icon-mail" aria-hidden="true"></i> Email isn't set up, so you'll get a temporary password to share with the user.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveUserBtn">Create user</button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk import -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import users from CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <section data-step="upload">
                    <form id="importForm" class="ox-import-drop" enctype="multipart/form-data">
                        <i class="icon-upload" aria-hidden="true"></i>
                        <label class="form-label" for="importFile">Choose a CSV file</label>
                        <input type="file" class="form-control" id="importFile" name="file" accept=".csv,text/csv" required>
                        <p class="form-text mb-0">Up to 500 users, 1 MB. Nothing is saved until you confirm on the next step.</p>
                    </form>
                    <div class="ox-import-help">
                        <h6>Columns</h6>
                        <ul>
                            <li><code>first_name</code>, <code>username</code>, <code>email</code> — required</li>
                            <li><code>last_name</code>, <code>employee_id</code> — optional (a free ID is assigned when empty)</li>
                            <li><code>role</code> — role name, for example <em>Developer</em>; defaults to Developer</li>
                            <li><code>products</code> — product names separated by <code>;</code>. Add <code>:Role</code> for a different role on that product, for example <em>Orvexa Tool:Product Owner; Dev Road Maps</em></li>
                        </ul>
                        <a class="btn btn-secondary btn-sm" href="<?= ASSERT_PATH ?>admin/users/import/template"><i class="icon-download" aria-hidden="true"></i> Download template</a>
                    </div>
                </section>
                <section data-step="preview" hidden>
                    <p class="ox-import-summary" id="importSummary" aria-live="polite"></p>
                    <div class="table-responsive ox-import-table">
                        <table class="table ox-users-table mb-0">
                            <thead><tr><th>Line</th><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Products</th><th>Check</th></tr></thead>
                            <tbody id="importRows"></tbody>
                        </table>
                    </div>
                    <?php if ($data['mailReady']): ?>
                        <label class="form-check mt-3">
                            <input type="checkbox" class="form-check-input" id="importInvite" checked>
                            <span class="form-check-label">Email each new user their sign-in details</span>
                        </label>
                    <?php else: ?>
                        <p class="form-text mt-3 mb-0">Email isn't set up: after the import you can download a CSV with each user's temporary password.</p>
                    <?php endif; ?>
                </section>
                <section data-step="done" hidden>
                    <div id="importResult"></div>
                </section>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="importBack" hidden>Choose another file</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="importCancel">Cancel</button>
                <button type="button" class="btn btn-primary" id="importNext">Check file</button>
            </div>
        </div>
    </div>
</div>

<!-- Activity -->
<aside class="offcanvas offcanvas-end ox-activity" tabindex="-1" id="activityPanel" aria-labelledby="activityTitle">
    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title" id="activityTitle">Activity</h5>
            <div class="ox-user-meta" id="activitySubtitle"></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <select class="form-select form-select-sm mb-3" id="activityModule" aria-label="Filter activity by area">
            <option value="0">All areas</option>
        </select>
        <ol class="ox-timeline" id="activityList" aria-live="polite"></ol>
        <button type="button" class="btn btn-secondary btn-sm w-100" id="activityMore" hidden>Show older activity</button>
    </div>
</aside>

<script>
    var baseUrl = '<?= base_url() ?>';
    let data = <?= json_encode($users) ?>;
    const userCapabilities = { editRole: <?= json_encode($canEditRole) ?>, mailReady: <?= json_encode($data['mailReady']) ?> };

    //module name
    const manageUser = "manageUser";
</script>
