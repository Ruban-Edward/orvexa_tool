<!--
    setPermission.php

    @category   View
    @Author     Ruban Edward
    @created    10 July 2024
    @updated
    @purpose    View page to set the user permission for the tool       
-->

<!-- Main Content Section -->
<div class="cls-main">
    <!-- Form to set user permissions -->
    <div class="row">
        <div class="col-sm-12">
            <div class="cls-buttons">
                <!-- Button to open modal for adding new permission -->
                <button data-bs-toggle="modal" data-bs-target="#permissionModal" class="btn primary_button"
                    onclick="permissionModal('add')">
                    <i class="icon-plus-circle sprintListIcon"></i> <span> Add new permission</span>
                </button>
                <!-- Button to open modal for editing permission -->
                <button data-bs-toggle="modal" data-bs-target="#permissionModal" class="button-secondary"
                    onclick="permissionModal('update')">
                    <i class="icon-edit sprintListIcon"></i> <span> Edit permission</span>
                </button>
                <!-- Button to open modal for deleting permission -->
                <button data-bs-toggle="modal" data-bs-target="#permissionModal" class="button-secondary"
                    onclick="permissionModal('delete')">
                    <i class="icon-trash sprintListIcon"></i> <span> Delete permission</span>
                </button>
            </div>
        </div>
    </div>
    <!-- How the stored permissions compare with the tool right now -->
    <p class="ox-perm-sync">
        <i class="icon-refresh-cw" aria-hidden="true"></i>
        Checked against the tool just now: <b><?= (int) $data['routeCount'] ?></b> routes and <b><?= (int) $data['checkCount'] ?></b> screen checks.
        Only permissions for features that exist are listed below.
    </p>

    <?php if ($data['missing']): ?>
        <section class="ox-panel ox-perm-alert is-warning" aria-labelledby="missingTitle">
            <header class="ox-panel-head">
                <h2 class="ox-panel-title" id="missingTitle"><i class="icon-alert-triangle" aria-hidden="true"></i> Features without a permission <span class="ox-badge-count"><?= count($data['missing']) ?></span></h2>
            </header>
            <p class="ox-perm-alert-text">The tool checks these features, but no permission exists for them, so only Super Admins can use them. Create a permission (or reuse a matching old one), then grant it to roles below.</p>
            <ul class="ox-perm-list">
                <?php foreach ($data['missing'] as $item): ?>
                    <li>
                        <div>
                            <code><?= esc($item['routes_url']) ?></code>
                            <span class="ox-muted">checked in <?= esc(implode(', ', array_map('basename', $item['files']))) ?></span>
                        </div>
                        <div class="ox-perm-actions">
                            <?php if ($item['suggestion']): ?>
                                <button type="button" class="btn btn-primary btn-sm" data-relink="<?= (int) $item['suggestion']['permission_id'] ?>" data-route="<?= esc($item['routes_url'], 'attr') ?>">
                                    Use <?= esc(ucwords(str_replace('_', ' ', strtolower($item['suggestion']['permission_name'])))) ?>
                                </button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-secondary btn-sm" data-create-route="<?= esc($item['routes_url'], 'attr') ?>" data-create-name="<?= esc(ucwords(str_replace('_', ' ', strtolower($item['name']))), 'attr') ?>" data-create-module="<?= (int) ($item['module_id'] ?? 0) ?>">
                                Create permission
                            </button>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php if ($data['stale']): ?>
        <section class="ox-panel ox-perm-alert" aria-labelledby="staleTitle">
            <header class="ox-panel-head">
                <h2 class="ox-panel-title" id="staleTitle"><i class="icon-archive" aria-hidden="true"></i> No longer in the tool <span class="ox-badge-count"><?= count($data['stale']) ?></span></h2>
                <button type="button" class="btn btn-secondary btn-sm" id="removeAllStale">Remove all</button>
            </header>
            <p class="ox-perm-alert-text">These permissions point to features that were removed (for example Redmine sync) or to a route that does not exist. Granting them has no effect.</p>
            <ul class="ox-perm-list">
                <?php foreach ($data['stale'] as $item): ?>
                    <li data-stale="<?= (int) $item['permission_id'] ?>">
                        <div>
                            <b><?= esc(ucwords(str_replace('_', ' ', strtolower($item['permission_name'])))) ?></b>
                            <span class="ox-muted"><?= esc(ucwords($item['module_name'])) ?> · <code><?= esc($item['routes_url']) ?></code></span>
                        </div>
                        <div class="ox-perm-actions">
                            <button type="button" class="btn btn-link ox-icon-action is-danger" data-remove="<?= (int) $item['permission_id'] ?>" title="Remove" aria-label="Remove <?= esc($item['permission_name'], 'attr') ?>"><i class="icon-trash-2" aria-hidden="true"></i></button>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <!-- Form to submit user role and permissions -->
    <form id="permissionsForm">

        <!-- User Role Selection -->
        <div class="cls-select">
            <div class="cls-header">
                <label for="selectUser" class="form-label">
                    <h4>Select user role</h4>
                </label>
            </div>
            <div class="cls-body">
                <!-- Dropdown to select user role, with data-url attribute for fetching permissions -->
                <select class="form-select" id="selectUser" name="selectUser" aria-label="User role" required>
                    <option value="" disabled selected>-- Select role --</option>
                    <?php foreach ($data['roles'] as $value) {
                        echo "<option value='{$value['role_id']}'>{$value['role_name']}</option>";
                    } ?>
                </select>
            </div>
        </div>

        <!-- Permissions Selection -->
        <div class="cls-checkbox custom-control custom-checkbox">
            <div class="cls-header">
                <h4>Choose module permission</h4>
            </div>

            <div class="cls-body">
                <!-- Select All Checkbox for all permissions -->
                <div class="cls-main-select">
                    <input type="checkbox" class="form-check-input form-check-primary" id="selectAll">
                    <label for="selectAll" class="form-check-label cls-all">Select all</label><br>
                </div>

                <?php
                // Grouping permissions by module
                $permissions = $data['permissions'];
                $group_permissions = [];

                foreach ($permissions as $value) {
                    $module_name = $value['module_name'];
                    $group_permissions[$module_name][] = $value;
                }

                // Displaying grouped permissions, one card per module
                echo "<div class='ox-perm-grid'>";
                foreach ($group_permissions as $module_name => $permissions) {
                    echo "<section class='ox-perm-module'>";
                    echo "<div class='cls-module-select'>";
                    echo "<h5>" . esc(ucwords($module_name)) . "</h5>";
                    // Select All Checkbox for the module
                    echo "<div class='cls-sub-select'>";
                    echo "<input type='checkbox' class='form-check-input form-check-primary' id='selectAll_{$module_name}' data-module='{$module_name}'>";
                    echo "<label for='selectAll_{$module_name}' class='form-check-label cls-all'>All</label>";
                    echo "</div>";
                    echo "</div>";
                    echo "<div class='checkbox-container'>";

                    // Displaying individual permissions
                    foreach ($permissions as $key => $value) {
                        $formatted_permission_name = ucwords(str_replace('_', ' ', strtolower($value['permission_name'])));
                        echo "<div class='checkbox-item'>";
                        echo "<input type='checkbox' class='form-check-input form-check-primary module-checkbox' 
                                    value='{$value['permission_id']}' id='{$value['permission_name']}_{$key}' name='permissions[]' data-module='{$module_name}'>";
                        echo "<label for='{$value['permission_name']}_{$key}' class='form-check-label' title='" . esc($value['routes_url'], 'attr') . "'>" . esc($formatted_permission_name) . "</label>";
                        echo "</div>";
                    }
                    echo "</div>";
                    echo "</section>";
                }
                echo "</div>";
                ?>

                <!-- Submit Button -->
                <div class="cls-submit">
                    <button type="submit" class="btn primary_button" name="setRoleButton" form="permissionsForm">Set
                        role</button>
                </div>

            </div>
        </div>
    </form>
