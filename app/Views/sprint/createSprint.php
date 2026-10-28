<?php
/**
 * @author JEEVA
 * 
 * @modified-by JEEVA
 * @created-date 04-07-2024
 * @modified-date 10-07-2024
 * 
 */
?>

<script>
    const BASE_URL = "<?= ASSERT_PATH ?>";
    const sprintActivity = <?php $sprintActivity = json_encode($data['sprintActivity']);
    echo $sprintActivity; ?>;
    const editMode = <?php $editMode = json_encode($data['edit']);
    echo $editMode; ?>;

    //module name
    const m_createSprint = [];
</script>
<?php if (!$data['edit']): ?>
    <div class="process-flow-container">
        <div class="process-step" id="step-sprint-detail">
            <div class="icon-container">
                <i class="icon-flag"></i>
            </div>
            <p>Sprint detail</p>
        </div>
        <div class="process-step" id="step-user-stories">
            <div class="icon-container">
                <i class="fas fa-book"></i>
            </div>
            <p>Backlog board</p>
        </div>
        <div class="process-step" id="step-add-members">
            <div class="icon-container">
                <i class="fas fa-users"></i>
            </div>
            <p>Scrum members</p>
        </div>
        <div class="process-step" id="step-sprint-planning">
            <div class="icon-container">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <p>Sprint planning</p>
        </div>
        <div class="process-step" id="step-sprint-goal">
            <div class="icon-container">
                <i class="fas fa-bullseye"></i>
            </div>
            <p>Submit</p>
        </div>
    </div>
