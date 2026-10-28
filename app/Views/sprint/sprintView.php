<?php /** * @author SIVABALAN * * @modified-by VISHVA * @created-date 07-07-2024 * @modified-date 10-07-2024 * */ ?>
<script>
    var sprintId = <?= json_encode($data['id']) ?>;
    var dailyScrum = <?= json_encode($data['dailyScrum']) ?>;
    var ASSERT_PATH = <?= json_encode(ASSERT_PATH) ?>;
    const sprintActivity = <?php $sprintActivity = json_encode($data['sprintActivity']);
    echo $sprintActivity; ?>;
    const sprintPlanningStatus = <?php $sprintPlanningStatus = json_encode($data['sprintPlanningStatus']);
    echo $sprintPlanningStatus; ?>;
    const pdfSprintDetails = <?php $pdfSprintDetails = json_encode($data['sprintDetails'][0]);
    echo $pdfSprintDetails; ?>;
    const sprintStatus = <?php $sprintStatus = json_encode($data['sprintDetails'][0]['sprint_status_name']);
    echo $sprintStatus; ?>;
    const sprintDetailsend_date = <?php $sprintDetailsend_date = json_encode($data['sprintDetails'][0]['end_date']);
    echo $sprintDetailsend_date; ?>;
    const sprintReviewStatus = <?php $sprintReviewStatus = json_encode($data['sprintReviewStatus']);
    echo $sprintReviewStatus; ?>;
    const sprintRetrospectiveStatus = <?php $sprintRetrospectiveStatus = json_encode($data['sprintRetrospectiveStatus']);
    echo $sprintRetrospectiveStatus; ?>;
    var permit = <?= has_permission('sprint/edit') ? 1 : 0 ?>;
    var sprintStartDate = <?php $sprintStartDate = json_encode(date("Y-m-d", strtotime($data['sprintDetails'][0]['start_date'])));
    echo $sprintStartDate; ?>;
    var sprintEndDate = <?php $sprintEndDate = json_encode(date("Y-m-d", strtotime($data['sprintDetails'][0]['end_date'])));
    echo $sprintEndDate; ?>;

    //module name
    const m_sprintView = [];
</script>

<div class="fixed-header">
    <div class="sprintview-header">
        <nav class="navbar1 navbar-expand-lg navbar-dark">
            <div class="header-elementsprintview">
                <a class="navbar-brand" href="#">
                    <h5 class="headerName headerSize"><?= ucfirst($data['sprintDetails'][0]['sprint_name']) ?></h5>
                </a>
                <div class="collapse navbar-collapse show" id="navbarNavDropdown">
                    <ul class="navbar-nav mr-auto">
                        <?php if (has_permission('sprint/edit')): ?>
                            <li class="nav-item">
                                <a>
                                    <form action="edit" method="post">
                                        <input type="hidden" name="sprint_id" value="<?= $data['id'] ?>">
                                        <button type="submit" class="edit"><span
                                                class="icon-edit cls-headerIcon"></span>Edit</button>
                                    </form>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item card-links"><a id="spntHis" data-bs-toggle="modal"
                                data-bs-target="#sprintHistory"><span
                                    class="icon-file-text cls-headerIcon"></span>History</a></li>
                        <li class="nav-item card-links"><a href="<?= site_url('sprint/generate-pdf/' . $data['id']) ?>"
                                target="_blank" id="generatePDFButton"><span
                                    class="icon-download cls-headerIcon"></span>Download</a></li>

                    </ul>
                </div>
            </div>
        </nav>
    </div>
</div>

