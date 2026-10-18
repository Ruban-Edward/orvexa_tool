<!--
    adminSettings.php

    @category   View
    @purpose    View page to manage product owner
    @autor      Ruban Edward
-->

<div class="cls-main">
    <div class="cls-sidebar">
        <button class="cls-btn" data-div="productUserDiv">
            <div class="cls-settings">
                <div class="tick-icon"><i class="fas fa-check"></i></div>
                <h2><i class="bi bi-person-gear"></i></h2>
                <h6>Product Owner</h6>
            </div>
        </button>
        <button class="cls-btn" data-div="pokerConfigDiv">
            <div class="cls-settings">
                <div class="tick-icon"><i class="fas fa-check"></i></div>
                <h2><i class="bi bi-suit-club"></i></h2>
                <h6>Poker</h6>
            </div>
        </button>
        <button class="cls-btn" data-div="addRoleDiv">
            <div class="cls-settings">
                <div class="tick-icon"><i class="fas fa-check"></i></div>
                <h2><i class="bi bi-person-badge"></i></h2>
                <h6>Add Role</h6>
            </div>
        </button>
        <button class="cls-btn" data-div="holidayDiv">
            <div class="cls-settings">
                <div class="tick-icon"><i class="fas fa-check"></i></div>
                <h2><i class="fas fa-umbrella-beach"></i></h2>
                <h6>Holidays</h6>
            </div>
        </button>
        <button class="cls-btn" data-div="tShirtDiv">
            <div class="cls-settings">
                <div class="tick-icon"><i class="fas fa-check"></i></div>
                <h2><i class="fas fa-tshirt"></i></h2>
                <h6>T-shirt size</h6>
            </div>
        </button>
        <button class="cls-btn" data-div="customerDiv">
            <div class="cls-settings">
                <div class="tick-icon"><i class="fas fa-check"></i></div>
                <h2><i class="bi bi-building"></i></h2>
                <h6>Customers</h6>
            </div>
        </button>
    </div>
    <div class="cls-sidebarContent">
        <div id="customerDiv">
            <h4>Customer Management</h4><br>
            <form class="row g-3" method="post" action="<?= base_url('admin/addCustomer') ?>">
                <?= csrf_field() ?>
                <div class="col-md-8">
                    <label for="customer_name" class="form-label">Customer name</label>
                    <input type="text" id="customer_name" name="customer_name" class="form-control" maxlength="50" required>
                </div>
                <div class="col-md-4 d-flex align-items-end"><button type="submit" class="btn primary_button"><i class="bi bi-plus-circle"></i> Add customer</button></div>
            </form>
            <hr>
            <h5>Customers</h5>
            <div class="list-group">
                <?php foreach ($data['customers'] as $customer): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><?= esc($customer['customer_name']) ?></span>
                        <form method="post" action="<?= base_url('admin/toggleCustomer') ?>" class="m-0">
                            <?= csrf_field() ?>
                            <input type="hidden" name="customer_id" value="<?= (int) $customer['customer_id'] ?>">
                            <?php if ($customer['is_deleted'] === 'N'): ?>
                                <span class="badge bg-success me-2">Active</span>
                                <button type="submit" class="btn btn-sm btn-outline-danger">Set inactive</button>
                            <?php else: ?>
                                <span class="badge bg-secondary me-2">Inactive</span>
                                <button type="submit" class="btn btn-sm btn-outline-success">Set active</button>
                            <?php endif; ?>
                        </form>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($data['customers'])): ?><p class="text-muted">No active customers found.</p><?php endif; ?>
            </div>
        </div>
        <!-- Manage product owner -->
        <div id="productUserDiv">
            <h4>Manage Product Owner</h4><br>
            <form class="row g-3 needs-validation" id="productUserForm" novalidate>
                <div class="productUser" id="productUser">
                    <label for="productSelect" class="form-label">Select the product</label>
                    <select class="form-select" id="productSelect" name="productSelect" required>
                        <option value="" disabled selected>Select the product</option>
                        <?php foreach ($data['products'] as $value) {
                            echo "<option value='{$value['external_project_id']}'>{$value['product_name']}</option>";
                        } ?>
                    </select>
                    <div class="valid-feedback">Looks good!</div>
                    <div class="invalid-feedback">Please choose a product.</div>
                </div>
                <br>
                <div class="productUserMember" id="productUserMember">
                    <label for="productUserMemberSelect" class="form-label">Select the owner</label>
                    <select class="form-select" id="productUserMemberSelect" name="productUserMemberSelect" required>
                        <option value="" disabled selected>Select the owner</option>
                    </select>
                    <div class="valid-feedback">Looks good!</div>
                    <div class="invalid-feedback">Please choose a owner.</div>
                </div>
                <br>
                <div class="cls-button">
                    <button type="submit" class="btn primary_button">Add owner</button>
                </div>
            </form>
        </div>
        <!-- Poker configuration -->
        <div id="pokerConfigDiv">
            <h4>Poker Configuration</h4><br>
            <form class="row g-3 needs-validation" id="pokerConfigForm" novalidate>
                <div class="pokerInput">
                    <label for="poker" class="form-label">Poker limit</label>
                    <input type="number" class="form-control" id="poker" name="poker" placeholder="Set poker limit"
                        min="0" required>
                    <div class="valid-feedback">Looks good!</div>
                    <div class="invalid-feedback">Please give a poker config.</div>
                </div>
                <div class="cls-button">
                    <button type="submit" class="btn primary_button">Set limit</button>
                </div>
            </form>
        </div>
        <!-- Add new role settings -->
        <div id="addRoleDiv">
            <h4>Role Management</h4><br>
            <form class="row g-3 needs-validation" id="addRoleForm" novalidate>
                <label for="addRole" class="form-label">Add new role</label>
                <div class="addRoleInput">
                    <input type="text" class="form-control" id="addRole" name="addRole" placeholder="Add role" required>
                    <button type="submit" class="btn primary_button cls-icons"><i class="bi bi-plus-circle"></i>
                        Add</button>
                </div>
            </form><br>
            <button class="cls-delete form-label">Delete role</button>
            <form class="needs-validation" id="deleteRoleForm" style="display:none;">
                <div class="deleteRole" id="deleteRole">
                    <select class="form-select" id="deleteRoleSelect" name="deleteRoleSelect" required>
                        <option value="" disabled selected>Select the role</option>
                    </select>
                    <button type="submit" class="btn primary_button cls-icons"><i class="bi bi-trash"></i>
                        Delete</button>
                </div>
            </form>
        </div>
        <!-- Holidays management -->
        <div id="holidayDiv">
            <h4>Holidays Settings</h4><br>
            <form class="row g-3 needs-validation" id="holidayForm" novalidate>
                <div class="holidayTitleInput">
                    <label for="holidayTitle" class="form-label">Holiday title</label>
                    <input type="text" class="form-control" id="holidayTitle" name="holidayTitle"
                        placeholder="Add holiday title" required>
                    <div class="valid-feedback">Looks good!</div>
                    <div class="invalid-feedback">Please give a holiday title.</div>
                </div>
                <div class="holidayDate">
                    <label for="holidayDate" class="form-label">Holiday Date</label>
                    <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input" id="holidayDate"
                        name="holidayDate" placeholder="Select date" required>
                    <div class="valid-feedback">Looks good!</div>
                    <div class="invalid-feedback">Please give a holiday date.</div>
                </div>
            </form>

            <form id="fileUploadForm" enctype="multipart/form-data" style="display:none;" novalidate>
                <div class="mb-3">
                    <label for="file" class="form-label">Choose file</label>
                    <a class="download" href="<?= base_url('backlog/downloadReference/holidayFileFormat.csv') ?>">
                        <i class="icon-download"></i> Reference file
                    </a>
                    <input type="file" class="form-control" id="file" name="file"
                        placeholder="Only CSV file can be uploaded" accept=".csv" required>
                    <span style="color:red;display:none">Only CSV files are accepted</span>
                    <div class="valid-feedback">Looks good!</div>
                    <div class="invalid-feedback">Please Upload the csv file.</div>
                </div>
            </form>

            <div class="cls-button">
                <button id="importButton" class="import"><i class="icon-download"></i> Import file</button>
                <button id="backButton" class="btn btn-secondary" style="display:none">Back</button>
                <button type="submit" class="btn primary_button" id="holidayButton">Add holiday</button>
            </div>
        </div>
        <!-- product t shirt management -->
        <div id="tShirtDiv">
            <h4>Product T-shirt Size</h4><br>
            <form class="row g-3 needs-validation" id="tShirtForm" novalidate>
                <div class="parentProduct" id="parentProduct">
                    <label for="parentProductSelect" class="form-label">Select the Product</label>
                    <select class="form-select" id="parentProductSelect" name="parentProductSelect" required>
                        <option value="" disabled selected>Select the Product</option>
                        <?php foreach ($data['parentProduct'] as $value) {
                            echo "<option value='{$value['external_project_id']}'>{$value['product_name']}</option>";
                        } ?>
                    </select>
                    <div class="valid-feedback">Looks good!</div>
                    <div class="invalid-feedback">Please choose a owner.</div>
                </div>
                <div id="tshirtContainer">
                    <div class="t-shirtValues">
                        <div class="col-sm-5">
                            <label for="t-shirtName" class="form-label">T-Shirt size name</label>
                            <input type="text" class="form-control" name="t-shirtName[]"
                                placeholder="Set T-Shirt size name" required>
                            <div class="valid-feedback">Looks good!</div>
                            <div class="invalid-feedback">Please give a T-Shirt name.</div>
                        </div>
                        <div class="col-sm-2">
                            <!-- Empty column for spacing -->
                        </div>
                        <div class="col-sm-5">
                            <label for="t-shirtValue" class="form-label">T-Shirt size value</label>
                            <input type="text" class="form-control" name="t-shirtValue[]"
                                placeholder="Set T-Shirt size value" required>
                            <div class="valid-feedback">Looks good!</div>
                            <div class="invalid-feedback">Please give a T-Shirt value.</div>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="button" id="addTshirtSize" class="sec-icon"><i class="bi bi-plus-circle"></i></button>
                </div>
                <div class="cls-button">
                    <button type="submit" class="btn primary_button">add T-shirt size</button>
                </div>
            </form>
        </div>
    </div>
</div>
