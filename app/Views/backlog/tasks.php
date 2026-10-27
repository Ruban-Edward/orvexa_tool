<dl class="ox-context">
    <div><dt>Product</dt><dd><?= esc(trim(ucfirst(strtolower($data['product_name'])))) ?></dd></div>
    <div><dt>Backlog item</dt><dd><?= esc(trim(ucfirst(strtolower($data['backlog_item_name'])))); ?></dd></div>
    <div><dt>User story</dt><dd><?= "US_" . (int) $data['user_story_id'] ?></dd></div>
    <div><dt>Tasks</dt><dd><?= (int) $data['task_count'] ?></dd></div>
</dl>
<p class="ox-story-line">
    <b>As a / an</b> <?= esc(trim(ucfirst(strtolower($data['user_story']['as_a_an'])))) ?>
    <b>I want</b> <?= esc(ucfirst($data['user_story']['i_want'])) ?>
    <b>so that</b> <?= esc(ucfirst($data['user_story']['so_that'])) ?>
</p>
<div class=" d-flex justify-content-between">

    <div>
        <?php if (has_permission('backlog/addTasks')): ?>
            <div>
                <button class="btn primary_button" id="addbutton" data-bs-toggle="modal" data-bs-target="#addTaskModal"
                    onclick="addform('add')">
                    <i class="icon-plus-circle sprintListIcon"></i> Add task </button>
            </div>
        <?php endif; ?>
    </div>

    <div class="d-flex cls-task-action-buttons">

        <?php if ($data['task_count'] > 0): ?>
            <div class="dropdown me-2 cls-search-button">
                <div class="dropdown">
                    <div class="search-container">
                        <div class="search-box">
                            <input type="text" id="backlogSearchInput" class="search-input" placeholder="Search task title|description">
                            <button id="search_btn" class="search-button">
                                <i class="bi bi-search"></i>
                                <span>Search</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="dropdown cls-filter-button">
                <button id="filter_btn" class="button-secondary"><i class="icon-filter"></i> <span class="cls-name">Filter </span>
                <span id="noti" class="badge badge-pill badge-danger"></span>
            </button>
            </div>
        <?php endif ?>
    </div>
</div>
<div class="modal" id="addTaskModal" tabindex="-1" aria-labelledby="add_tasks" aria-hidden="true"
    data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add_tasks">Add tasks</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <div id="add-form">
                    <form class="row g-3 needs-validation" action="" id="taskDetailsForm" method="POST" novalidate>
                        <input type="hidden" id="formOperation" name="formOperation" value="">
                        <input type="hidden" id="taskId" name="taskId" value="">
                        <div class="col-md-6">
                            <label for="task_title" class="form-label">Title:</label>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="task_title" id="task_title" autocomplete="off"
                                placeholder="Title" required>
                            <div class="valid-feedback">Looks good!</div>
                            <div class="invalid-feedback">Enter title</div>
                        </div>

                        <div class="col-md-6">
                            <label for="task_description" class="form-label">Description :</label>
                        </div>
                        <div class="col-md-6">
                            <textarea class="form-control" name="task_description" id="task_description"
                                autocomplete="off" placeholder="Description" required></textarea>
                            <div class="valid-feedback">Looks good!</div>
                            <div class="invalid-feedback">Enter a Description</div>
                        </div>

                        <div class="col-md-6">
                            <label for="task_statuses" class="form-label">Status :</label>
                        </div>
                        <div class="col-md-6">
                            <select class="form-select" name="task_status" id="task_statuses" required>
                                <?php foreach ($data['status'] as $key => $value): ?>
                                    <option value="<?= $value['status_id'] ?>"><?= $value['status_name'] ?></option>
                                <?php endforeach ?>
                            </select>
                            <div class="valid-feedback">Looks good!</div>
                            <div class="invalid-feedback">select a status</div>
                        </div>

                        <div class="col-md-6">
                            <label for="task_priority" class="form-label">Priority:</label>
                        </div>
                        <div class="col-md-6">
                            <select class="form-select" name="priority" id="task_priority" required>
                                <option value="L">Low</option>
                                <option value="M">Medium</option>
                                <option value="H">High</option>
                            </select>
                            <div class="valid-feedback">Looks good!</div>
                            <div class="invalid-feedback">select a priority</div>
                        </div>

                        <div class="col-md-6">
                            <label for="task_assignee" class="form-label">Assignee :</label>
                        </div>
                        <div class="col-md-6">
                            <select class="form-select" name="assignee_id" id="task_assignee">
                                <option value="">Select User</option>
                                <?php foreach ($data['users'] as $key => $value): ?>
                                    <option value="<?= $value['id'] ?>"><?= ucfirst(strtolower($value['name'])) ?></option>;
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="estimated_time" class="form-label">Estimated Time (Hours):</label>
                        </div>
                        <div class="col-md-6">
                            <input type="number" class="form-control" name="estimated_hours" id="estimated_time"
                                autocomplete="off" placeholder="Estimated hours" min="0" step="1">
                        </div>
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start date:</label>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control mb-2 flatpickr-no-config flatpickr-input"
                                id="start_date" name="start_date" placeholder="Click to select start date"
                                readonly="readonly">
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End date:</label>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control mb-2 flatpickr-no-config flatpickr-input"
                                id="end_date" name="end_date" placeholder="Click to select the end date"
                                readonly="readonly">
                        </div>
                        <div class="col-md-6">
                            <label for="completed_percentage" class="form-label">Completed percentage:</label>
                        </div>
                        <div class="col-md-6">
                            <input type="number" class="form-control" name="completed_percentage"
                                id="completed_percentage" autocomplete="off" placeholder="Completed Percentage" min="0"
                                max="100" step="1">
                            <div class="valid-feedback">Looks good!</div>
                            <div class="invalid-feedback">Maximum completed percentage is 100</div>
                        </div>


                        <div class="col-md-12 d-flex justify-content-center">
                            <button type="submit" class="btn primary_button" id="submitTaskBtn">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="main-page">
    <div class="row card-container" id="taskList">
        <div class='col-md-12'>
            <div class='card'>
                <!-- Card Content -->
            </div>
        </div>
    </div>
    <div id="paginationControls" class="d-flex justify-content-between align-items-center mt-3">
        <button id="prevPage" class="button-secondary">Prev</button>
        <span id="pageInfo" class="mx-3"></span>
        <button id="nextPage" class="button-secondary">Next</button>
    </div>