<!-- Sprint Overview -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0 headerName"><i class="fas fa-info-circle me-2"></i>Sprint overview</h5>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="sprint-info">
                <div class="info-item">
                    <div class="label">Sprint name:</div>
                    <div class="value">
                        <?= isset($data['sprintDetails'][0]['sprint_name']) ? $data['sprintDetails'][0]['sprint_name'] : 'No data found'; ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="label">Sprint version:</div>
                    <div class="value">
                        <?= isset($data['sprintDetails'][0]['sprint_version']) ? $data['sprintDetails'][0]['sprint_version'] : 'No data found'; ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="label">Product:</div>
                    <div class="value">
                        <?= isset($data['sprintDetails'][0]['product_name']) ? $data['sprintDetails'][0]['product_name'] : 'No data found'; ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="label">Customer:</div>
                    <div class="value">
                        <?= isset($data['sprintDetails'][0]['customer_name']) ? $data['sprintDetails'][0]['customer_name'] : 'No data found'; ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="label">Start date:</div>
                    <div class="value">
                        <?= isset($data['sprintDetails'][0]['start_date']) ? $data['sprintDetails'][0]['start_date'] : 'No data found'; ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="label">End date:</div>
                    <div class="value">
                        <?= isset($data['sprintDetails'][0]['end_date']) ? $data['sprintDetails'][0]['end_date'] : 'No data found'; ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="label">Sprint goal:</div>
                    <div class="value">
                        <?= isset($data['sprintDetails'][0]['sprint_goal']) ? $data['sprintDetails'][0]['sprint_goal'] : 'No data found'; ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="label">Duration:</div>
                    <div class="value">
                        <?= isset($data['sprintDetails'][0]['sprint_duration']) ? $data['sprintDetails'][0]['sprint_duration'] : 'No data found'; ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="label">Sprint status:</div>
                    <div class="value">
                        <?php if (has_permission('sprint/updateSprintStatus')): ?>
                            <select class="form-select" name="sprintStatus" id="sprintStatus" aria-label="Sprint status">
                                <?php
                                foreach ($data['sprintStatus'] as $key => $value) {
                                    $check = $value['status_name'] === $data['sprintDetails'][0]['sprint_status_name'] ? " selected" : "";
                                    echo '<option value="' . $value['module_status_id'] . '"' . $check . '>' . $value['status_name'] . '</option>';
                                }
                                ?>
                            </select>
                        <?php else: ?>
                            <?= isset($data['sprintDetails'][0]['sprint_status_name']) ?
                                $data['sprintDetails'][0]['sprint_status_name'] : 'No data found'; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sprint Member Details -->
<div class="card mb-4">
    <button class="card-header" onclick="toggleCollapse('exampleCollapse')" type="button" data-bs-toggle="collapse"
        data-bs-target="#sprintmembers" aria-expanded="false" aria-controls="sprintRetrospective"
        id="sprintMembersList">
        <h5 class="mb-0 d-flex justify-content-between align-items-center headerName">
            <span><i class="fas fa-chart-line me-2"></i>Sprint members</span>
        </h5>
        <i class="fas fa-chevron-down collapsible-icon"></i>
    </button>
    <div class="collapse" id="exampleCollapse">
        <div class="card-body">
            <div class="content" id="content">
                <table class="sprint-members-details">
                    <thead>
                        <tr>
                            <th>Employee id</th>
                            <th>Employee name</th>
                            <th>Email id</th>
                            <th>Employee role</th>
                        </tr>
                    </thead>
                    <tbody id="Members-body">
                        <!-- Sprint details will be added dynamically here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Sprint Planning Details -->
<div class="card mb-4">
    <div class="card-header" onclick="toggleCollapse('exampleCollapse1')" data-bs-toggle="collapse"
        data-bs-target="#sprintPlanning" aria-expanded="false" aria-controls="sprintRetrospective"
        id='sprint-planning-card'>
        <h5 class="mb-0 d-flex justify-content-between align-items-center headerName">
            <span><i class="fas fa-chart-line me-2"></i>Sprint planning details</span>
        </h5>
        <div class="card-links">
            <?php if (has_permission('sprint/ReviewSprintPlanDetails')): ?>
                <a data-bs-toggle="modal" data-bs-target="#sprintSetting" style="margin-right: 30px;">
                    Sprint plan
                </a>
            <?php endif; ?>
            <i class="fas fa-chevron-down collapsible-icon"></i>
        </div>
    </div>
    <div class="collapse" id="exampleCollapse1">
        <div class="card-body">
            <div class="content" id="content">
                <table class="sprint-details">
                    <thead>
                        <tr>
                            <th>Component name</th>
                            <th>Start date</th>
                            <th>End date</th>
                            <th>Status</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody id="sprintList">
                        <!-- Sprint details will be added dynamically here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Task Progress -->
