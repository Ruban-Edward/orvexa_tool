<?php
/**
 * Sprint Dashboard Template
 * 
 * This template displays various sprint and project statistics including:
 * - Current sprint details
 * - Pending tasks
 * - Backlog items
 * - User stories overview
 * - Sprint burndown chart
 * - Overall backlog progress
 * - Current and upcoming sprints
 * - Overdue sprints
 */
 
// Extract data from the $data array
$userId  = session()->get('employee_id'); 
$currentSprintDetails = $data['currentSprintDetails'] ?? [];
$sprintDetails = $data['sprintDetails'] ?? [];
$userStoryStatusCounts = $data['userStoryStatusCounts'][0] ?? [];
$backlogCounts = $data['backlogStatusCounts'] ?? [];
$getTotalTaskHoursPerSprint = $data['totalTaskHoursPerSprint'] ?? 0;
$pendingTask = $data['pendingTaskStatusCounts'] ?? [];
// $burndownChartData = $data['burndownChartData'] ?? [];
$estimatedHours = $burndownChartData['estimatedHours'] ?? [];
$runningSprints=$data['runningSprints'] ?? [];

?>

<div class="mb-2">
    <?php if (count($runningSprints) > 1): ?>
        <form id="sprintForm" action="<?= base_url('dashboard/showProductDashboard') ?>" method="post" class="container">
            <div class="row align-items-center cls-dashboardRow">
                <div class="col-auto ms-1">
                    <label for="sprint" class="card-title"><h5>Select current running sprints: </h5></label>
                </div>
                <div class="col">
                    <select name="sprint_version" id="sprint" class="form-select text-dark select-sprint" onchange="document.getElementById('sprintForm').submit()">
                        <?php foreach ($runningSprints as $sprint): ?>
                            <option value="<?= $sprint['sprint_id'] ?>" <?= isset($currentSprintDetails['sprint_version']) && $currentSprintDetails['sprint_version'] == $sprint['sprint_version'] ? 'selected' : '' ?>>
                                <?= "Sprint version: " . $sprint['sprint_version'] . " - " . ucfirst($sprint['sprint_name'])  ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
 
            <!-- <i class="fa fa-chevron-down custom-select-icon"></i> -->
            <input type="hidden" name="product-id" value="<?= $data['productId'] ?? 0 ?>">
        </form>
    <?php elseif (count($runningSprints) === 0): ?>
        
    <?php else: ?>
        
    <?php endif; ?>