</div>

<!-- Sidebar for filter -->
<div id="filterSidebar" class="filter-sidebar">
    <div class="sidebar-content">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="filter-heading">Filters</h3>
            <button type="button" id="closeFilterSidebarBtn" class="btn-filterclose align-item-center"></button>
        </div>
        <form id="filterformAction">
            <label for="filterStatus" class="form-label filter-text">Priority</label>
            <select name="sprintname[]" id="filterPriority" placeholser="Select Sprint Name" class="form-select select" multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="1">
                <option value="H">High priority</option>
                <option value="M">Medium priority</option>
                <option value="L">Low priority</option>
            </select>
            <label for="filterStatus" class="form-label filter-text">Status</label>
            <select name="sprintname[]" id="filterstatus" placeholser="Select Sprint Name" class="form-select select" multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="1">
                <?php foreach ($data['status'] as $val): ?>
                    <option value="<?= ucfirst(strtolower($val['status_name'])); ?>">
                        <?= ucfirst(strtolower($val['status_name'])); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="d-flex justify-content-between mt-4">
                <button type="submit" class="btn primary_button ">Apply </button>
                <button type="reset" class="btn button-secondary " id="resetFiltersBtn">Reset</button>
            </div>
        </form>
    </div>
</div>
<div id ="empty" class="align-items-center justify-content-center">
    <div class="text-center" style="margin-top:30px;">
        <h1 class="display-1 text-primary mb-4"><span class="bi bi-emoji-frown cls-noProductIcon"></span></h1>
        <h2 class=" text-primary mb-4">No task found</h2>
    </div>
</div>
<script>
    var totalCount = <?= ($data['task_count']) ?>;
    var assertPath = <?= json_encode(ASSERT_PATH) ?>;
    var pid = <?= $data['product_id'] ?>;
    var pblid = <?= $data['backlog_item_id'] ?>;
    var userStoryId = <?= $data['user_story_id'] ?>;
    var userPermissions = {
        updateTask: <?php echo json_encode(has_permission('backlog/updateTaskById')); ?>,
        deleteTask: <?php echo json_encode(has_permission('backlog/deletetask')); ?>
    }

    //module name 
    const m_tasks = [];
</script>