<div class="card mb-4">
    <div class="card-header" onclick="toggleCollapse('exampleCollapse2')" data-bs-toggle="collapse"
        data-bs-target="#sprintTasks" aria-expanded="false" aria-controls="sprintTasks">
        <h5 class="mb-0 d-flex justify-content-between align-items-center headerName">
            <span><i class="fas fa-tasks me-2"></i>Sprint board</span>
        </h5>
        <div class="total-backlogs"><label for="">Total backlogs</label>
            <p id="total-backlogs"></p>
        </div>
        <div class="total-tasks"><label for="">Total tasks</label>
            <p id="total-tasks"></p>
        </div>
        <div class="tasks-completed"><label for="">Tasks completed</label>
            <p id="task-no"></p>
        </div>
        <div>
            <i class="fas fa-chevron-down collapsible-icon"></i>
        </div>
    </div>
    <div class="collapse" id="exampleCollapse2">
        <div class="card-body">
            <div class="ox-board-bar">
                <div class="ox-segmented" role="radiogroup" aria-label="Sprint board layout">
                    <input type="radio" name="sprintLayout" id="sprintLayoutBoard" value="board" checked>
                    <label for="sprintLayoutBoard"><i class="icon-columns" aria-hidden="true"></i> Board</label>
                    <input type="radio" name="sprintLayout" id="sprintLayoutTable" value="table">
                    <label for="sprintLayoutTable"><i class="icon-list" aria-hidden="true"></i> Table</label>
                </div>
                <label class="ox-board-mine"><input type="checkbox" class="form-check-input" id="boardOnlyMine"> Only my tasks</label>
                <span class="ox-board-hint" id="boardHint">Drag cards between columns, or focus a card and use ← →</span>
            </div>
            <div id="sprintBoard" class="ox-board" aria-describedby="boardHint"></div>
            <div class="visually-hidden" aria-live="polite" id="boardStatus"></div>
            <div id="sprintTable" hidden>
            <div class="filter-container" id='filter-element'>
                <div class="backlogCotainer">
                    <div id='filterBlock'>
                        <label for="statusFilter">Filter by status:</label>
                        <select id="statusFilter" onchange="applyStatusFilter()">
                            <option value="all">All</option>
                            <option value="In Progress">In progress</option>
                            <option value="Completed">Completed</option>
                            <option value="Move to Prelive">Move to prelive</option>
                            <option value="Assign for UAT">Assign for UAT</option>
                            <option value="Assign for Testing">Assign for testing</option>
                            <option value="Move to Live">Move to live</option>
                        </select>
                    </div>
                    <div id="backlogName">

                    </div>
                </div>
                <div id="progress-bar">
                </div>

            </div>
            <div class="table-container" id="table-containers">
                <table class="table table-striped" id="table-style">
                    <thead class="backlog-thead">
                        <tr class="backlog-table-heading">
                            <!-- <th>Backlog</th> -->
                            <th>Epic</th>
                            <th>Us Id</th>
                            <th>User story</th>
                            <th>Task</th>
                            <th>Task status</th>
                        </tr>
                    </thead>
                    <tbody id="table-body" class="formElements">
                        <!-- Table content will be generated dynamically -->
                    </tbody>
                </table>


            </div>
            </div>
        </div>
        <nav aria-label="Page navigation" id="myNav" class="pagenav">
            <ul>
                <button id="prevbutton" class="pagebutton"><i class='fas fa-angle-left'
                        style='font-size:36px'></i></button>
                <button id="nextbutton" class="pagebutton"><i class='fas fa-angle-right'
                        style='font-size:36px'></i></button>
            </ul>
        </nav>
    </div>
    <div class="text-center cls-noProduct" id='errormsg'>
        <span class="bi bi-emoji-frown cls-noProductIcon"></span>
        <h4> No Such Tasks Available</h4>
    </div>
</div>

<!-- Daily Scrum -->
<div class="card mb-4" id="navscrumdiary">
    <div class="card-header" onclick="toggleCollapse('exampleCollapse3')" data-bs-toggle="collapse"
        data-bs-target="#dailyScrum" aria-expanded="false" aria-controls="dailyScrum">
        <h5 class="mb-0 d-flex justify-content-between align-items-center headerName">
            <span><i class="fas fa-calendar-check me-2"></i>Daily scrum</span>
        </h5>
        <div class="card-links" id='scumDairyBtn'>
            <?php if (has_permission('sprint/scrumdiary')): ?>
                <a data-bs-toggle="modal" data-bs-target="#dailyScrumModel" style="margin-right: 30px;"
                    id="scrumDiaryButton">
                    Daily scrum
                </a>
            <?php endif; ?>
            <i class="fas fa-chevron-down collapsible-icon"></i>
        </div>
    </div>
    <div class="collapse" id="exampleCollapse3">
        <div class="card-body" id="dailyScrumComponents" style="display: none"></div>
        <div id="errorsdmsg" style="text-align: center; margin-top:1rem">
            <p>Data not found</p>
        </div>
    </div>