</div>


    
<div class="container">
    <div class="row " id="card-row">

        <!-- Backlog Items Card -->
        <div class="col-md-3">
            
              <form action="<?= base_url() ?>backlog/backlogitems" method="get" class="card bg-card-1 card-selector" id ="backlog-submit" data-toggle="tooltip" title="view backlog items">
                <input type="hidden" name="pid" value="<?= $data['productId'] ?? 0 ?>">
                    <div class="card-body " >

                        <h5 class="card-title text-start align-items-center d-flex">
                        <i class="fas fa-project-diagram me-2 cls-cardHeaderIcon"></i>
                            <div class="cls-cardHeaderTitle">
                            Overall product <span class="text-center">backlog items</span>
                            </div>
                        </h5>
                        <?php if (!empty($backlogCounts)): ?>
                            <div class="row mx-0 py-2 align-items-center">
                                <p class="card-text col-sm-9 px-0 my-0">
                                    <b>Total backlog items </b>
                                </p>
                                <div class="col-sm-3">
                                    <span class="badge px-3  ">
                                        <?php
                                        $backlogCounts['total'] = array_sum($backlogCounts);
                                        echo $backlogCounts['total'];
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <div class="row mx-0 py-2 align-items-center">
                                <p class="card-text col-sm-6 px-0 my-0">
                                    <b>Completed </b>
                                </p>
                                <div class="col-sm-3"></div>
                                <div class="col-sm-3">
                                    <span class="badge px-3">
                                        <?= $backlogCounts['completed_backlogs'] ?? 0 ?>
                                    </span>
                                </div>
                            </div>
                            <div class="row mx-0 py-2 align-items-center">
                                <p class="card-text col-sm-6 px-0 my-0">
                                    <b>Remaining </b>
                                </p>
                                <div class="col-sm-3"></div>
                                <div class="col-sm-3">
                                    <span class="badge px-3 ">
                                        <?= ($backlogCounts['total'] ?? 0) - ($backlogCounts['completed_backlogs'] ?? 0) ?>
                                    </span>
                                </div>
                            </div>
                            

                        <?php else: ?>
                            <p class="ox-card-empty">No backlog items found</p>
                        <?php endif; ?>
                    </div>
                
              </form>
            
        </div>

        <!-- User Stories Overview Card -->
        <div class="col-md-3">
            <div class="card bg-card-2 ">
                <div class="card-body ">
                    <h5 class="card-title text-start align-items-center d-flex"><i class="fas fa-book cls-cardHeaderIcon"></i> 
                    <div class="cls-cardHeaderTitle">
                     User stories <br>  overview</div></h5>
                    <?php if (!empty($userStoryStatusCounts)): ?>
                        <div class="row mx-0 py-2 align-items-center">
                            <p class="card-text col-sm-9 px-0 my-0">
                                <b>Total user stories </b>
                            </p>
                            <div class="col-sm-3">
                                <span class="badge px-3  ">
                                    <?= $userStoryStatusCounts['total_user_stories'] ?? 0 ?>
                                </span>
                            </div>
                        </div>
                        <div class="row mx-0 py-2 align-items-center ">
                            <p class="card-text col-sm-9 px-0 my-0">
                                <b>Completed </b>
                            </p>
                            <div class="col-sm-3">
                                <span class="badge px-3 ">
                                    <?= $userStoryStatusCounts['completed_stories'] ?? 0 ?>
                                </span>
                            </div>
                        </div>
                        <div class="row mx-0 py-2 align-items-center">
                            <p class="card-text col-sm-9 px-0 my-0">
                                <b>Remaining </b>
                            </p>
                            <div class="col-sm-3">
                                <span class="badge px-3 ">
                                    <?= $userStoryStatusCounts['remaining_stories'] ?? 0 ?>
                                </span>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="ox-card-empty">No user stories yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Current Sprint Card -->
        <div class="col-md-3">
            <form action="<?= base_url() ?>sprint/navsprintview" method="get" class="card bg-card-3 card-selector" id="currentSprint-submit" data-toggle="tooltip" title="view sprint details">
                <input type="text" name="sprint_id" value="<?= $currentSprintDetails['sprint_id'] ?? 0 ?>" hidden>
                <div class="card-body">
                    <h5 class="card-title text-start align-items-center mb-4 cls-currentsprint"><i class="fas fa-tachometer-alt fa-fade cls-cardHeaderIcon"></i>Current sprint</h5>
                    <?php if (!empty($currentSprintDetails)): ?>
                        <div class="row mx-0 py-2 align-items-center cls-currentsprintcontent">
                            <p class="card-text col-sm-8 px-0 my-0">
                                <b class="text-left">Sprint version  </b> 
                            </p>
                            <div class="col-sm-4 badge text-end px-0">
                                <?= $currentSprintDetails['sprint_version'] ?? 'N/A'; ?>
                            </div>
                        </div>
                        <div class="row mx-0 py-2 align-items-center">
                            <p class="card-text d-flex justify-items-center col-sm-7 px-0 my-0">
                                <b>Progress</b>
                            </p> 
                            <div class="col-sm-5 text-end px-0 badge">
                                <?= $currentSprintDetails['sprint_completed'].' %' ?? 'N/A'; ?>
                            </div>
                        </div>
                        <div class="row mx-0 py-2 align-items-center">
                            <p class="card-text d-flex justify-items-center col-sm-6 px-0 my-0">
                                <b>Start date </b> 
                            </p>
                            <div class="col-sm-6 text-end px-0 badge">
                                <?= $currentSprintDetails['start_date'] ?? 'N/A'; ?>
                            </div>
                        </div>
                        <div class="row mx-0 py-2 justify-items-center align-items-center">
                            <p class="card-text d-flex justify-items-left col-sm-6 px-0 my-0">
                                <b>End date</b>
                            </p>
                            <div class="col-sm-6 text-end px-0 badge">
                                <?= $currentSprintDetails['end_date'] ?? 'N/A'; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="ox-card-empty cls-currentsprintcontent">No sprint is currently active.</p>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Pending Tasks Card -->
        
        <div class="col-md-3">
            <div class="card bg-card-4 card-selector" >
                <div class="card-body " id="pendingTasksCard" p-sprint-id="<?= $currentSprintDetails['sprint_id'] ?? 0 ?>" p-product-id="<?= $data['productId'] ?? 0 ?>" data-toggle="tooltip" title="view pending tasks">
                    <h5 class="card-title text-start align-items-center d-flex">
                        <i class="fas fa-tasks cls-cardHeaderIcon"></i><div class="cls-cardHeaderTitle">
                        Pending tasks in current<br>  sprint</div>
                    </h5>
                    <?php if (!empty($pendingTask)): ?>
                        <ul class="list-group list-group-flush bg-transparent">
                            <?php
                            // Define priority labels and their corresponding classes
                            $priorityLabels = [
                                'HIGH' => ['label' => 'High Priority', 'class' => 'bg-danger'],
                                'MEDIUM' => ['label' => 'Medium', 'class' => 'bg-warning '],
                                'LOW' => ['label' => 'Low Priority', 'class' => 'bg-info ']
                            ];

                            // Display pending tasks grouped by priority
                            foreach ($pendingTask as $task) {
                                
                                $priority = $task['priority'] ?? '';
                                $pending_tasks = $task['pending_tasks'] ?? 0;
                                $label = $priorityLabels[$priority]['label'] ?? 'Unknown Priority';
                                $class = $priorityLabels[$priority]['class'] ?? 'bg-secondary';
                                if($priorityLabels[$priority]['class'] == 'bg-danger' ){
                                    $color = 'text-white';
                                } else{
                                    $color = 'text-dark';
                                }
                                echo "<li class='list-group-item border-0 d-flex justify-content-between align-items-center bg-transparent'>
                                        <b>{$label}</b>
                                        <span class='badge px-3'>{$pending_tasks}</span>
                                    </li>";
                            }
                            ?>
                        </ul>
                    <?php else: ?>
                        <p class="ox-card-empty">No pending tasks found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
          
    </div>
