<?php
// Sprint ids (kept for the included modals) – previously overwritten on every iteration
$sprintId = $data['sprintDetails'];
$id = ['sprint_id' => []];
if (!empty($sprintId)) {
    foreach ($sprintId as $value) {
        $id['sprint_id'][] = $value['sprint_id'];
    }
}
?>

<!-- ===================== Calendar workspace ===================== -->
<div class="ox-cal" id="oxCal">

    <!-- Left rail: quick create, mini calendar, filters -->
    <aside class="ox-cal-side" aria-label="Calendar filters">
        <?php if (has_permission('meeting/scheduleMeeting')): ?>
            <button type="button" class="btn btn-primary ox-new-btn" id="oxNewMeeting">
                <i class="fas fa-plus"></i> New meeting
            </button>
        <?php endif; ?>

        <div class="ox-side-card ox-mini-wrap">
            <div id="oxMini" aria-label="Jump to date"></div>
        </div>

        <div class="ox-side-card">
            <div class="ox-side-title">Show</div>
            <div class="ox-seg ox-seg-full" role="tablist" aria-label="Event kind">
                <button type="button" class="ox-seg-btn active" data-kind="Meeting" role="tab" aria-selected="true"><i class="fas fa-users"></i> Meetings</button>
                <button type="button" class="ox-seg-btn" data-kind="Sprint" role="tab" aria-selected="false"><i class="fas fa-tachometer-alt"></i> Sprints</button>
            </div>
        </div>

        <div class="ox-side-card" id="oxTypeFilter">
            <div class="ox-side-title">Meeting type</div>
            <div class="ox-chips" id="oxTypeChips"></div>
        </div>

        <div class="ox-side-card" id="oxProductFilter" hidden>
            <div class="ox-side-title">Product</div>
            <select class="form-select form-select-sm" id="oxProductSelect" aria-label="Filter sprints by product"></select>
        </div>

        <div class="ox-side-card">
            <div class="ox-side-title">Status</div>
            <div class="ox-chips ox-chips-col" id="oxStatusChips"></div>
        </div>
    </aside>

    <!-- Main: toolbar + calendar -->
    <section class="ox-cal-main" aria-label="Calendar">
        <div class="ox-toolbar">
            <div class="ox-toolbar-left">
                <button type="button" class="btn ox-btn-today" id="oxToday">Today</button>
                <div class="ox-nav">
                    <button type="button" class="ox-icon-btn" id="oxPrev" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
                    <button type="button" class="ox-icon-btn" id="oxNext" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
                </div>
                <h2 class="ox-title" id="oxTitle" aria-live="polite"></h2>
            </div>
            <div class="ox-toolbar-right">
                <div class="ox-seg" role="tablist" aria-label="Calendar view">
                    <button type="button" class="ox-seg-btn" data-view="dayGridMonth" role="tab" title="Month (M)">Month</button>
                    <button type="button" class="ox-seg-btn" data-view="timeGridWeek" role="tab" title="Week (W)">Week</button>
                    <button type="button" class="ox-seg-btn" data-view="timeGridDay" role="tab" title="Day (D)">Day</button>
                    <button type="button" class="ox-seg-btn" data-view="listMonth" role="tab" title="Agenda (A)">Agenda</button>
                </div>
                <?php if (has_permission('meeting/createGroupDetails')): ?>
                    <div class="dropdown">
                        <button class="ox-icon-btn ox-icon-btn-lg" type="button" id="oxGroupMenu" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Meeting groups" title="Meeting groups">
                            <i class="fas fa-user-friends"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="oxGroupMenu">
                            <button class="dropdown-item" type="button" id="createGroupButton" onclick="createGroup();"><i class="fas fa-plus me-2"></i>Create group</button>
                            <button class="dropdown-item" type="button" id="GroupEditbutton" onclick="editGroup();"><i class="fas fa-pen me-2"></i>Edit group</button>
                            <button class="dropdown-item" type="button" id="GroupDeletebutton" onclick="DeleteGroup();"><i class="fas fa-trash me-2"></i>Delete group</button>
                        </div>
                    </div>
                <?php endif; ?>
                <button type="button" class="ox-icon-btn ox-icon-btn-lg" id="helpButton" onclick="showEventDetailsModal();" aria-label="How to use the calendar" title="How to use the calendar (?)">
                    <i class="fas fa-question"></i>
                </button>
            </div>
        </div>

        <div class="ox-legend" id="oxLegend" aria-label="Legend"></div>

        <!-- Calendar Display Section -->
        <div id="calendar" class="calendar"></div>
    </section>
