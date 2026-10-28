<?php
$canAdd = has_permission('backlog/addTasks');
?>
<dl class="ox-context">
    <div><dt>Product</dt><dd><?= esc(trim(ucfirst(strtolower($data['product_name'])))) ?></dd></div>
    <div><dt>Backlog item</dt><dd><?= esc(trim(ucfirst(strtolower($data['backlog_item_name'])))); ?></dd></div>
    <div><dt>User story</dt><dd><?= "US_" . (int) $data['user_story_id'] ?></dd></div>
    <div><dt>Tasks</dt><dd id="taskTotal"><?= (int) $data['task_count'] ?></dd></div>
</dl>
<p class="ox-story-line">
    <b>As a / an</b> <?= esc(trim(ucfirst(strtolower($data['user_story']['as_a_an'])))) ?>
    <b>I want</b> <?= esc(ucfirst($data['user_story']['i_want'])) ?>
    <b>so that</b> <?= esc(ucfirst($data['user_story']['so_that'])) ?>
</p>

<div class="ox-pagebar">
    <div class="ox-pagebar-actions">
        <?php if ($canAdd): ?>
            <button class="btn btn-primary" id="addbutton" type="button" data-shortcut="new" onclick="addform('add')"
                    aria-keyshortcuts="n" title="Add task (N)">
                <i class="icon-plus" aria-hidden="true"></i> Add task
            </button>
            <form class="ox-quick-add" id="quickAddTask" autocomplete="off">
                <label class="visually-hidden" for="quickAddTitle">Quick add a task</label>
                <input type="text" class="form-control" id="quickAddTitle" minlength="3" maxlength="255"
                       placeholder="Quick add: type a title, press Enter">
                <button type="submit" class="btn btn-secondary" aria-label="Quick add task"><i class="icon-corner-down-left" aria-hidden="true"></i></button>
            </form>
        <?php endif; ?>
    </div>

    <div class="ox-pagebar-actions" id="taskTools">
        <div class="ox-search">
            <i class="icon-search" aria-hidden="true"></i>
            <label class="visually-hidden" for="backlogSearchInput">Search tasks</label>
            <input type="search" id="backlogSearchInput" class="form-control" placeholder="Search task titles">
        </div>
        <button id="filter_btn" type="button" class="btn btn-secondary ox-filter-btn" data-shortcut="filter" aria-keyshortcuts="f"
                aria-controls="filterSidebar" aria-expanded="false" title="Filters (F)">
            <i class="icon-filter" aria-hidden="true"></i> Filter
            <span id="noti" class="ox-filter-count" hidden></span>
        </button>
    </div>
</div>

<div class="ox-chips" id="taskChips" hidden></div>

<div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="add_tasks" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form class="needs-validation" id="taskDetailsForm" method="POST" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="add_tasks">Add task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="formOperation" name="formOperation" value="">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="task_title" class="form-label">Title</label>
                            <input type="text" class="form-control" name="task_title" id="task_title" autocomplete="off"
                                   minlength="3" maxlength="255" required aria-describedby="task_title_error">
                            <div class="invalid-feedback" id="task_title_error">Enter a title of at least 3 characters.</div>
                        </div>
                        <div class="col-12">
                            <label for="task_description" class="form-label">Description</label>
                            <textarea class="form-control" name="task_description" id="task_description" rows="3"
                                      minlength="3" maxlength="1000" required aria-describedby="task_description_error"></textarea>
                            <div class="invalid-feedback" id="task_description_error">Enter a description of at least 3 characters.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="task_statuses" class="form-label">Status</label>
                            <select class="form-select" name="task_status" id="task_statuses" required>
                                <?php foreach ($data['status'] as $value): ?>
                                    <option value="<?= (int) $value['status_id'] ?>"><?= esc($value['status_name']) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="task_priority" class="form-label">Priority</label>
                            <select class="form-select" name="priority" id="task_priority" required>
                                <option value="L">Low</option>
                                <option value="M">Medium</option>
                                <option value="H">High</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="task_assignee" class="form-label">Assignee</label>
                            <select class="form-select" name="assignee_id" id="task_assignee" aria-describedby="task_assignee_help">
                                <option value="">Me</option>
                                <?php foreach ($data['users'] as $value): ?>
                                    <option value="<?= (int) $value['id'] ?>"><?= esc(ucfirst(strtolower($value['name']))) ?></option>
                                <?php endforeach ?>
                            </select>
                            <div class="form-text" id="task_assignee_help">Leave as "Me" to assign it to yourself.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Start date</label>
                            <input type="text" class="form-control flatpickr-no-config flatpickr-input" id="start_date" name="start_date" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">End date</label>
                            <input type="text" class="form-control flatpickr-no-config flatpickr-input" id="end_date" name="end_date" readonly>
                        </div>
                        <div class="col-md-2">
                            <label for="estimated_time" class="form-label">Estimate (h)</label>
                            <input type="number" class="form-control" name="estimated_hours" id="estimated_time" min="0" step="1">
                        </div>
                        <div class="col-md-2">
                            <label for="completed_percentage" class="form-label">Done (%)</label>
                            <input type="number" class="form-control" name="completed_percentage" id="completed_percentage"
                                   min="0" max="100" step="1" aria-describedby="completed_percentage_error">
                            <div class="invalid-feedback" id="completed_percentage_error">Use a value from 0 to 100.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary" id="submitAddAnother" value="another">Save &amp; add another</button>
                    <button type="submit" class="btn btn-primary" id="submitTaskBtn" value="save">Add task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="main-page">
    <div class="row card-container" id="taskList" aria-live="polite"></div>
    <div id="taskPager"></div>
</div>

<div id="empty" hidden></div>

<!-- Filter panel -->
<aside id="filterSidebar" class="filter-sidebar" aria-label="Task filters">
    <div class="sidebar-content">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="filter-heading">Filters</h3>
            <button type="button" id="closeFilterSidebarBtn" class="btn-close" aria-label="Close filters"></button>
        </div>
        <form id="filterformAction">
            <label for="filterPriority" class="form-label filter-text">Priority</label>
            <select name="priority[]" id="filterPriority" class="form-select select" multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="1">
                <option value="H">High</option>
                <option value="M">Medium</option>
                <option value="L">Low</option>
            </select>
            <label for="filterstatus" class="form-label filter-text">Status</label>
            <select name="status[]" id="filterstatus" class="form-select select" multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="1">
                <?php foreach ($data['status'] as $val): ?>
                    <option value="<?= esc($val['status_name'], 'attr') ?>"><?= esc(ucfirst(strtolower($val['status_name']))) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="d-flex justify-content-between mt-4">
                <button type="submit" class="btn btn-primary">Apply</button>
                <button type="button" class="btn btn-secondary" id="resetFiltersBtn">Reset</button>
            </div>
        </form>
    </div>
</aside>
<script>
    var totalCount = <?= (int) $data['task_count'] ?>;
    var assertPath = <?= json_encode(ASSERT_PATH) ?>;
    var pid = <?= (int) $data['product_id'] ?>;
    var pblid = <?= (int) $data['backlog_item_id'] ?>;
    var userStoryId = <?= (int) $data['user_story_id'] ?>;
    var userPermissions = {
        addTask: <?= json_encode($canAdd) ?>,
        updateTask: <?= json_encode(has_permission('backlog/updateTaskById')) ?>,
        deleteTask: <?= json_encode(has_permission('backlog/deletetask')) ?>
    };

    //module name
    const m_tasks = [];
</script>
