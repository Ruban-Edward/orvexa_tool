<!-- Table to show the list of the available sprints and the page where the new sprint created is added -->
<!-- <div class="row">
    <div class="col-sm-4">
        <p><span class="h5">TOTAL NUMBER OF SPRINTS : </span></p>
    </div>
    <div class="col-sm-4">
        <p><span class="h5">RUNNING SPRINTS : </span> </p>
    </div>
    <div class="col-sm-4">
        <p><span class="h5">COMPLETED SPRINTS : </span> </p>
    </div>
</div> -->
<script>
    const BASE_URL = "<?= ASSERT_PATH ?>";
    var permit = <?= has_permission('sprint/edit') ? 1 : 0 ?>;

    //module name
    const m_sprintList = [];
</script>
<div class="container">
    <div class="page">
        <div class="row d-flex justify-content-end">
            <div class="col-sm-12 basicBtnComponents">
                <ul class="header_button  d-flex align-items-center">
                    <?php if (has_permission('sprint/navcreatesprint')) : ?>
                        <li><a href='<?= ASSERT_PATH ?>sprint/navcreatesprint'><button id="sprint" class="btn primary_button"><i class="fas fa-plus-circle"></i> Create Sprint</button></a></li>
                    <?php endif; ?>
                </ul>
                <ul class="option-button d-flex">
                    <li class="me-2">
                        <div class="search-container" style="display: none">
                            <div class="search-box">
                                <input type="text" id="backlogSearchInput" class="search-input" placeholder="Search Product">
                                <button id="search_btn" class="search-button style-button">
                                    <i class="bi bi-search"></i>
                                    <span>Search</span>
                                </button>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="dropdown">
                            <button id="column_btn" class=" button-secondary style-button column-btn" type="button" aria-expanded="false">
                                <i class="far fa-check-square"></i><span>Columns</span>
                            </button>
                            <div id="columnDropdown" class="dropdown-menu p-3">
                                <!-- Checkboxes will be appended here dynamically -->
                            </div>
                        </div>
                    </li>
                    <li>

                        <button id="filter_btn" class=" button-secondary style-button" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="icon-filter mt-1 me-1"></i><span>Filter</span>
                            <span id="noti" class="badge badge-pill badge-danger"></span>

                        </button>
                    </li>


                </ul>
            </div>
        </div>
        <div class="row">
        <div class="cls-tableview">
            <table id="priority-table" class="table table-borderless custom-table">
                <div class="headerStyle">
                    <thead id="tableHeader">
                        <!-- Table headers will be inserted here -->
                    </thead>
                    <tbody id="tableBody">
                        <!-- Table rows will be inserted here -->

                    </tbody>
                </div>
            </table>
            <div id="paginationControls" class="d-flex justify-content-between align-items-center mt-3">
                <button id="prevPage" class="btn button-secondary">Prev</button>
                <span id="pageInfo" class="mx-3"></span>
                <button id="nextPage" class="btn button-secondary">Next</button>
            </div>
            <div id="noData">
                <div class="empty-content" id="empty" style="display: block;">
                    <div class="text-center cls-noProduct">
                        <span class="bi bi-emoji-frown cls-noProductIcon"></span>
                        <h4> No Such Sprints Found</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
<div id="filterSidebar" class="sidebar-filter">
    <form method="post" id="filterForm">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="filter-heading">Filter</h3>
            <button type="button" class="btn-close" id="closeBtn" aria-label="Close filters"></button>
        </div>

        <!-- Date Filters -->
        <div class="mb-3">
            <label for="fromDate" class="form-label input-style filter-text">From Date:</label>
            <input type="date" name="start_date" class="dates form-control mb-3 flatpickr-no-config" id="fromDate" placeholder="Select date">
            <!-- <input type="date" name="start_date" class="form-control" id="fromDate"> -->
        </div>
        <div class="mb-3">
            <label for="toDate" class="form-label input-style filter-text">To Date:</label>
            <input type="date" name="end_date" class="dates form-control mb-3 flatpickr-no-config" id="toDate" placeholder="Select date">
            <!-- <input type="date" name="end_date" class="form-control" id="toDate"> -->
        </div>

        <div class="mb-3">
            <label for="product" class="form-label filter-text">Product:</label>
            <select name="product_name[]" class="form-select select" multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="1" placeholder="Select Product">

                <?php
                foreach ($data['filter_values'][1] as $key => $value) {
                    echo "<option>" . $value . "</option>";
                }

                ?>
            </select>
        </div>

        <!-- Sprint and Backlog Common Filters -->
        <div id="sprintbacklogdrops" class="mb-3">
            <label for="customer" class="form-label filter-text ">Customer:</label>
            <select name="customer[]" id="customer" class="form-select select" multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="1" placeholder="Select Customer">
                <?php
                foreach ($data['filter_values'][0] as $key => $value) {
                    echo "<option>" . $value . "</option>";
                }

                ?>
            </select>

            <label for="status" class="form-label filter-text mt-1">Status:</label>
            <select name="status_name[]" id="sprintstatus" class="form-select select" multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="1" placeholder="Select Status">
                <?php
                foreach ($data['statusList'] as $key => $value) {
                    echo "<option>" . $value['status_name'] . "</option>";
                }
                ?>
            </select>
        </div>


        <!-- Sprint Filters -->
        <div style="display: none;">
            <div id="sprint" class="mb-3">
                <label for="sprintname" class="form-label filter-text">Sprint Name:</label>
                <select name="sprint_name[]" id="sprintname" class="form-select select" multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="1" plac>
                    <option value="Select Product" disabled selected>Select Product</option>
                    <?php
                    foreach ($data['filter_values'][4] as $key => $value) {
                        echo "<option>" . $value . "</option>";
                    }
                    ?>
                </select>
            </div>
            <label for="duration" class="form-label filter-text">Duration:</label>
            <select name="sprint_duration_value[]" id="duration" class="form-select select " multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="1">
                <?php
                foreach ($data['filter_values'][2] as $key => $value) {
                    echo "<option>" . $value . "</option>";
                }
                ?>
            </select>
        </div>

        <!-- Filters -->
        <div class="d-flex justify-content-between filter-btn">
            <button type="submit" id="applyfilter" class="btn primary_button">Apply</button>
            <button type="reset" class="button-secondary" id="resetbtn">Reset</button>
        </div>

    </form>