</div>

<!-- Modal for Scheduling Meeting -->
<div class="modal fade text-left" id="meetingModal" tabindex="-1" role="dialog" aria-labelledby="myMeetingModal"
    aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document" id="modal-size">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Schedule meeting</h5>
                <button type="button" class="btn-close custom-close-btn" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <!--Ram -->
                <div id="timeLogSection" class="timeLogSection">
                    <div class="container">
                        <div class="row">
                            <!-- Left side: Meeting Details -->
                            <div class="col-md-6">
                                <div class="meeting-details">
                                    <p><b>Meeting host</b><span>: </span> <span id="mHost"></span></p>
                                    <p><b>Meeting name</b><span>: </span><span id="mName"></span></p>
                                    <p><b>Meeting description</b><span>: </span> <span id="mDescription"></span></p>
                                    <p><b>Meeting date</b><span>: </span><span id="mDate"></span></p>
                                    <p><b>Meeting start time</b><span>: </span><span id="mstartTime"></span></p>
                                    <p><b>Meeting end time</b><span>: </span><span id="mendTime"></span></p>
                                    <p><b>Meeting duration</b><span>: </span> <span id="mDuration"></span></p>
                                    <p><b>Product:</b><span>: </span><span id="mProduct"></span></p>

                                </div>
                            </div>

                            <!-- Right side: Attendees with input boxes -->
                            <div class="col-md-6">
                                <p style=" color: #167a40 ;"><strong>Meeting Attendees</strong></p>


                                <form class="row g-3 needs-validation" id="timeLogForm" method="POST" novalidate>
                                    <input type="hidden" id="meetingIdHiddenInput" name="meetingId">
                                    <input type="hidden" id="comments" name="comments">
                                    <input type="hidden" id="sdate" name="sdate">
                                    <input type="hidden" id="product" name="product">
                                    <input type="hidden" id="mtype" name="mtype">
                                    <input type="hidden" id="sprintId" name="sprintId">


                                    <div class="meeting-attendees scrollable" id="attendeesList">
                                        <!-- Attendees will be populated here dynamically -->
                                    </div>
                                    <button type="submit" id="timelogbutton" class="btn btn-primary me-1 mb-2">Log
                                        Time</button>
                                </form>

                            </div>

                        </div>
                    </div>
                </div>

                <!-- Form for Scheduling Meeting -->
                <div id="scheduleMeetingForm">
                    <form class="row g-3 needs-validation" id="meetingForm" novalidate>

                        <!-- Hidden Field for Selected Emails -->
                        <input type="hidden" id="selectedEmailsInput" name="selectedEmails" value="">
                        <input type="hidden" id="meeting_details_id" name="meeting_id" value="">
                        <input type="hidden" id="formOperation" name="formOperation" value="insert">
                        <input type="hidden" id="recurranceId" name="recurranceId">


                        <!-- Form Field: Meeting Title -->
                        <div class="col-md-6">
                            <label for="meeting_title" class="form-label">Meeting title</label>
                            <input type="text" class="form-control" id="meeting_title" name="meeting_title"
                                placeholder="Enter the meeting title" required>
                            <div class="invalid-feedback">Please provide a meeting title.</div>
                        </div>

                        <!-- Form Field: Product -->
                        <div class="col-md-6">
                            <label for="r_product_id" class="form-label">Product</label>
                            <select class="form-select" id="product_div" name="r_product_id" required>
                                <option value="" disabled selected>Select the product</option>
                                <?php foreach ($data['product'] as $value) {
    echo '<option value="' . esc($value['external_project_id'], 'attr') . '">' . esc($value['product_name']) . '</option>';
}?>
                            </select>
                            <div class="valid-feedback">Looks good!</div>
                            <div class="invalid-feedback">Please choose a product.</div>
                        </div>

                        <!-- Form Field: Meeting Type -->
                        <div class="col-md-6">
                            <label for="r_meeting_type_id" class="form-label">Meeting type</label>
                            <select class="form-select" id="meeting_type" name="r_meeting_type_id" required>
                                <option value="" disabled selected>Select the meeting type</option>
                                <?php foreach ($data['meetingType'] as $value) {
    echo '<option value="' . esc($value['meeting_type_id'], 'attr') . '">' . esc($value['meeting_type_name']) . '</option>';
}?>
                            </select>
                            <div class="valid-feedback">Looks good!</div>
                            <div class="invalid-feedback">Please choose a meeting type.</div>
                        </div>

                        <!-- Form Field: Backlog Items -->
                        <div class="col-md-6" id="backlog-container" style="display: none;">
                            <label for="r_backlog_item_id" class="form-label">Backlog item name</label>
                            <select class="form-select" id="backlog" name="r_backlog_item_id">
                                <option value="" disabled selected>Select the backlog</option>
                            </select>
                            <div class="invalid-feedback">Please provide a brainstorming title</div>
                        </div>

                        <!-- Form Field: Epic Items -->
                        <div class="col-md-6" id="epic-container" style="display: none;">
                            <label for="epicdropdown" class="form-label">Epic item name</label>
                            <input type="text" class="form-control" id="epicdropdown" name="r_epic_id"
                                data-bs-toggle="dropdown" aria-expanded="false" placeholder="Choose the epic" readonly>
                            <input type="hidden" id="epicHidden" name="epicHidden">
                            <ul class="dropdown-menu" aria-labelledby="epicdropdown">
                                <div id="epic-checkbox-container"></div>
                            </ul>
                        </div>

                        <!-- Form Field: User Story Items -->
                        <div class="col-md-6" id="userstory-container" style="display:none" ;>
                            <label for="userstory" class="form-label">User story</label>
                            <input type="text" class="form-control" id="userstory" name="userstory"
                                data-bs-toggle="dropdown" aria-expanded="false" placeholder="Choose the UserStory"
                                readonly>
                            <input type="hidden" id="userstoryHidden" name="userstoryHidden">
                            <ul class="dropdown-menu" aria-labelledby="userstory">
                                <li id="defaultMessage">
                                    <label class="dropdown-item px-3 text-danger">Select an epic to see user
                                        stories.</label>
                                </li>
                                <li id="allUserStories" class="d-none">
                                    <label class="px-3">
                                        <input type="checkbox" id="selectAllUserStories"> Select all
                                    </label>
                                </li>
                                <li id="userstoryDropdown"></li>
                            </ul>
                        </div>

                        <!-- Form Field: sprint Type -->
                        <div class="col-md-6" id="sprint_div" style="display: none;">
                            <label for="r_sprint_id" class="form-label">Sprint</label>
                            <select class="form-select" id="spId" name="r_sprint_id">
                                <option value="" disabled selected>Select the sprint</option>
                            </select>
                        </div>

                        <!-- Form Field: Recurrence Count -->
                        <div class="col-md-6" id="recurrence_meeting_container" style="display: none;">
                            <label for="recurrence_meeting" id="labelok" class="form-label">Recurrence meeting</label>
                            <select class="form-select" id="recurrence_meeting" name="recurrance_meeting_id" required>
                                <option value="" disabled selected>Select the recurrence</option>
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
                        <input type="hidden" id="selected_recurrence" name="selected_recurrence" value="">

                        <!-- Form Field: Sprint Start Date -->
                        <div class="col-md-6" id="sprint_start_date_container" style="display: none;">
                            <label for="sprint_start_date" class="form-label">Recurrence start date</label>
                            <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input"
                                id="sprint_start_date" name="sprint_start_date" accept=""
                                placeholder="Select recurrence start date" required>
                            <div class="invalid-feedback">Please choose a recurrence start date.</div>
                        </div>

                        <!-- Form Field: Sprint End Date -->
                        <div class="col-md-6" id="sprint_end_date_container" style="display: none;">
                            <label for="sprint_end_date" class="form-label">Recurrence end date</label>
                            <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input"
                                id="sprint_end_date" name="sprint_end_date" accept=""
                                placeholder="Select recurrence end date" required>
                            <div class="invalid-feedback">Please choose a recurrence end date.</div>
                        </div>

                        <!-- Form Field: Meeting Duration -->
                        <div class="col-md-6">
                            <label for="meeting_duration" class="form-label" style="margin-right: 130px;">Hours</label>
                            <label for="meeting_duration" class="form-label">Minutes</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="meeting_duration_hours"
                                    name="meeting_duration_hours" min="0" max="24" placeholder="0">
                                <input type="number" class="form-control" id="meeting_duration_minutes"
                                    name="meeting_duration_minutes" min="0" max="59" placeholder="00" required>
                            </div>
                        </div>

                        <!-- Form Field: Meeting Start Date -->
                        <div class="col-md-3" id="meeting_start_date_container">
                            <label for="meeting_start__date" class="form-label">Meeting start date</label>
                            <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input"
                                id="meeting_start_date" name="meeting_start_date" accept="" placeholder="Select date"
                                required>
                            <div class="invalid-feedback">Please choose a meeting date.</div>
                        </div>

                        <!-- Form Field: Meeting Start Time -->
                        <div class="col-md-3">
                            <label for="meeting_start_time" class="form-label">Meeting start time</label>
                            <select class="form-select" id="startTimeSelect" name="meeting_start_time"
                                required></select>
                            <div class="invalid-feedback">Please choose a meeting start time.</div>
                        </div>

                        <!-- Form Field: Meeting End Date -->
                        <div class="col-md-3" id="meeting_end_date_container">
                            <label for="meeting_end_date" class="form-label">Meeting end date</label>
                            <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input"
                                id="meeting_end_date" name="meeting_end_date" accept="" placeholder="Select date"
                                required>
                            <div class="invalid-feedback">Please choose a meeting date.</div>
                        </div>

                        <!-- Form Field: Meeting End Time -->
                        <div class="col-md-3">
                            <label for="meeting_end_time" class="form-label">Meeting end time</label>
                            <select class="form-select" id="endTimeSelect" name="meeting_end_time" required></select>
                            <div class="invalid-feedback">Please choose a meeting end time.</div>
                        </div>

                        <!-- Form Field: Meeting Location -->
                        <div class="col-md-6">
                            <label for="r_meeting_location_id" class="form-label">Meeting location</label>
                            <select class="form-select" id="meeting_location" name="r_meeting_location_id" required>
                                <option value="">Select the meeting location</option>
                                <?php foreach ($data['meetingLocation'] as $value) {
    echo '<option value="' . esc($value['meeting_location_id'], 'attr') . '">' . esc($value['meeting_location_name']) . '</option>';
}?>
                            </select>
                            <div class="valid-feedback">Looks good!</div>
                            <div class="invalid-feedback">Please choose a meeting location.</div>
                        </div>

                        <!-- Form Field: Meeting Link -->
                        <div class="col-md-6">
                            <label for="meeting_link" class="form-label">Meeting link / place</label>
                            <input type="text" class="form-control" id="meeting_link" name="meeting_link"
                                placeholder="Enter the meeting link" required>
                            <div class="valid-feedback">Looks good!</div>
                            <div class="invalid-feedback">Please provide a meeting link / Place</div>
                        </div>

                        <!-- Form Field: Meeting Description -->
                        <div class="col-md-6">
                            <label for="meeting_description" class="form-label">Meeting description</label>
                            <textarea type="text" class="form-control" id="meeting_description"
                                name="meeting_description" placeholder="Enter the description"></textarea>
                            <div class="valid-feedback">Looks good!</div>
                            <div class="invalid-feedback">Please provide a meeting description.</div>
                        </div>


                        <!-- Form Field: Team Members -->
                        <div class="col-md-6">
                            <label for="meeting_team" class="form-label">
                                <div class="btn-group" role="group" aria-label="Invite options">
                                    <button type="button" class="cls-add-members" onclick="showAddTeamMember()">Add team
                                        member</button>
                                    <button type="button" class="cls-add-members" onclick="showInviteGroup()">/ Invite
                                        group</button>
                                </div>
                            </label>
                            <div class="dropdown">
                                <div id="addTeamMemberSection" style="display: none;">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" id="teamInput"
                                            placeholder="Search team members" onkeyup="filterEmails()"
                                            autocomplete="off">
                                    </div>
                                    <div id="emailDropdown" class="dropdown-menu">
                                    </div>
                                </div>
                                <div id="inviteGroupSection" style="display: none;">
                                    <input type="text" class="form-control" id="addgroup" name="addgroup"
                                        data-bs-toggle="dropdown" aria-expanded="false" placeholder="Choose the Group"
                                        readonly>
                                    <ul class="dropdown-menu" aria-labelledby="addgroup">
                                        <?php if (empty($data['groupName'])): ?>
                                        <label class="dropdown-item px-3 text-danger">No Group Available</label>
                                        <?php else: ?>
                                        <?php foreach ($data['groupName'] as $value): ?>
                                        <label class="dropdown-item-group px-3">
                                            <input type="checkbox" class="team-checkbox"
                                                value="<?=trim(ucfirst(strtolower($value['meeting_team_id'])))?>"
                                                data-description="<?=trim(ucfirst(strtolower($value['meeting_team_name'])))?>">
                                            <?=trim(ucfirst(strtolower($value['meeting_team_name'])))?>
                                        </label>
                                        <?php endforeach;?>
                                        <?php endif;?>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div id="selectedEmails" class="selected-emails"></div>
                        </div>

                        <!-- Add this inside your form, perhaps near the submit button -->
                        <div id="updateAsSeriesDiv" style="display: none;">
                            <div class="form-check" style="padding-left : 340px;">
                                <input class="form-check-input" type="checkbox" id="updateAsSeries"
                                    name="updateAsSeries" value="1">
                                <label class="form-check-label" for="updateAsSeries">
                                    Update as series
                                </label>
                            </div>
                        </div>

                        <!-- Form Submit Button -->
                        <div class="modal-footer d-flex justify-content-center">
                            <button class="button" type="submit" name="Schedulebutton" id="Schedulebutton"
                                value="submit">Schedule</button>
                        </div>
                    </form>
                </div>

                <!-- Event Details Section -->
                <div id="eventDetails" class="eventDetails">
                    <div style="display: flex;">
                        <div class="col-md-6">
                            <p><b>Meeting title</b><span>:</span> <span id="eventTitle"></span></p>
                        </div>
                        <div class="col-md-6 view-details" id="viewDetails" style="display: none;"><a href=""></a></div>
                    </div>
                    <p><b>Meeting host</b><span>:</span> <span id="eventHost"></span></p>
                    <p><b>Product name</b><span>:</span> <span id="productName"></span></p>
                    <p><b>Sprint name</b><span>:</span> <span id="sprintName"></span></p>
                    <p><b>Meeting date</b><span>:</span> <span id="eventDate"></span></p>
                    <p><b>Meeting duration</b><span>:</span> <span id="eventduration"></span></p>
                    <p><b>Meeting time</b><span>:</span> <span id="eventStart"></span></p>
                    <p id="peventdesc"><b>Meeting description</b><span>:</span> <span id="eventdesc"></span></pD>
                    <p id="pmeetingLink"><b>Meeting link</b><span>:</span> <a id="meetingLink" href=""><span
                                id="eventDescription"></span></a></p>
                    <p id="peventMembers"><b>Meeting members</b><span>:</span> <span id="eventMembers"></span></p>
                    <p id="pcancel"><b>Cancelled reason</b><span>:</span> <span id="cancel"></span></p>
                    <button class="btn btn-primary me-1 mb-2" name="editMeeetingButton" id="editMeetingButton"
                        style="display: none;">Edit meeting</button>
                    <button class="btn btn-danger me-1 mb-2" id="cancelMeetingButton" style="display: none;">Cancel
                        meeting</button>
                </div>

                <!-- Help Details Section -->
                <div id="helpdetails" class="helpdetails">
                    <h6>Schedule meeting:</h6>
                    <p>Choose a date from the calendar to schedule our meetings.</p>
                    <h6>Group creation for meeting:</h6>
                    <p>To create a group for the meeting click group dropdown in the calendar</p>
                    <h6>Status dropdown colours:</h6>
                    <p>Explaining the meaning of each status and their associated colours.</p>
                    <b class="cls-types">For meetings:</b>
                    <div>
                        <span class="badge ongoing col-md-2"><b>Ongoing</b></span> This colour indicates ongoing
                        meetings.
                        <br>
                        <span class="badge upcoming col-md-2"><b>Upcoming</b></span> This colour indicates upcoming
                        meetings.
                        <br>
                        <span class="badge completed col-md-2"><b>Completed</b></span> This colour indicates completed
                        meetings.
                        <br>
                        <span class="badge cancelled col-md-2"><b>Cancelled</b></span> This colour indicates cancelled
                        meetings.
                        <br>
                        <span class="badge needtolog col-md-2"><b>Need to log</b></span> This colour indicates to Log
                        the meeting duration for attendees.
                    </div>
                    <br>
                    <b class="cls-types">For sprints:</b>
                    <div>
                        <span class="badge ongoing col-md-2"><b>Ongoing</b></span> This colour indicates ongoing
                        sprints.
                        <br>
                        <span class="badge upcoming col-md-2"><b>Upcoming</b></span> This colour indicates upcoming
                        sprints.
                        <br>
                        <span class="badge onhold col-md-2"><b>On hold</b></span> This colour indicates on hold sprints.
                        <br>
                        <span class="badge completed col-md-2"><b>Completed</b></span> This colour indicates completed
                        sprints.
                    </div>
                </div>

                <div id="groupdetails">
                    <form class="row g-3 needs-validation" id="groupForm" method="POST" novalidate>
                        <input type="hidden" name="groupId" id="groupId"
                            value="<?=isset($data['groupId']) ? $data['groupId'] : ''?>">
                        <div class="col-md-6" id="productDetails"></div>
                        <div class="col-md-6" id="showDetails"></div>
                        <div class="col-md-6" id="editGroup"></div>
                        <div class="col-md-12" id="memberDetails">
                            <label class="form-label">Add members</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="memberSearch"
                                    placeholder="Search members...">
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th class="cls-membersth"><input type="checkbox" id="selectAllMembers">
                                                Select all</th>
                                            <th class="cls-membersth">User name</th>
                                        </tr>
                                    </thead>
                                    <tbody id="memberTableBody">
                                    </tbody>
                                </table>
                                <div id="paginationControls" class="d-flex justify-content-end align-items-center">
                                </div>
                            </div>
                        </div>
                        <!-- Hidden input to hold the selected members -->
                        <input type="hidden" id="groupProductId" name="r_product_id" value="">
                        <input type="hidden" id="groupIdByName" name="meeting_team_id" value="">
                        <input type="hidden" name="selectedMembers" id="selectedMembers">
                        <input type="hidden" id="groupData" value="<?= esc(json_encode($data['groupName']), 'attr') ?>">
                        <input type="hidden" id="productData" value="<?= esc(json_encode($data['product']), 'attr') ?>">

                        <div class="modal-footer">
                            <button class="btn btn-primary me-1 mb-2" type="submit" name="groupbutton" id="groupbutton"
                                value="submit">Create group</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<!--sprint Modal -->
