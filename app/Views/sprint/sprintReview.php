<?php
/**
 * @author VISHVA
 * 
 * @modified-by JEEVA
 * @created-date 10-07-2024
 * @modified-date 10-07-2024
 * 
 */
?>
<script>
    var sprintId = <?= json_encode($data['id']) ?>;
    var backlogData = <?= json_encode($data['sprintTask']) ?>;
    const path = "<?= ASSERT_PATH ?>";
    var members = <?= json_encode($data['productUsers']) ?>;
      //module name
      const m_sprintReview = [];
</script>
<div class="card mb-4">
    <div class="card-header">
        <h3><i class="fas fa-tasks me-2"></i>Task Status</h3>
    </div>
    <div class="card-body table-style">
        <!-- User Stories Form -->
        <form id="userStoriesForm" method="POST" class="formElements">
            <div class="mb-3">
                <div class="filter-container">
                    <label for="statusFilter">Filter by Status:</label>
                    <select id="statusFilter" onchange="applyStatusFilter(event)">
                        <option value="all">All</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                        <option value="Move to Prelive">Move to Prelive</option>
                        <option value="Assign for UAT">Assign for UAT</option>
                        <option value="Assign for Testing">Assign for Testing</option>
                        <option value="Move to Live">Move to Live</option>
                        <option value="OnHold">On Hold</option>
                    </select>
                </div>
                <div id="backlogName">

                </div>
                <div class="table-container" id='backlogTable'>
                    <table class="table table-striped">
                        <thead class="backlog-thead">
                            <tr class="backlog-table-heading" id='table-heading'>
                                <!-- <th>Backlog</th> -->
                                <th>Epic</th>
                                <th>Us Id</th>
                                <th>User Story</th>
                                <th>Task</th>
                                <th>Task Status</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            <!-- Table content will be generated dynamically -->
                        </tbody>
                    </table>


                </div>
                <div class="text-center cls-noProduct" id='errormsg'>
                    <span class="bi bi-emoji-frown cls-noProductIcon"></span>
                    <h4> No Such Tasks Available</h4>
                </div>
            </div>
        </form>
    </div>

    <nav aria-label="Page navigation" id="myNav" class="pagenav">
        <ul>
            <button id="prevbutton" class="pagebutton"><i class='fas fa-angle-left' style='font-size:36px'></i></button>
            <button id="nextbutton" class="pagebutton"><i class='fas fa-angle-right'
                    style='font-size:36px'></i></button>
        </ul>
    </nav>

</div>

<form id="sprintReviewForm" class="mt-4">
    <div class="card mb-4">
        <div class="card-header">
            <div class="row">
                <div class="col-4">
                    <h3>General Review</h3>
                </div>
                <div class="offset-4 col-2">
                    <label for="dateInput" class="form-label labelReview">Review Date</label>
                </div>
                <div class="col-2">
                    <input type="date" class="form-control" id="date-picker" name="reviewDate" aria-label="Review date" disabled required>
                </div>

            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <textarea class="form-control" id="lessonsLearned" rows="3" name="generalReview"
                    placeholder="Enter general review here" required></textarea>
                <button type="button" id="micButton" class="mic-button" aria-label="Dictate the general review"
                    onclick="voiceRecognition('micButton','lessonsLearned')">
                    <i class="bi bi-mic"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3></i>Code Review Status</h3>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Code Review Done?</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="codeReviewStatus" id="codeReviewYes" value="Y"
                        required>
                    <label class="form-check-label" for="codeReviewYes">Yes</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="codeReviewStatus" id="codeReviewNo" value="N">
                    <label class="form-check-label" for="codeReviewNo">No</label>
                </div>
            </div>
            <div class="mb-3" id="codeReviewReason" style="display: none;">
                <textarea class="form-control" id="codeReviewReasonText" rows="2" name="codeReview"
                    placeholder="Reason for not completing code review"></textarea>
                <button type="button" id="mic" class="mic-button" aria-label="Dictate the code review reason"
                    onclick="voiceRecognition('mic','codeReviewReasonText')">
                    <i class="bi bi-mic"></i>
                </button>
            </div>
            <div class="membervisibility" style="display: none">
                <div class="row">
                    <h6 class="mb-3 addmem" style="margin-top: 6px;margin-right: -20px;">Search Members</h6>
                    <div class="input-group mb-3 addmem" style="width: 25%;">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="memberSearch" placeholder="Search members">
                    </div>
                </div>
                <div id="selectedMembersContainer" class="mb-3"></div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAllMembers" aria-label="Select all members">
                                </th>
                                <th>User Name</th>
                                <th>Role</th>
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
    <div class="card mb-4">
        <div class="card-header">
            <h3>Challenges</h3>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Challenges Faced?</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="challengeStatus" id="challengeYes" value="Y"
                        required>
                    <label class="form-check-label" for="challengeYes">Yes</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="challengeStatus" id="challengeNo" value="N">
                    <label class="form-check-label" for="challengeNo">No</label>
                </div>
            </div>
            <div class="mb-3" id="challengeDetails" style="display: none;">
                <textarea class="form-control" id="challenges" rows="3" name="challengesReview"
                    placeholder="Describe any challenges encountered during the sprint"
                    placeholder="Enter faced challenges"></textarea>
                <button type="button" id="mic1" class="mic-button" onclick="voiceRecognition('mic1','challenges')">
                    <i class="bi bi-mic"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3>Sprint Goals</h3>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Sprint Goals Met?</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="goalsStatus" id="goalsMetYes" value="Y" required>
                    <label class="form-check-label" for="goalsMetYes">Yes</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="goalsStatus" id="goalsMetNo" value="N">
                    <label class="form-check-label" for="goalsMetNo">No</label>
                </div>
            </div>
            <div class="mb-3" id="goalsNotMetReason" style="display: none;">
                <textarea class="form-control" id="goalsNotMetReasonText" rows="2" name="goalsReview"
                    placeholder="Reason for not meeting goals"></textarea>
                <button type="button" id="mic2" class="mic-button"
                    onclick="voiceRecognition('mic2','goalsNotMetReasonText')">
                    <i class="bi bi-mic"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="text-center">
        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-paper-plane me-2"></i>Submit
            Review</button>
    </div>
</form>