</div>

<!-- Sprint Review -->
<div class="card mb-4" id="navreview">
    <div class="card-header" onclick="toggleCollapse('exampleCollapse4')" data-bs-toggle="collapse"
        data-bs-target="#sprintReview" aria-expanded="false" aria-controls="sprintReview" id='sprint-review-card'>
        <h5 class="mb-0 d-flex justify-content-between align-items-center headerName">
            <span><i class="fas fa-clipboard-check me-2"></i>Sprint review</span>
        </h5>
        <div class="card-links" <?php if (has_permission('sprint/submitSprintReview')) {
            echo 'id="review-align"';
        } ?>>
            <?php if (has_permission('sprint/submitSprintReview')): ?>
                <form id="sprintReviewForm" action="navsprintreview" method="post">
                    <input type="hidden" name="sprint_id" value="<?= $data['id'] ?>">
                    <button class='removeBtnstyle' type="submit" data-bs-toggle="modal"
                        id="scrumReviewButton">Review</button>
                </form>
            <?php endif; ?>
            <i class="fas fa-chevron-down collapsible-icon"></i>
        </div>
    </div>
    <div class="collapse" id="exampleCollapse4">
        <div class="card-body" id='sprintReviewDisplays'>
            <div class="details">
                <div><strong>Review date:</strong> <span id="reviewDate"></span></div>
            </div>
            <div id="reviewDetails">

            </div>
        </div>
    </div>
</div>

<!-- Sprint Retrospective -->
<div class="card mb-4" id="navretrospective">
    <div class="card-header" onclick="toggleCollapse('exampleCollapse5')" data-bs-toggle="collapse"
        data-bs-target="#sprintRetrospective" aria-expanded="false" aria-controls="sprintRetrospective"
        id='sprint-retrospective-card'>
        <h5 class="mb-0 d-flex justify-content-between align-items-center headerName">
            <span><i class="fas fa-chart-line me-2"></i>Sprint retrospective</span>
        </h5>
        <div class="card-links">
            <?php if (has_permission('sprint/sprintretrospective')): ?>
                <a data-bs-toggle="modal" data-bs-target="#sprintRetrospectiveModal"
                    id="scrumRetrospectiveButton">Retrospective</a>
            <?php endif; ?>
            <i class="fas fa-chevron-down collapsible-icon"></i>
        </div>
    </div>
    <div class="collapse" id="exampleCollapse5">
        <div class="card-body">
            <div class="details">
                <div><strong>Retrospective date:</strong> <span id="retrospectiveDate"></span></div>
            </div>

            <div class="pros-cons" id='sprint-retrpctve'>

            </div>

        </div>
    </div>
</div>
<div id="scrollTopButton" class="scroll-top-button">
    ↑
</div>


<!-- Sprint planning model -->
<div class="modal fade" id="sprintSetting" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
    data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="mb-0 headerName modal-title fs-5" id="exampleModalLabel">Sprint Plan</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post">
                    <div class="card-body" id="sprintSettingContainer">
                    </div>
                    <div class="card-footer">
                        <button type="button" class="button-secondary" id="addMoreSetting">
                            <i class="fas fa-plus-circle me-2"></i>Add more
                        </button>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="button-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveChanges">Save changes</button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- Sprint History Modal -->
<div class="modal fade" id="sprintHistory" tabindex="-1" aria-labelledby="sprinthistory" aria-hidden="true"
    data-bs-backdrop="static">
    <div class="modal-dialog custom-modal-width">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sprint history:
                    <?= $data['sprintDetails'][0]['sprint_name'] . " - " . $data['sprintDetails'][0]['sprint_version'] ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                    id="clearHistory"></button>
            </div>
            <div class="modal-body">
                <div class="cls-filterHeaderContainer">
                    <div class="date-filter" style="display:flex;">
                        <label for="" style="margin-top: 7px;">Start Date</label>
                        <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input" id="startDate"
                            name="startDate" accept="" placeholder="Select date" readonly="readonly" required
                            style="margin: 0px 10px;width: auto;">
                        <label for="" style="margin-top: 7px;">End Date</label>
                        <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input" id="endDate"
                            name="startDate" accept="" placeholder="Select date" readonly="readonly" required
                            style="margin: 0px 10px;width: auto;">
                        <button id="filterButton" class="btn primary_button"
                            style="height: 37px;width: 75px;">Filter</button>
                        <button id="resetButton" class="btn primary_button"
                            style="height: 37px;width: 75px;margin-left: 5px;">Reset</button>
                    </div>
                </div>
                <ul class="data-list" id="dataList"></ul>
                <div class="pagination">
                    <button id="previous" class="btn primary-button">Prev</button>
                    <button id="next" class="btn primary-button">Next</button>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Sprint Retrospective Modal -->