</div>

<!-- Modal for adding new permission -->
<div class="modal fade" id="permissionModal" tabindex="-1" aria-labelledby="permissionModalLabel" aria-hidden="true"
    data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h1 class="modal-title fs-5" id="permissionModalLabel">Add new permission</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Form to submit new permission details -->
                <form class="row g-3 needs-validation" id="newPermissionForm" novalidate>

                    <input type="hidden" id="operation">

                    <!-- edit modal -->
                    <div class="col-md-10" id="editPermissionName">
                        <label for="editPermissionNameModel" class="form-label">Select the permission</label>
                        <select class="form-select" id="editPermissionNameModel" name="editPermissionNameModel"
                            required>
                            <option value="" disabled selected>Select the permission</option>
                        </select>
                        <div class="valid-feedback">Looks good!</div>
                        <div class="invalid-feedback">Please provide a Url.</div>
                    </div>

                    <div class="col-md-10" id="permissionName">
                        <label for="permissionNameModal" class="form-label">Permission name</label>
                        <input type="text" class="form-control" id="permissionNameModal" name="permissionNameModal"
                            placeholder="Eg: View dashboard" required>
                        <div class="valid-feedback">Looks good!</div>
                        <div class="invalid-feedback">Please provide a permission name.</div>
                    </div>

                    <div class="col-md-10" id="module">
                        <label for="moduleModel" class="form-label">Module</label>
                        <select class="form-select" id="moduleModel" name="moduleModel" required>
                            <option value="" disabled selected>Select the module</option>
                            <?php foreach ($data['module'] as $value) {
                                echo "<option value='{$value['module_id']}'>" . ucfirst($value['module_name']) . "</option>";
                            } ?>
                        </select>
                        <div class="valid-feedback">Looks good!</div>
                        <div class="invalid-feedback">Please choose a Module.</div>
                    </div>

                    <div class="col-md-10" id="routesURL">
                        <label for="routesURLModel" class="form-label">URL routes name</label>
                        <input type="text" class="form-control" id="routesURLModel" name="routesURLModel"
                            placeholder="Start typing, e.g. backlog/" list="routeTargets" autocomplete="off" required
                            aria-describedby="routesURLHelp">
                        <datalist id="routeTargets">
                            <?php foreach ($data['targets'] as $target): ?>
                                <option value="<?= esc($target, 'attr') ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <div class="form-text" id="routesURLHelp">Suggestions come from the routes and screen checks in the tool that have no permission yet.</div>
                        <div class="invalid-feedback" id="routesURLError">Choose a route that exists in the tool.</div>
                    </div>

                    <!-- delete modal -->
                    <div class="col-md-10" id="deletePermission">
                        <label for="deletePermissionModel" class="form-label">Select the permission</label>
                        <select class="form-select" id="deletePermissionModel" name="deletePermissionModel" required>
                            <option value="" disabled selected>Select the permission</option>
                        </select>
                        <div class="valid-feedback">Looks good!</div>
                        <div class="invalid-feedback">Please choose a Module.</div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn primary_button" id="setPermissionButton"
                            form="newPermissionForm" name="setPermissionButton" value="1">Add permission</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Base URL for AJAX requests
    var baseUrl = '<?= base_url() ?>';

    // module name 
    const setPermissionPage = "setPermissionPage";
</script>