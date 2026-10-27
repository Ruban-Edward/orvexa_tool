<?php
$pId = $data['product_id'];
$userName = $data['userName'];
$pblId = $data['backlog_item_id'];
$epicByBrainstrom = $data['epicByBrainstrom'];
$productName = $data['product_name'];
$group = $data['teamMembers'];
$backlogItemName = $data['backlog_item_name'];
$epic = $data['epic'];
$meetingLocation = $data['meetingLocation'];
$status = $data['user_story_status'];
$comments = $data['totalcomments'];
$fibonacciLimit = $data['fibonacciLimit'];
$current_user = $data['current_user'];

// $data = $data['userStory_details'];
$user_story_id = 0;
// if (!empty($data)) {
//     $user_story_id = $data[0]['user_story_id'];
// }
?>
<dl class="ox-context">
    <div><dt>Product</dt><dd><?= esc(trim(ucfirst(strtolower($productName)))); ?></dd></div>
    <div><dt>Backlog item</dt><dd><?= esc(trim(ucfirst(strtolower($backlogItemName)))); ?></dd></div>
    <div><dt>User stories</dt><dd><?= (int) $data['story_count'] ?></dd></div>
</dl>

<div class=" d-flex justify-content-between flex-wrap flex-md-nowrap cls-backlog-action-buttons">

    <div class="cls-backlog-buttons-left-side">
        <?php if (has_permission("backlog/addepic")): ?>
        <button class="btn button-secondary flex-start" data-bs-toggle="modal" data-bs-target="#epicAddModal">
            <i class="icon-plus-circle sprintListIcon"></i> Add epic
        </button>
        <?php endif;?>

        <?php if (has_permission("backlog/addUserStory")): ?>
        <button id="addUserStoriesBtn" class="btn primary_button" data-has-epics="<?=!empty($epic) ? 'true' : 'false'?>"
            onclick="openAddModal()">
            <i class="icon-plus-circle sprintListIcon"></i> Add user story
        </button>
        <?php endif;?>

        <?php if (has_permission("backlog/uploaduserstories")): ?>

        <button type="button" class="btn button-secondary" data-bs-toggle="modal" data-bs-target="#fileUploadModal">
            <i class="icon-upload"></i> Import user stories
        </button>


        <?php endif;?>
    </div>


    <div class="header-action-buttons cls-backlog-buttons-right-side">
        <?php if ($data['story_count'] > 0): ?>
        <?php if (has_permission("meeting/scheduleMeeting")): ?>
        <button class="button-secondary" data-bs-toggle="modal" data-bs-target="#brainstormingModal">
            <i class="icon-sunrise"> </i>  <span class="cls-action-buttons">Brainstorming meeting</span>
        </button>
        <?php endif;?>

        <a href="<?=site_url('backlog/downloadUserStories?pblid=' . $pblId)?>" class="button-secondary"><i
                class="icon-download"></i> <span class="cls-action-buttons">Export </span></a>

        <div class="dropdown">
            <button id="filter_btn" class="button-secondary">
                <i class="icon-filter"></i> <span class="cls-action-buttons"> Filter</span>
                <span id="noti" class="badge badge-pill badge-danger"></span>
            </button>
        </div>
        <?php endif?>
    </div>
</div>
<?php if ($data['story_count'] > 0): ?>
<div class="mt-3 d-flex justify-content-end">
    <div class="search-container">
        <div class="search-box">
            <input type="text" id="backlogSearchInput" class="search-input" placeholder="Search user story">
            <button id="search_btn" class="search-button">
                <i class="bi bi-search"></i>
                <span>Search</span>
            </button>
        </div>
    </div>
</div>
<?php endif?>
<?php if ($data['story_count'] > 0): ?>
<div id="userStoryContainer" class="userstory-container"></div>

<div id="paginationControls" class="d-flex justify-content-between align-items-center mt-3">
    <button id="prevPage" class="button-secondary">Prev</button>
    <span id="pageInfo" class="mx-3"></span>
    <button id="nextPage" class="button-secondary">Next</button>