</div>

<div class="row mb-2" id="pendingTasksContainer" class="container mx-4">
    <div class="col-sm-8" id="pendingTasks"> </div>
    <div class="col-sm-4 mt-2" id="pendingTasksStatus">
        <h4 class="d-flex justify-content-center">Tasks breakdown</h4>
        <canvas id="pendingTasksChart"></canvas>
    </div>
</div>

<div class="container">
    <div class="row mb-3">
        <!-- Sprint Hours Chart -->
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-body"> 
                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title text-center">
                                    Sprint version - <?= $currentSprintDetails['sprint_version']  ?? 'N/A'; ?> sprint hours
                                </h5>
                           
                                
                            </div>
                        </div>
                    </div>
                    <canvas id="sprintHoursChart"></canvas>
                </div>
            </div>

            <!-- <div class="card h-100">
                <div class="card-body"> 
                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title text-center">
                                    <i class="fas fa-chart-line"></i> 
                                    Sprint Version - <?= $currentSprintDetails['sprint_version']  ?? 'N/A'; ?> Burndown Chart
                                </h5>
                           
                                <p class="text-dark"><strong>Required:</strong> <span id="requiredRate"></span> hrs/day</p>
                            </div>
                        </div>
                    </div>
                    <canvas id="burndownChart"></canvas>
                </div>
            </div> -->
        </div>
        <!-- Overall Backlog Progress Chart -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-center mb-4">Overall product backlog progress</h5>
                    <canvas class="mt-4" id="backlogProgressChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4 g-4">
        <!-- Velocity Sprint Chart -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title text-center">Velocity by sprints </h5>
                                <?php 
                                    $velocityData=json_decode($getTotalTaskHoursPerSprint, true);
                                    $totalHours = array_sum($velocityData);
                                    $totalSprints = count($velocityData) == 0 ? 1 : count($velocityData);
                                    $overallVelocity = $totalHours / $totalSprints;
                                    $overallVelocity = round($overallVelocity, 2);
                                ?>
                                <p class="text-dark"><strong>Team velocity :</strong> <span> <?= $overallVelocity ?> </span> hrs</p>
                            </div>
                        </div>
                    </div>
                    <canvas id="velocitySprintChart" class="mt-2"></canvas>
                </div>
            </div>
        </div>
        <!-- Story Completion Chart -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title text-center">Sprint version - <?= $currentSprintDetails['sprint_version'] ?? 'N/A'; ?> stories spent hours</h5>
                            </div>
                        </div>
                    </div>
                    <canvas id="storyCompletionChart" class="mt-2"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Current and Upcoming Sprints Table -->
    <h4 class="mb-3">Sprints</h4>
    <?php if (!empty($sprintDetails)): ?>
    <div class="table-responsive">
        <table class="table table-borderless custom-table">
            <thead class="header_color">
                <tr>
                    <th>Sprint version</th>
                    <th>Sprint name</th>
                    <th>Start date</th>
                    <th>End date</th>
                    <th>Completed percentage</th>
                    <th>Sprint duration</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    // Filter and sort the sprint details
                    $ongoing = [];
                    $upcoming = [];
                    $overdue = [];

                    foreach ($sprintDetails as $sprint) {
                        if ($sprint['sprint_status'] == 'Sprint Running' || $sprint['sprint_status'] == 'Sprint Review' || $sprint['sprint_status'] == 'Sprint Retrospective') {
                            $ongoing[] = $sprint;
                        } elseif ($sprint['sprint_status'] == 'Sprint Planned') {
                            $upcoming[] = $sprint;
                        } elseif ($sprint['sprint_status'] == 'Sprint Completed' && $sprint['sprint_completed'] < 100) {
                            $overdue[] = $sprint;
                        }
                    }

                    $sprintDetailsSorted = array_merge($ongoing, $upcoming, $overdue);
                ?>
                <script>
                    let data = <?php echo json_encode($sprintDetailsSorted); ?>;

                </script>
                <?php foreach ($sprintDetailsSorted as $sprint) : ?>
                    <tr>
                        <td><?php echo $sprint['sprint_version'] ?? 'N/A'; ?></td>
                        <td><?php echo $sprint['sprint_name'] ?? 'N/A'; ?></td>
                        <td><?php echo isset($sprint['start_date']) ? date("d, M Y", strtotime($sprint['start_date'])) : 'N/A'; ?></td>
                        <td><?php echo isset($sprint['end_date']) ? date("d, M Y", strtotime($sprint['end_date'])) : 'N/A'; ?></td>
                        <td><?php echo $sprint['sprint_completed'] .' % ' ?? 'N/A'; ?></td>
                        <td><?php echo $sprint['sprint_duration'] ?? 'N/A'; ?></td>
                        <td>
                            <?php
                            $badgeClass = 'secondary';
                            $badgeText = 'Unknown';

                            if ($sprint['sprint_status'] == 'Sprint Running' )  {
                                $badgeClass = 'text-primary';
                                $badgeText = 'ongoing';
                            } elseif ($sprint['sprint_status'] == 'Sprint Planned') {
                                $badgeClass = 'text-warning';
                                $badgeText = 'upcoming';
                            } elseif ($sprint['sprint_status'] == 'Sprint Completed') {
                                if ($sprint['sprint_completed'] < 100) {
                                    $badgeClass = 'text-danger';
                                    $badgeText = 'overdue';
                                } else {
                                    $badgeClass = 'primary';
                                    $badgeText = 'completed';
                                }
                            } elseif ($sprint['sprint_status'] == 'Sprint Review') {
                                $badgeClass = 'text-primary';
                                $badgeText = 'In review';
                            } elseif ($sprint['sprint_status'] == 'Sprint Retrospective') {
                                $badgeClass = 'text-primary';
                                $badgeText = 'In retrospective';
                            }
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>">
                                <?php echo $badgeText; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div id="paginationControls" class="d-flex justify-content-between align-items-center mt-3">
            <button id="prevPage" class="btn-outline button-secondary">Prev</button>
            <span id="pageInfo" class="mx-3"></span>
            <button id="nextPage" class="btn-outline button-secondary">Next</button>
        </div>
    </div>
    <?php else: ?>
        <p class="text-center no-sprints">No sprint details available</p>
    <?php endif; ?>
</div>


<?php
        
        $backlogCountsJson = json_encode($backlogCounts);
        $currentSprintDetails = json_encode($currentSprintDetails ?? []);
        $sprintHours = $data['sprintHours'] ?? [];
        // $burndownChartData = $burndownChartData['chartData'];   
        // $estimatedHours =  json_encode($estimatedHours ?? []);
        $userStoryCompletionHours =$data['userStoryCompletionHours'] ?? json_encode([]);
        $sprintCounts = count($sprintDetails);
        
?>
<script>
    var baseUrl = '<?= base_url() ?>';
    var userId = <?php echo json_encode($userId); ?>;
    const backlogCounts = <?php echo $backlogCountsJson; ?>;
    const sprintDetails = <?php echo $currentSprintDetails; ?>;
    const velocityChartData = <?php echo $getTotalTaskHoursPerSprint; ?>;
    const sprintHoursPerDay = <?php echo $sprintHours; ?>;
    const userStoryCompletionHours = <?php echo $userStoryCompletionHours ?>;
    const sprintCounts = <?php echo $sprintCounts ?>;

    // Module Name 
    const productDashboard = "productDashboard";
</script>

 