<div class="modal" id="sprintModal" tabindex="-1" aria-labelledby="sprintModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sprintModalLabel">Sprint Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <div id="sprintDetails" class="sprintDetails mb-4">
                    <p><b>Sprint Name</b> <span>:</span> <span id="sprintNames"></span></p>
                    <p><b>Sprint Creator</b> <span>:</span> <span id="sprintCreator"></span></p>
                    <p><b>Product Name</b> <span>:</span> <span id="sprintProduct"></span></p>
                    <p><b>Customer</b> <span>:</span> <span id="sprintCustomer"></span></p>
                    <p><b>Duration</b> <span>:</span> <span id="sprintDuration"></span></p>
                    <p><b>Start Date</b> <span>:</span> <span id="startDate"></span></p>
                    <p><b>End Date</b> <span>:</span> <span id="endDate"></span></p>
                    <p><b>Sprint Status</b> <span>:</span> <span id="sprintStatus"></span></p>
                </div>
                <div class="button-container" id="orgButton">
                    <?php if (has_permission('sprint/changeSprintStatusById')): ?>
                    <button type="button" class="btn btn-primary" id="changeStatusBtn">
                        <i class="bi bi-check-square"></i> Change Status
                    </button>
                    <?php endif;?>
                    <?php if (has_permission('sprint/navsprintview')): ?>
                    <a href="" id="viewDetailsLink">
                        <button type="button" class="btn btn-primary" id="viewDetailsBtn">
                            <i class="bi bi-eye"></i> View Details
                        </button>
                    </a>
                    <?php endif;?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Including FullCalendar Library -->
<script src='<?=ASSERT_PATH?>assets/fullcalendar/dist/index.global.min.js'></script>

<script>
// JavaScript variables for meeting details and base URL
var meetingDetails = <?=json_encode($data['showMeeting'], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?>;
var productList = <?=json_encode($data['product'], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?>;
var sprintDetails = <?=json_encode($data['sprintDetails'], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?>;
var meetType = <?=json_encode($data['meetingType'], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?>;
var duration = <?=json_encode($data['sprintDuration'], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?>;
var sprintStatus = <?=json_encode($data['sprintStatus'], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?>;
var holidayDetails = <?=json_encode($data['holidays'], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?>;
var sessionId = <?= (int) session()->get('employee_id') ?>;
var baseUrl = <?= json_encode(base_url(), JSON_UNESCAPED_SLASHES) ?>;
var permissions = {
    'groupButton': <?=json_encode(has_permission('meeting/createGroupDetails'))?>,
    'scheduleMeeting': <?=json_encode(has_permission('meeting/scheduleMeeting'))?>,
    'changestatus': <?=json_encode(has_permission('sprint/changeSprintStatusById'))?>,
    'viewSprint': <?=json_encode(has_permission('sprint/navsprintview'))?>
};

//module name
const calendar = "calendar";
</script>