</div>

<?php endif?>

<div id="empty" class="align-items-center justify-content-center">
    <div class="text-center" style="margin:100px;">
        <h1 class="display-1 text-primary mb-4"><span class="bi bi-emoji-frown cls-noProductIcon"></span></h1>
        <h2 class="card-title text-primary mb-4">No user story found</h2>
    </div>
</div>


<div class="modal" id="epicAddModal" tabindex="-1" aria-labelledby="userstoryModalLabel" aria-hidden="true"
    data-bs-backdrop="static">
    <div id="size" class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTitle">Add epic</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <form class="row g-3 needs-validation" id="addEpicform"
                    action="<?php echo ASSERT_PATH . 'backlog/addepic?pid=' . $pId . '&pblid=' . $pblId ?>"
                    method="POST" enctype="multipart/form-data" novalidate
                    onsubmit="event.preventDefault(); handleFormSubmission({formId: 'addEpicform',modalId: 'addTaskModal',successTitle: 'Epic Added'});">
                    <input type="number" value="<?=$pblId?>" id="pblid" name="pblid" hidden>
                    <div class="col-md-6">
                        <label for="productname" class="form-label form-need">Product name</label>
                        <input type="text" id="productname" class="form-control readonly" name="product name"
                            value="<?php echo $productName ?>" readonly>
                        <div class="valid-feedback">Looks good!</div>
                        <div class="invalid-feedback">Enter product</div>
                    </div>
                    <div class="col-md-6">
                        <label for="backlogitem" class="form-label form-need ">Backlog item name</label>
                        <input type="text" id="backlog item" class="form-control readonly" name="backlog item"
                            value="<?php echo trim(ucfirst(strtolower($backlogItemName))) ?>" readonly>
                        <div class="valid-feedback">Looks good!</div>
                        <div class="invalid-feedback">Choose Priority</div>
                    </div>
                    <div>
                        <label for="epic" class="form-label form-need">Epic description</label>
                        <textarea class="form-control" name="epic_description" id="epic" placeholder="Your epic"
                            required></textarea>

                    </div>

                    <div class="modal-footer d-flex justify-content-center">
                        <button type="submit" class="btn primary_button" name="epicsubmit" id="submitBtn">Add
                            epic</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal" id="epicModal" tabindex="-1" aria-labelledby="userstoryModalLabel" aria-hidden="true"
    data-bs-backdrop="static">
    <div id="size" class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStoryTitle">Add userstory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <form class="row g-3 needs-validation" id="addUserStoryform"
                    action="<?php echo ASSERT_PATH . 'backlog/addUserStory?pid=' . $pId . '&pblid=' . $pblId; ?>"
                    method="POST" enctype="multipart/form-data" novalidate
                    onsubmit="event.preventDefault(); handleFormSubmission({formId: 'addUserStoryform',modalId: 'addUserStoryModal'});">
                    <input type="number" value="<?=$pblId?>" id="pblid" name="pblid" hidden>
                    <div class="col-md-6">
                        <label for="productname" class="form-label form-need">Product name</label>
                        <input type="text" id="product name" class="form-control readonly" name="product name"
                            value="<?php echo $productName ?>" readonly>
                        <div class="valid-feedback">Looks good!</div>
                        <div class="invalid-feedback">Enter product</div>
                    </div>
                    <div class="col-md-6">
                        <input type="hidden" id="userId" name="userId">
                        <label for="backlog item" class="form-label form-need">Backlog item name</label>
                        <input type="text" id="backlog item" class="form-control readonly" name="backlog item"
                            value="<?=trim(ucfirst(strtolower($backlogItemName)))?>" readonly>
                        <div class="valid-feedback">Looks good!</div>
                        <div class="invalid-feedback">Choose Priority</div>
                    </div>
                    <div class="col-md-3 d-flex align-items-center"><label for="epics"
                            class="form-label text-center">Select the epic</label></div>

                    <div class="col-md-9">
                        <select class="form-select cls-select" id="epics" name="r_epic_id"
                            aria-label="Default select example" required>
                            <?php