</div>
<div class="modal fade text-left" id="meetingModal" tabindex="-1" role="dialog" aria-labelledby="myMeetingModal" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document" id="modal-size">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Schedule Meeting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="color : red;"></button>
            </div>

            <div class="modal-body">
                <!-- Modal content goes here -->
                <form class="row g-3 needs-validation" id="meetingForm" method="POST" novalidate>
                    <input type="hidden" name="r_product_id" id="product">
                    <input type="hidden" name="r_sprint_id" id="sprintID">
                    <!-- Form Field: Meeting Title -->
                    <div class="col-md-6 d-flex align-items-center">
                        <label for="sprintName" class="form-label me-2">Sprint:</label>
                        <input type="text" class="form-control no-border" id="sprintName" name="sprint_name" readonly>
                    </div>
                    <!-- Form Field: sprint Type -->
                    <div class="col-md-6 d-flex align-items-center">
                        <label for="productName" class="form-label me-2"> Product:</label>
                        <input type="text" class="form-control no-border" id="productName" readonly>
                    </div>

                    <!-- Form Field: Meeting Title -->
                    <div class="col-md-6">
                        <label for="meeting_title" class="form-label form-label1">Meeting Title</label>
                        <input type="text" class="form-control" id="meeting_title" name="meeting_title" placeholder="Enter the Meeting Title" required>
                        <div class="invalid-feedback">Please provide a meeting title.</div>
                    </div>

                    <!-- Form Field: Meeting Type -->
                    <div class="col-md-6">
                        <label for="meeting_type" class="form-label form-label1">Sprint Meeting Type</label>
                        <select class="form-select" id="meeting_type" name="r_meeting_type_id" required>
                            <option value="" disabled selected>Select the Meeting type</option>
                        </select>
                        <div class="valid-feedback">Looks good!</div>
                        <div class="invalid-feedback">Please choose a meeting type.</div>
                    </div>
                    <!-- Form Field: Meeting Date -->
                    <div class="col-md-6" id="recurrence_meeting_container" style="display: none;">
                        <label for="recurrence_meeting" id="labelok" class="form-label form-label1">Recurrence Meeting</label>
                        <select class="form-select" id="recurrence_meeting" name="recurrance_meeting_id" required>
                            <option value="" disabled selected>Select the Recurrence</option>
                            <option value="0">None</option>
                            <option value="1">Daily</option>
                            <option value="2">Every 2 Days</option>
                            <option value="3">Every 3 Days</option>
                            <option value="4">Every 4 Days</option>
                            <option value="5">Every 5 Days</option>
                            <option value="7">Every Week</option>
                        </select>
                        <div class="valid-feedback">Looks good!</div>
                        <div class="invalid-feedback">Please choose a recurrence.</div>
                    </div>

                    <!-- Form Field: Meeting Duration -->
                    <div class="col-md-6">
                        <label for="meeting_duration" class="form-label form-label1" style="margin-right: 130px;">Hours</label>
                        <label for="meeting_duration" class="form-label form-label1">Minutes</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="meeting_duration_hours" name="meeting_duration_hours" min="0" max="24" placeholder="0" >
                            <input type="number" class="form-control" id="meeting_duration_minutes" name="meeting_duration_minutes" min="0" max="59" placeholder="00" required>
                        </div>
                    </div> 

                    <!-- Form Field: Meeting Start Date -->
                    <div class="col-md-3" id="meeting_start_date_container">
                        <label for="meeting_start__date" class="form-label form-label1">Meeting start date</label>
                        <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input" id="meeting_start_date" name="meeting_start_date" value="<?= date('Y-m-d'); ?>" accept="" placeholder="Select date.." required>
                        <div class="invalid-feedback">Please choose a meeting date.</div>
                    </div>

                    <!-- Form Field: Sprint Start Date -->
                    <div class="col-md-3" id="sprint_start_date_container" style="display: none;">
                        <label for="sprint_start_date" class="form-label form-label1">Recurrence Start Date</label>
                        <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input" id="sprint_start_date" name="sprint_start_date" value="<?= date('Y-m-d'); ?>" accept="" placeholder="Select date.." required>
                        <div class="invalid-feedback">Please choose a sprint start date.</div>
                    </div>

                    <!-- Form Field: Meeting Start Time -->
                    <div class="col-md-3">
                        <label for="meeting_start_time" class="form-label form-label1">Meeting Start Time</label>
                        <select class="form-select" id="startTimeSelect" name="meeting_start_time" required></select>
                        <div class="invalid-feedback">Please choose a meeting start time.</div>
                    </div>

                    <!-- Form Field: Meeting End Date -->
                    <div class="col-md-3" id="meeting_end_date_container">
                        <label for="meeting_end_date" class="form-label form-label1">Meeting end date</label>
                        <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input" id="meeting_end_date" name="meeting_end_date" value="<?= date('Y-m-d'); ?>" accept="" placeholder="Select date.." required>
                        <div class="invalid-feedback">Please choose a meeting date.</div>
                    </div>

                    <!-- Form Field: Sprint End Date -->
                    <div class="col-md-3" id="sprint_end_date_container" style="display: none;">
                        <label for="sprint_end_date" class="form-label form-label1">Recurrence End Date</label>
                        <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input" id="sprint_end_date" name="sprint_end_date" accept="" value="<?= date('Y-m-d'); ?>" placeholder="Select date.." required>
                        <div class="invalid-feedback">Please choose a sprint end date.</div>
                    </div>

                    <!-- Form Field: Meeting End Time -->
                    <div class="col-md-3">
                        <label for="meeting_end_time" class="form-label form-label1" >Meeting End Time</label>
                        <select class="form-select" id="endTimeSelect" name="meeting_end_time" required></select>
                        <div class="invalid-feedback">Please choose a meeting end time.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="meeting_location" class="form-label form-label1">Meeting Location</label>
                        <select class="form-select" id="meeting_location" name="r_meeting_location_id" required>
                            <option value="" disabled selected>Select Meeting Location</option>
                            <option value="1">Online</option>
                            <option value="2">Offline</option>
                        </select>
                        <div class="invalid-feedback">Please choose a meeting location.</div>
                    </div>


                    <!-- Form Field: Meeting Link -->
                    <div class="col-md-6">
                        <label for="meeting_link" class="form-label form-label1">Meeting Link / Place</label>
                        <input type="text" class="form-control" id="meeting_link" name="meeting_link" placeholder="Enter the Meeting link" required>
                        <div class="valid-feedback">Looks good!</div>
                        <div class="invalid-feedback">Please provide a meeting link / Place</div>
                    </div>

                    <!-- Form Field: Meeting Description -->
                    <div class="col-md-6">
                        <label for="meeting_description" class="form-label">Meeting Description</label>
                        <textarea type="text" class="form-control" id="meeting_description" name="meeting_description" placeholder="Enter the Description"></textarea>
                        <div class="valid-feedback">Looks good!</div>
                        <div class="invalid-feedback">Please provide a meeting description</div>
                    </div>

                    <div class="col-md-6">
                        <label for="meeting_team" class="form-label">Sprint Team Members for meeting</label>
                        <div class="dropdown">
                            <button class="form-select howa" type="button" id="teamMembersDropdownButton" data-bs-toggle="dropdown" aria-expanded="false">
                                Select Team Members
                            </button>
                            <div id="teamMembersDropdown" class="dropdown-menu" aria-labelledby="teamMembersDropdownButton" style="max-height: 200px; overflow-y: auto;">
                                <!-- Team members will be populated here -->
                            </div>
                        </div>
                        <!-- <div id="selectedTeamMembers" class="selected-team-members mt-2"></div> -->
                        <input type="hidden" id="selectedTeamMembersInput" name="selectedEmails">
                    </div>
            </div>
            <!-- Form Submit Button -->
            <div class="modal-footer d-flex justify-content-center">
                <button class="button submit" type="submit" name="Schedulebutton" id="Schedulebutton" value="submit">Schedule</button>
            </div>
            </form>
        </div>
    </div>
</div>

<script>
    const refinement_url = "<?= ASSERT_PATH ?>"
    const user = <?php echo json_encode($data['user_id']); ?>;
    const totalPages_sprint = <?php echo json_encode($data['totalPages']); ?>;
    const statuslist = <?php echo json_encode($data['statusList']); ?>;
    var data = <?php echo json_encode($data['tasks']); ?>;
</script>