<div class="modal fade" id="sprintRetrospectiveModal" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="mb-0 headerName modal-title fs-5">Sprint retrospective</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="scrumRetrospectiveForm" action="" method="post">
                    <div class="date-picker">
                        <input type="date" id="date-picker" name="date" aria-label="Retrospective date">
                    </div>
                    <div class="feedback-section">
                        <div class="feedback-type">
                            <span>Choose type:</span>
                            <label>
                                <input type="radio" name="feedback-type" id="pros-input" value="pros"> Pros
                            </label>
                            <label>
                                <input type="radio" name="feedback-type" id="cons-input" value="cons"> Cons
                            </label>
                            <label>
                                <input type="radio" name="feedback-type" id="suggestions-input" value="lns">
                                Suggestions
                            </label>
                        </div>
                        <div class="error-container">
                            <span class="error-message" id="typeError">Please select any of the above options</span>
                        </div>
                        <div class="form-group">
                            <div class="form-group-header" id="feedback-label">
                                <h5 class="headerName"><i class="fas fa-clipboard"></i> General</h5>
                            </div>
                            <div class="form-group-content">
                                <textarea id="general" name="general" placeholder="General comments"></textarea>
                                <button type="button" id="mic" class="mic-button" aria-label="Dictate with the microphone"
                                    onclick="voiceRecognition('mic','general')">
                                    <i class="bi bi-mic"></i>
                                </button>
                            </div>
                            <div class="error-container">
                                <span class="error-message" id="generalError">This is a required field</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveRetrospective">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Scrum diary modal -->
<div class="modal fade" id="dailyScrumModel" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
    data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="mb-0 headerName modal-title fs-5" id="exampleModalLabel">Daily scrum</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="scrumDiaryForm" action="" method="post">
                    <div class="datepicker">
                        <input type="date" id="datepicker" name="date" aria-label="Date">
                    </div>
                    <div class="input-area">
                        <div class="form-group">
                            <label for="task">Select task:</label>
                            <div class="custom-select" id="taskSelect">
                                <div class="select-selected">Choose a task</div>
                                <div class="select-items" id="taskCheckboxes">
                                    <label>
                                        <input type="checkbox" id="selectAll"> Select all
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div id="pagination">
                            <button type="button" id="prevPage" class="btn primary-button">Previous</button>
                            <span id="pageInfo"></span>
                            <button type="button" class="btn primary-button" id="nextPage">Next</button>
                        </div>
                        <div class="radio-group">
                            <span>Challenges:</span>
                            <label>
                                <input type="radio" name="challenges" value="N"> No
                            </label>
                            <label>
                                <input type="radio" name="challenges" value="Y"> Yes
                            </label>
                        </div>
                        <div class="error-container">
                            <span class="error-message" id="radioError">Please select any one of the above
                                options</span>
                        </div>
                        <div class="form-group">
                            <div class="form-group-header" id="formGroupHeader">
                                <h5 class="headerName"><i class="fas fa-clipboard"></i> General</h5>
                            </div>
                            <div class="form-group-content">
                                <textarea id="generalDailyScrum" name="general"
                                    placeholder="General comments"></textarea>
                                <button type="button" id="micButton" class="mic-button" aria-label="Dictate with the microphone"
                                    onclick="voiceRecognition('micButton','generalDailyScrum')">
                                    <i class="bi bi-mic"></i>
                                </button>
                            </div>
                            <div class="error-container">
                                <span class="error-message" id="generalError">This is a required field</span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="button-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn primary_button" id="saveRetrospective">Save
                                changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="<?= ASSERT_PATH ?>assets/js/sprint/sprintBoard.js?v=<?= filemtime(FCPATH . 'assets/js/sprint/sprintBoard.js') ?>"></script>