foreach ($epic as $value): ?>

                            <option class="dropdown-epic" value="<?=$value['epic_id']?>"><?=$value['epic_description']?>
                            </option>

                            <?php endforeach?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-center"><label for="userStories_status"
                            class="form-label">Select the status</label></div>
                    <div class="col-md-9">
                        <select class="form-select" id="userStories_status" name="r_module_status_id"
                            aria-label="Default select example" required>
                            <?php foreach ($status as $value): ?>
                            <option value="<?=$value['status_id']?>"><?=ucwords($value['status_name'])?></option>
                            <?php endforeach?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <h4>User story</h4>
                    </div>
                    <div class="col-md-6">
                        <label for="as" class="form-label form-need">As a /an</label>
                        <textarea class="form-control" name="as_a_an" id="as" placeholder="Your Message"
                            required></textarea>
                        <div class="valid-feedback">Looks good!</div>
                        <div class="invalid-feedback">Enter as / a </div>
                    </div>
                    <div class="col-md-6">
                        <label for="Iwant" class="form-label form-need">I want</label>
                        <textarea class="form-control" name="i_want" id="Iwant" placeholder="Your Message"
                            required></textarea>
                        <div class="valid-feedback">Looks good!</div>
                        <div class="invalid-feedback">Enter I want </div>
                    </div>

                    <div class="col-md-6">
                        <label for="sothat" class="form-label form-need">So that</label>
                        <textarea class="form-control" name="so_that" id="sothat" placeholder="Your Message"
                            required></textarea>
                        <div class="valid-feedback">Looks good!</div>
                        <div class="invalid-feedback">Enter so that</div>
                    </div>

                    <div class="col-md-12">
                        <h4>Acceptance criteria</h4>
                    </div>

                    <div class="col-md-6">
                        <label for="given" class="form-label form-need">Given</label>
                        <textarea class="form-control" name="given" id="given" placeholder="Your Message"
                            required></textarea>
                        <div class="valid-feedback">Looks good!</div>
                        <div class="invalid-feedback">Enter Given </div>
                    </div>

                    <div class="col-md-6">
                        <label for="when" class="form-label form-need">When</label>
                        <textarea class="form-control" name="us_when" id="when" placeholder="Your Message"
                            required></textarea>
                        <div class="valid-feedback">Looks good!</div>
                        <div class="invalid-feedback">Enter when </div>
                    </div>

                    <div class="col-md-12">
                        <label for="default" class="form-label form-need">Then</label>
                        <section class="section">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-body">
                                        <textarea id="default" class="thenTextArea" name="us_then" cols="30" rows="10"
                                            required></textarea>
                                        <div class="valid-feedback">Looks good!</div>
                                        <div class="invalid-feedback">Enter the then</div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="col-md-12">
                        <label for="default2" class="form-label form-need">Condition</label>
                        <section class="section">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-body">
                                        <textarea id="default2" class="conditionTextArea" name="condition_text"
                                            cols="30" rows="10"></textarea>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="modal-footer d-flex justify-content-center">
                        <button type="submit" class="btn primary_button" name="userstorysubmit" id="submitBtn1">Add user
                            story</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="fileUploadModal" tabindex="-1" aria-labelledby="fileUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileUploadModalLabel">Import user stories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="fileUploadForm" action="<?php echo base_url('backlog/uploaduserstories'); ?>" method="POST"
                    enctype="multipart/form-data">
                    <input name='pblId' value="<?=$pblId?>" hidden>
                    <input name='pId' value="<?=$pId?>" hidden>
                    <div class="mb-3">
                        <label for="file" class="form-label">Choose file</label>
                        <a class="download" href="<?=base_url('backlog/downloadReference/samplefile.csv')?>">
                            <i class="icon-download"></i>Reference file
                        </a>
                        <input type="file" class="form-control" id="file" name="file"
                            placeholder="Only CSV file can be uploaded" accept=".csv" required>
                        <span style="color:red;display:none">Only CSV file are accepted</span>
                    </div>
                    <div class="modal-footer d-flex justify-content-center">
                        <button type="submit" class="btn primary_button">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--  brainstorming -->