<?php endif; ?>
<!-- Main content -->
<?php if (!$data['edit']): ?>
    <!-- Form to both create and edit the sprint -->
    <form id="createSprintForm" class="fade-in g-3 needs-validation" action="<?= ASSERT_PATH ?>sprint/createsprint"
        method="post" novalidate>
        <!-- Form Field: Sprint Details -->
    <?php endif; ?>
    <?php if ($data['edit']): ?>
        <script>
            const sprintId = <?php $sprint = $data['sprint_id'];
            echo $sprint; ?>;
            console.log(sprintId, "hello");
            const sprint_data = <?php $sprint_data = json_encode($data['sprint_data']);
            echo $sprint_data; ?>;
            const task_in_sprint = <?php $task_in_sprint = json_encode($data['task_in_sprint_id']);
            echo $task_in_sprint; ?>;
            const member_in_sprint = <?php $member_in_sprint = json_encode($data['member_in_sprint']);
            echo $member_in_sprint; ?>;


            const status = <?php $status = json_encode($data['status']);
            echo $status; ?>;
            const data = <?php $task = json_encode($data['tasks']);
            echo $task; ?>;
            console.log(data);
        </script>
        <form id="editSprintForm" class="fade-in g-3 needs-validation" action="<?= ASSERT_PATH ?>sprint/update"
            method="post" novalidate>
            <input type="hidden" name="sprint_id" value="<?= $data['sprint_id'] ?>">
        <?php endif; ?>
        <div class="card mb-4 main-div" id="sprintDetails">
            <div class="card-header">
                <h5 class="mb-0 headerName">Sprint detail</h5>
            </div>
            <div class="card-body">
            <!-- <?php if (!$data['edit']): ?>
                <div class="cls-sprintDetailsSpan">Select product to select tasks</div>
                <?php endif; ?> -->
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="productName" class="form-label">Product name</label>
                        <?php if ($data['edit']): ?>
                            <select class="form-select" id="productName" name="productName" disabled>
                                <option value="" disabled selected>Select Product</option>
                                <?php foreach ($data['product'] as $product): ?>
                                    <option value="<?= $product['product_id'] ?>"><?= $product['product_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                        <?php if (!$data['edit']): ?>
                            <select class="form-select" id="productName" name="productName" required>
                                <option value="" disabled selected>Select product</option>
                                <?php foreach ($data['product'] as $product): ?>
                                    <option value="<?= $product['product_id'] ?>"><?= $product['product_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                        <div class="invalid-feedback">
                            Please select a product name.
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="customer" class="form-label">Customer</label>
                        <?php if (!$data['edit']): ?>
                            <select class="form-select" id="customer" name="customer" required>
                                <option value="" disabled selected>Select Customer</option>
                                <?php foreach ($data['customer'] as $customer): ?>
                                    <option value="<?= $customer['customer_id'] ?>"><?= $customer['customer_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                        <?php if ($data['edit']): ?>
                            <select class="form-select" id="customer" name="customer" disabled>
                                <option value="" disabled selected>Select customer</option>
                                <?php foreach ($data['customer'] as $customer): ?>
                                    <option value="<?= $customer['customer_id'] ?>"><?= $customer['customer_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                        <div class="invalid-feedback">
                            Please select a customer name.
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="sprintName" class="form-label">Sprint Name</label>
                        <?php if (!$data['edit']): ?>
                            <input type="text" class="form-control" id="sprintName" name="sprintName"
                                placeholder="Sprint Name" required>
                        <?php endif; ?>
                        <?php if ($data['edit']): ?>
                            <input type="text" class="form-control" id="sprintName" name="sprintName"
                                placeholder="Sprint Name" disabled>
                        <?php endif; ?>
                        <div class="invalid-feedback">
                            Please provide sprint name.
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="sprintVersion" class="form-label">Sprint version</label>
                        <?php if (!$data['edit']): ?>
                            <input type="number" step="0.1" class="form-control" id="sprintVersion" name="sprintVersion"
                                placeholder="Sprint Version" required>
                        <?php endif; ?>
                        <?php if ($data['edit']): ?>
                            <input type="number" step="0.1" class="form-control" id="sprintVersion" name="sprintVersion"
                                placeholder="Sprint Version" disabled>
                        <?php endif; ?>
                        <div class="invalid-feedback">
                            Please select a sprint version.
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="sprintDuration" class="form-label">Sprint duration</label>
                        <select class="form-select" id="sprintDuration" name="sprintDuration" required>
                            <option value="" disabled selected>Select Duration</option>
                            <?php foreach ($data['sprintDuration'] as $duration): ?>
                                <option value="<?= $duration['sprint_duration_id'] ?>">
                                    <?= $duration['sprint_duration_value'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                            Please select a sprint duration.
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="sprintDuration" class="form-label">Start Date</label>
                        <!-- <label for="startDate" class="form-label">Start Date</label> -->
                        <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input" id="startDate"
                            name="startDate" accept="" placeholder="Select Date" readonly="readonly" required>
                        <!-- <input type="date" class="form-control" id="startDate" name="startDate" required> -->
                        <div class="invalid-feedback">
                            Please select a start date.
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <!-- <label for="sprintDuration" class="form-label">Sprint Duration</label> -->
                        <label for="sprintDuration" class="form-label">End date</label>
                        <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input" id="endDate"
                            name="endDate" accept="" placeholder="Select Date" readonly="readonly" required>
                        <!-- <input type="date" class="form-control" id="endDate" name="endDate" required> -->
                        <div class="invalid-feedback">
                            Please select a end date.
                        </div>
                    </div>
                    <?php if ($data['edit']): ?>
                        <div class="col-md-3 mb-3">
                            <!-- <label for="sprintDuration" class="form-label">Sprint Duration</label> -->
                            <label for="sprintDuration" class="form-label">Sprint status</label>
                            <select class="form-select" id="sprintStatus" name="sprintStatus" required>
                                <option value="" disabled selected>Select status</option>
                                <?php foreach ($data['status'] as $status): ?>
                                    <option value="<?php echo htmlspecialchars($status['module_status_id']); ?>">
                                        <?php echo htmlspecialchars($status['status_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Please select a sprint status.
                            </div>
                        </div>
                    <?php endif; ?>
                    <div  id="sprintGoal">
                        <div class="card-header main-div">
                            <h5 class="mb-0 headerName">Sprint goal</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <!-- <textarea name="sprintGoal" id="sprintGoal" cols="30" rows="10" required></textarea> -->
                                    <textarea id="default" name="default" cols="30" rows="10" required></textarea>
                                    <div class="invalid-feedback">
                                        Please select a sprint goal.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!$data['edit']): ?>
            <div id="product-specific-sections" style="display: none;">
            <?php endif; ?>
            <!-- Form Field: Select User Stories section -->
            <div class="card mb-4 main-div" id="userStories">
                <div class="card-header" onclick="toggleCollapse('exampleCollapse1')" data-bs-toggle="collapse" data-bs-target="#SelectUserStories"
                    aria-expanded="false" aria-controls="SelectUserStories">
                    <h5 class="mb-0 headerName">
                        <div class="row " style="align-items: center; justify-content: space-between;">
                            <div class="col-2 d-flex justify-content-start">
                                Backlog board
                            </div>
                            <div class="col-2 d-flex justify-content-center">
                                <div class="cls-backspan-margin">
                                    <p class="backSpan">Backlog </p>
                                    <p class="backlog-percent epicSpan"></p>
                                </div>
                            </div>
                            <div class="col-2 d-flex justify-content-center ">
                                <div>
                                    <p class="backSpan">Epic</p>
                                    <p class="epic-percent epicSpan"></p>
                                </div>
                            </div>
                            <div class="col-2 d-flex justify-content-center">
                                <div>
                                    <p class="backSpan">User stories</p>
                                    <p class="user-story-percent epicSpan"></p>
                                </div>
                            </div>
                            <div class="col-2 d-flex justify-content-center">
                                <div>
                                    <p class="backSpan">Tasks </p>
                                    <p class="task-percent epicSpan"></p>
                                </div>

                            </div>
                            <div class="col-1 d-flex justify-content-end">
                                <i class="fas fa-chevron-down float-end collapsible-icon"></i>
                            </div>

                        </div>
                    </h5>
                </div>
                <div class="collapse" id="exampleCollapse1">
                    <div class="card-body">
                        <div class="dropdown">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#projectModal" id="SelectSprintTasks">
                                Select sprint tasks
                            </button>
                            <?php if (!$data['edit']): ?>
                                <div id="SelectSprint">
                                <?php endif; ?>
                                <div class="container mt-5">
                                    <div class="row">
                                        <div class="col-6">
                                            <h2 class="headerSelectedTasks">Selected tasks </h2>
                                        </div>
                                        <div class="col-4">
                                            <button id="clearAllBtn" class="btn btn-danger btn-sm mb-3">Clear
                                                all</button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="card shadow-sm">
                                                <div class="card-body">
                                                    <ul id="selectedItemsList" class="list-group list-group-flush">
                                                        <!-- Selected items will be dynamically added here -->
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card shadow-sm">
                                                <div class="card-body">
                                                    <h5 class="card-title">Summary</h5>
                                                    <p class="card-text">Sprint start date : <span
                                                            id="SprintStartDate">0</span></p>
                                                    <p class="card-text">Sprint end date  : <span
                                                            id="SprintEndDate">0</span></p>
                                                    <p class="card-text">Total backlogs : <span
                                                            id="totalBacklogItems">0</span></p>
                                                    <p class="card-text">Total epics: <span id="totalEpicItems">0</span>
                                                    </p>
                                                    <p class="card-text">Total user stories : <span
                                                            id="totalUserStoriesItems">0</span></p>
                                                    <p class="card-text">Total tasks: <span id="totalItems">0</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if (!$data['edit']): ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Form Field: Add Members -->
            <div class="card mb-4 main-div" id="addMembers">
                <div class="card-header"  onclick="toggleCollapse('exampleCollapse2')" data-bs-toggle="collapse" data-bs-target="#AddMembers" aria-expanded="false"
                    aria-controls="AddMembers">
                    <h5 class="mb-0 headerName">
                        Scrum members
                        <!-- <i class="fas fa-chevron-down float-end collapsible-icon"></i> -->
                    </h5>
                </div>
                <div class="collapse" id="exampleCollapse2">
                    <div class="card-body">
                        <div class="row">
                            <h6 class="mb-3 addmem cls-searchMembers">Search members</h6>
                            <div class="input-group mb-3 addmem" style="width: 25%;">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="memberSearch"
                                    placeholder="Search members">
                            </div>
                        </div>
                        <div id="selectedMembersContainer" class="mb-3"></div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr >
                                        <th class="cls-membersth" style="padding: 13px 10px 10px 30px;">
                                            <input type="checkbox" id="selectAllMembers" aria-label="Select all members">
                                        </th>
                                        <th class="cls-membersth">User name</th>
                                        <th class="cls-membersth">Role</th>
                                    </tr>
                                </thead>
                                <tbody id="memberTableBody">
                                    <!-- Member rows will be dynamically added here -->

                                </tbody>

                            </table>

                            <div id="paginationControls" class="d-flex justify-content-end align-items-center"></div>
                        </div>
                    </div>
                </div>
            </div>


            <?php if (!$data['edit']): ?>
                <!-- Form Field: Sprint Planning -->
                <div class="card mb-4 main-div" id="sprintSettingBlock">
                    <div class="card-header" onclick="toggleCollapse('exampleCollapse3')" data-bs-toggle="collapse" data-bs-target="#sprintSettingCollapse"
                        aria-expanded="false" aria-controls="sprintSettingCollapse">
                        <h5 class="mb-0 headerName">
                            Sprint plan
                            <!-- <i class="fas fa-chevron-down float-end collapsible-icon"></i> -->
                        </h5>
                    </div>
                    <div class="collapse" id="exampleCollapse3">
                        <div class="card-body" id="sprintSettingContainer">
                            
                        </div>
                        <div class="card-footer">
                            <button type="button" class="btn btn-secondary" id="addMoreSetting">
                                <i class="fas fa-plus-circle me-2"></i>Add more
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <button type="submit" class="btn primary_button">
                    <i class="fas fa-paper-plane me-2"></i>Submit sprint
                </button>
                <?php if (!$data['edit']): ?>
                </div>
            <?php endif; ?>
        </div>
        </main>
        </div>
        </div>

    </form>

    <!-- Item Template (hidden) -->
    <template id="selectedItemTemplate">
        <li class="list-group-item selected-item fade-in">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 text-primary backlog-name"></h6>
                    <p class="mb-1"><small class="text-muted epic-name"></small></p>
                    <p class="mb-1 user-story-name"></p>
                    <p class="mb-0 task-name"></p>
                </div>
                <button class="btn btn-outline-danger btn-sm remove-selected">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </li>
    </template>



    <div class="modal fade" id="projectModal" data-bs-backdrop="static" tabindex="-1"
        aria-labelledby="projectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="projectModalLabel">Select sprint tasks</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- <div class="filter-bar mb-4"> -->

                            <!-- <div> -->

                        </div>
                    </div>

                    <div class="container-fluid">
                        <div class="row " id="taskColumns"
                            style=" display: flex; justify-content: center;align-items: center;">
                            <div class="col-11 ">
                                <div class="card mb-4" id="backlogColumn">
                                    <div class="card-header" data-bs-toggle="collapse" data-bs-target="#AddMembers"
                                        aria-expanded="false" aria-controls="AddMembers">
                                        <h5 class="mb-0 headerName">
                                            <span class="badge custom-badge rounded-pill me-2">1</span> Backlog
                                            <!-- <i class="fas fa-chevron-down float-end collapsible-icon"></i> -->
                                        </h5>
                                    </div>
                                    <div class="collapse" id="AddMembers">
                                        <div class="card-body">
                                            <div class="column-content">
                                                <div class="input-group mb-2">
                                                    <input type="text" class="form-control" id="backlogSearch"
                                                        placeholder="Search backlog items">
                                                </div>
                                                <div class="scrollable-list">
                                                    <ul class="list-group" id="backlogList"></ul>
                                                </div>
                                                <nav aria-label="Backlog pagination">
                                                    <ul class="pagination" id="backlogPagination"></ul>
                                                </nav>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- </div> -->
                            <!-- <div class="row"> -->
                            <div class="col-11 ">
                                <div class="card mb-4" id="epicColumn">
                                    <div class="card-header" data-bs-toggle="collapse" data-bs-target="#AddMembers"
                                        aria-expanded="false" aria-controls="AddMembers">
                                        <h5 class="mb-0 headerName">
                                            <span class="badge custom-badge  rounded-pill me-2">2</span> Epic
                                            <!-- <i class="fas fa-chevron-down float-end collapsible-icon"></i> -->
                                        </h5>
                                    </div>
                                    <div class="collapse" id="AddMembers">
                                        <div class="card-body">
                                            <div class="column-content">
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control" id="epicSearch"
                                                        placeholder="Search epics">
                                                </div>
                                                <div class="scrollable-list">
                                                    <ul class="list-group" id="epicList"></ul>
                                                </div>
                                                <nav aria-label="Epic pagination">
                                                    <ul class="pagination" id="epicPagination"></ul>
                                                </nav>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- </div> -->
                            <!-- <div class="row"> -->
                            <div class="col-11 ">
                                <div class="card mb-4" id="epicColumn">
                                    <div class="card-header" data-bs-toggle="collapse" data-bs-target="#AddMembers"
                                        aria-expanded="false" aria-controls="AddMembers">
                                        <h5 class="mb-0 headerName">
                                            <span class="badge custom-badge  rounded-pill me-2">3</span> User stories
                                            <!-- <i class="fas fa-chevron-down float-end collapsible-icon"></i> -->
                                        </h5>
                                    </div>
                                    <div class="collapse" id="AddMembers">
                                        <div class="card-body">
                                            <div class="column-content">
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control" id="userStorySearch"
                                                        placeholder="Search user stories">
                                                </div>
                                                <div class="scrollable-list">
                                                    <ul class="list-group" id="userStoryList"></ul>
                                                </div>

                                                <nav aria-label="User Story pagination">
                                                    <ul class="pagination" id="userStoryPagination"></ul>
                                                </nav>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- </div> -->

                            <!-- <div class="row"> -->
                            <div class="col-11 ">
                                <div class="card mb-4" id="epicColumn">
                                    <div class="card-header" data-bs-toggle="collapse" data-bs-target="#AddMembers"
                                        aria-expanded="false" aria-controls="AddMembers">
                                        <h5 class="mb-0 headerName">
                                            <span class="badge custom-badge  rounded-pill me-2">4</span>Tasks
                                            <!-- <i class="fas fa-chevron-down float-end collapsible-icon"></i> -->
                                        </h5>
                                    </div>
                                    <div class="collapse" id="AddMembers">
                                        <div class="card-body">
                                            <div class="column-content">
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control" id="taskSearch"
                                                        placeholder="Search tasks">
                                                </div>
                                                <div class="scrollable-list">
                                                    <ul class="list-group" id="taskList"></ul>
                                                </div>
                                                <nav aria-label="Task pagination">
                                                    <ul class="pagination" id="taskPagination"></ul>
                                                </nav>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="offset-10 col-md-1">
                            <button class="btn btn-primary" id="viewSelectedButton" data-bs-dismiss="modal"
                                aria-label="Close">
                                Save
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
    </div>

    <!-- Selected Items Modal -->
    <div class="modal fade" id="selectedItemsModall" tabindex="-1" aria-labelledby="selectedItemsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="selectedItemsModalLabel"><i class="fas fa-clipboard-list"></i> Selected
                        items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- Modal Body -->
                <div class="modal-body">
                    <ul class="list-group" id="selectedItemsList" name="selectedItemsList"></ul>
                </div>
            </div>
        </div>
    </div>
    </div>