<div class="modal" id="brainstormingModal" tabindex="-1" aria-labelledby="brainstormingModal" aria-hidden="true"
    data-bs-backdrop="static">
    <div id="size" class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="brainstormingModal">Brainstorming meeting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <form class="row g-3 needs-validation" id="brainstormMeetForm" method="POST" novalidate>

                    <input type="hidden" id="selectedEmailsInput" name="selectedEmails" value="">

                    <div class="col-md-6">
                        <label for="product_name" class="form-label">Product name</label>
                        <input type="text" class="form-control" id="product_name" name="product_name"
                            value="<?=trim(ucfirst(strtolower($productName)));?>" readonly>
                        <input type="hidden" name="r_product_id" value="<?=$pId;?>">
                        <div class="invalid-feedback">Please provide a product name</div>
                    </div>

                    <div class="col-md-6">
                        <label for="backlog" class="form-label">Backlog item</label>
                        <input type="text" class="form-control" id="backlog" name="backlog"
                            value="<?=trim(ucfirst(strtolower($backlogItemName)));?>" readonly>
                        <div class="invalid-feedback">Please provide a brainstorming title</div>
                    </div>

                    <div class="col-md-6">
                        <label for="meeting_title" class="form-label">Meeting title</label>
                        <input type="text" class="form-control" id="meeting_title" name="meeting_title"
                            value="<?="Brainstorm - " . trim(ucfirst(strtolower($backlogItemName)));?>" required>
                        <div class="invalid-feedback">Please provide a meeting title.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="brainstorming_title" class="form-label">Meeting type</label>
                        <input type="text" class="form-control" id="brainstorming_title" name="brainstorming_title"
                            value="Brainstorming" readonly>
                        <input type="hidden" name="r_meeting_type_id" value="2">
                        <div class="invalid-feedback">Please provide a brainstorming title</div>
                    </div>

                    <!-- Added Epic Dropdown -->
                    <div class="col-md-6 epicDropdown">
                        <label for="epic" class="form-label">Epic item</label>
                        <input type="text" class="form-control" id="epicdropdown" name="epic" data-bs-toggle="dropdown"
                            aria-expanded="false" placeholder="Choose the Epic" readonly>
                        <input type="hidden" id="epicHidden" name="epicHidden">
                        <ul class="dropdown-menu" aria-labelledby="epicdropdown" id="epicDropdownMenu">
                            <?php if (empty($epicByBrainstrom)): ?>
                            <label class="dropdown-item px-3 text-danger">No epics available.</label>
                            <?php else: ?>
                            <?php foreach ($epicByBrainstrom as $value): ?>
                            <label class="dropdown-item-epic px-3">
                                <input type="checkbox" class="epic-checkbox"
                                    value="<?=trim(ucfirst(strtolower($value['epic_id'])))?>"
                                    data-description="<?=trim(ucfirst(strtolower($value['epic_description'])))?>">
                                <?=trim(ucfirst(strtolower($value['epic_description'])))?>
                            </label>
                            <?php endforeach?>
                            <?php endif?>
                        </ul>
                    </div>

                    <!-- Added User Story Dropdown -->
                    <div class="col-md-6 userStory">
                        <label for="userstory" class="form-label">User story</label>
                        <input type="text" class="form-control" id="userstory" name="userstory"
                            data-bs-toggle="dropdown" aria-expanded="false" placeholder="Choose the UserStory" required>
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


                    <div class="col-md-6">
                        <label for="meeting_location" class="form-label">Meeting location</label>
                        <select class="form-select" id="meeting_location" name="r_meeting_location_id" required>
                            <option value="">Select the meeting type</option>
                            <?php foreach ($meetingLocation as $value) {
    echo "<option value='{$value['meeting_location_id']}'>{$value['meeting_location_name']}</option>";
}?>
                        </select>
                        <div class="valid-feedback">Looks good!</div>
                        <div class="invalid-feedback">Please choose a meeting location.</div>
                    </div>

                    <!-- Form Field: Meeting Link -->
                    <div class="col-md-6">
                        <label for="meeting_link" class="form-label">Meeting link / Place</label>
                        <input type="text" class="form-control" id="meeting_link" name="meeting_link"
                            placeholder="Enter the meeting link" required>
                        <div class="valid-feedback">Looks good!</div>
                        <div class="invalid-feedback">Please provide a meeting link / Place</div>
                    </div>

                    <!-- Form Field: Meeting Duration -->
                    <div class="col-md-6">
                        <label for="meeting_duration" class="form-label" style="margin-right: 130px;">Hours</label>
                        <label for="meeting_duration" class="form-label">Minutes</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="meeting_duration_hours"
                                name="meeting_duration_hours" min="0" max="24" placeholder="0" required>
                            <input type="number" class="form-control" id="meeting_duration_minutes"
                                name="meeting_duration_minutes" min="0" max="59" placeholder="00" required>
                        </div>
                    </div>

                    <!-- Form Field: Meeting Start Date -->
                    <div class="col-md-3">
                        <label for="meeting_start_date" class="form-label">Meeting start date</label>
                        <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input"
                            id="meeting_start_date" name="meeting_start_date" value="<?=date('Y-m-d');?>" accept=""
                            placeholder="Select date" required>
                        <div class="invalid-feedback">Please choose a meeting start date.</div>
                    </div>

                    <!-- Form Field: Meeting Start Time -->
                    <div class="col-md-3">
                        <label for="meeting_start_time" class="form-label">Meeting start time</label>
                        <select class="form-select" id="startTimeSelect" name="meeting_start_time" required></select>
                        <div class="invalid-feedback">Please choose a meeting time.</div>
                    </div>

                    <!-- Form Field: Meeting End Date -->
                    <div class="col-md-3">
                        <label for="meeting_end_date" class="form-label">Meeting end date</label>
                        <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input"
                            id="meeting_end_date" name="meeting_end_date" value="<?=date('Y-m-d');?>" accept=""
                            placeholder="Select date" required>
                        <div class="invalid-feedback">Please choose a meeting enddate.</div>
                    </div>

                    <!-- Form Field: Meeting End Time -->
                    <div class="col-md-3">
                        <label for="meeting_end_time" class="form-label">Meeting end time</label>
                        <select class="form-select" id="endTimeSelect" name="meeting_end_time" required></select>
                        <div class="invalid-feedback">Please choose a meeting time.</div>
                    </div>



                    <!-- Form Field: Meeting Description -->
                    <div class="col-md-6">
                        <label for="meeting_description" class="form-label">Meeting description</label>
                        <textarea type="text" class="form-control" id="meeting_description" name="meeting_description"
                            placeholder="Enter the description"></textarea>
                        <div class="valid-feedback">Looks good!</div>
                        <div class="invalid-feedback">Please provide a meeting description</div>
                    </div>



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
                                        placeholder="Search team members" onkeyup="filterEmails()" autocomplete="off">
                                </div>
                                <div id="emailDropdown" class="dropdown-menu">
                                    <?php foreach ($userName as $value): ?>
                                    <label class='email-option' for=""
                                        onclick="updateSelectedEmails('<?=$value?>')"><?=$value?></label>
                                    <?php endforeach;?>
                                </div>
                            </div>
                            <div id="inviteGroupSection" style="display: none;">
                                <input type="text" class="form-control" id="addgroup" name="addgroup"
                                    data-bs-toggle="dropdown" aria-expanded="false" placeholder="Choose the Group"
                                    readonly>
                                <ul class="dropdown-menu" aria-labelledby="addgroup"
                                    style="max-height: 125%; overflow-y: scroll;">
                                    <?php if (empty($group)): ?>
                                    <label class="dropdown-item px-3 text-danger">No Group
                                        Available</label>
                                    <?php else: ?>
                                    <?php foreach ($group as $value): ?>
                                    <label class="dropdown-item-group px-3">
                                        <input type="checkbox" class="team-checkbox"
                                            value="<?=trim(ucfirst(strtolower($value['meeting_team_id'])))?>"
                                            data-description="<?=trim(ucfirst(strtolower($value['meeting_team_name'])))?>">
                                        <?=trim(ucfirst(strtolower($value['meeting_team_name'])))?>
                                    </label>
                                    <?php endforeach?>
                                    <?php endif?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div id="selectedEmails" class="selected-emails"></div>
                    </div>

                    <div class="modal-footer d-flex justify-content-center">
                        <button type="submit" class="btn primary_button" name="Schedulebutton" id="Schedulebutton"
                            form="brainstormMeetForm" value="submit">Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Sidebar for filter -->
<div id="filterSidebar" class="filter-sidebar">
    <div class="sidebar-content">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="filter-heading">Filters</h3>
            <button type="button" id="closeFilterSidebarBtn" class="btn-filterclose align-item-center"></button>
        </div>
        <form id="filterOptionsForm">
            <div class="mb-3">
                <label for="statusFilter" class="form-label filter-text">Filter by status</label>
                <select name="sprintname[]" id="statusFilter" placeholser="Select Sprint Name"
                    class="form-select select" multiple multiselect-search="true" multiselect-select-all="true"
                    multiselect-max-items="1">
                    <?php foreach ($status as $value): ?>
                    <option value="<?=$value['status_id'];?>"><?=trim(ucfirst(strtolower($value['status_name'])));?>
                    </option>
                    <?php endforeach;?>
                </select>
            </div>
            <div class="mb-3">
                <label for="epicFilter" class="form-label filter-text">Filter by epic description</label>
                <select name="sprintname[]" id="epicFilter" placeholser="Select Sprint Name" class="form-select select"
                    multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="1">
                    <?php foreach ($epic as $value) {
    echo '<option value="' . trim(ucfirst(strtolower($value["epic_id"]))) . '">' . trim(ucfirst(strtolower($value["epic_description"]))) . '</option>';
}?>
                </select>
            </div>
            <div class="d-flex justify-content-between mt-4">
                <button type="submit" class="btn primary_button apply-filters-btn" id="applyfilter">Apply</button>
                <button type="reset" class="btn button-secondary " id="resetFiltersBtn">Reset</button>
            </div>
        </form>
    </div>
</div>

<!-- M -->

<script>
var allUserStories = <?php echo json_encode($data); ?>;
var totalCount = <?php echo json_encode($data['story_count']); ?>;
var current_user = <?=json_encode($current_user)?>;
console.log("current_user", current_user);
var pId = <?=$pId?>;
var pblId = "<?=$pblId?>"
var baseUrl = '<?=base_url()?>';
var comments = <?=json_encode($comments)?>;
var assert_path = '<?=ASSERT_PATH?>';
var statuses = <?=json_encode($status)?>;

var link = <?=json_encode(array(ASSERT_PATH . "backlog/tasks?pid=$pId&pblid=$pblId&usid="))?>;
const userPermissions = {
    updateUserStory: <?php echo json_encode(has_permission('backlog/updateUserStory')); ?>,
    deleteUserStory: <?php echo json_encode(has_permission('backlog/deleteUserStory')); ?>,
    viewTask: <?php echo json_encode(has_permission('backlog/tasks')); ?>,
    revealPoker: <?php echo json_encode(has_permission('backlog/updatereveal')); ?>,
    addUserStoryPoint: <?php echo json_encode(has_permission('backlog/addUserStoryPoint')); ?>
};
var fibonacciList = <?=json_encode($fibonacciLimit)?>;
//module name
const m_userStories = [];
</script>