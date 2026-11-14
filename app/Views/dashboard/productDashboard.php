<?php
/**
 * Product dashboard
 *
 * Displays the product and sprint statistics:
 * - Backlog items, user stories, current sprint and pending tasks (stat cards)
 * - Pending task list and breakdown (opened from the pending tasks card)
 * - Sprint hours, backlog progress, velocity, story spent hours and burnup charts
 * - Current, upcoming and overdue sprints
 */

// Extract data from the $data array
$userId  = session()->get('employee_id');
$currentSprintDetails = $data['currentSprintDetails'] ?? [];
$sprintDetails = $data['sprintDetails'] ?? [];
$userStoryStatusCounts = $data['userStoryStatusCounts'][0] ?? [];
$backlogCounts = $data['backlogStatusCounts'] ?? [];
$getTotalTaskHoursPerSprint = $data['totalTaskHoursPerSprint'] ?? 0;
$pendingTask = $data['pendingTaskStatusCounts'] ?? [];
$estimatedHours = $burndownChartData['estimatedHours'] ?? [];
$runningSprints = $data['runningSprints'] ?? [];
$sprintVersion = $currentSprintDetails['sprint_version'] ?? 'N/A';

// Stat card figures
if (! empty($backlogCounts)) {
    $backlogCounts['total'] = array_sum($backlogCounts);
}
$backlogTotal = (int) ($backlogCounts['total'] ?? 0);
$backlogDone = (int) ($backlogCounts['completed_backlogs'] ?? 0);
$backlogPct = $backlogTotal > 0 ? (int) round($backlogDone / $backlogTotal * 100) : 0;

$storyTotal = (int) ($userStoryStatusCounts['total_user_stories'] ?? 0);
$storyDone = (int) ($userStoryStatusCounts['completed_stories'] ?? 0);
$storyPct = $storyTotal > 0 ? (int) round($storyDone / $storyTotal * 100) : 0;

$sprintPct = max(0, min(100, (int) ($currentSprintDetails['sprint_completed'] ?? 0)));
$fmtDate = static fn ($d) => ! empty($d) ? date('d M Y', strtotime($d)) : 'N/A';

$priorityLabels = [
    'HIGH'   => ['label' => 'High', 'tone' => 'red'],
    'MEDIUM' => ['label' => 'Medium', 'tone' => 'amber'],
    'LOW'    => ['label' => 'Low', 'tone' => 'green'],
];
$pendingTotal = array_sum(array_map(static fn ($t) => (int) ($t['pending_tasks'] ?? 0), $pendingTask));
?>

<?php if (count($runningSprints) > 1): ?>
<form id="sprintForm" action="<?= base_url('dashboard/showProductDashboard') ?>" method="post" class="ox-pd-toolbar">
    <label for="sprint" class="ox-pd-toolbar-label"><i class="icon-zap" aria-hidden="true"></i> Running sprint</label>
    <select name="sprint_version" id="sprint" class="form-select select-sprint" onchange="document.getElementById('sprintForm').submit()">
        <?php foreach ($runningSprints as $sprint): ?>
            <option value="<?= $sprint['sprint_id'] ?>" <?= isset($currentSprintDetails['sprint_version']) && $currentSprintDetails['sprint_version'] == $sprint['sprint_version'] ? 'selected' : '' ?>>
                <?= "Sprint version: " . $sprint['sprint_version'] . " - " . ucfirst($sprint['sprint_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <input type="hidden" name="product-id" value="<?= $data['productId'] ?? 0 ?>">
</form>
<?php endif; ?>

<div class="ox-stat-grid ox-pd-kpis">

    <!-- Backlog items -->
    <form action="<?= base_url() ?>backlog/backlogitems" method="get" class="ox-stat-card ox-pd-link" id="backlog-submit" title="View backlog items">
        <input type="hidden" name="pid" value="<?= $data['productId'] ?? 0 ?>">
        <header class="ox-stat-head">
            <span class="ox-stat-icon ox-tone-green"><i class="icon-layers" aria-hidden="true"></i></span>
            <h2>Product backlog items</h2>
            <i class="icon-arrow-up-right ox-pd-go" aria-hidden="true"></i>
        </header>
        <?php if (! empty($backlogCounts)): ?>
            <div class="ox-stat-metrics">
                <div class="ox-metric">
                    <span class="ox-metric-value"><?= $backlogTotal ?></span>
                    <span class="ox-metric-label">Total</span>
                </div>
                <div class="ox-metric">
                    <span class="ox-metric-value"><?= $backlogDone ?></span>
                    <span class="ox-metric-label"><span class="ox-sdot ox-sdot-green"></span>Completed</span>
                </div>
                <div class="ox-metric">
                    <span class="ox-metric-value"><?= $backlogTotal - $backlogDone ?></span>
                    <span class="ox-metric-label">Remaining</span>
                </div>
            </div>
            <div class="ox-pd-progress">
                <div class="ox-progress-track" role="progressbar" aria-label="Backlog items completed" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $backlogPct ?>">
                    <span style="width: <?= $backlogPct ?>%"></span>
                </div>
                <span class="ox-pd-progress-value"><?= $backlogPct ?>%</span>
            </div>
        <?php else: ?>
            <p class="ox-card-empty">No backlog items found</p>
        <?php endif; ?>
    </form>

    <!-- User stories -->
    <section class="ox-stat-card">
        <header class="ox-stat-head">
            <span class="ox-stat-icon ox-tone-blue"><i class="icon-book-open" aria-hidden="true"></i></span>
            <h2>User stories</h2>
        </header>
        <?php if (! empty($userStoryStatusCounts)): ?>
            <div class="ox-stat-metrics">
                <div class="ox-metric">
                    <span class="ox-metric-value"><?= $storyTotal ?></span>
                    <span class="ox-metric-label">Total</span>
                </div>
                <div class="ox-metric">
                    <span class="ox-metric-value"><?= $storyDone ?></span>
                    <span class="ox-metric-label"><span class="ox-sdot ox-sdot-green"></span>Completed</span>
                </div>
                <div class="ox-metric">
                    <span class="ox-metric-value"><?= (int) ($userStoryStatusCounts['remaining_stories'] ?? 0) ?></span>
                    <span class="ox-metric-label">Remaining</span>
                </div>
            </div>
            <div class="ox-pd-progress">
                <div class="ox-progress-track" role="progressbar" aria-label="User stories completed" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $storyPct ?>">
                    <span style="width: <?= $storyPct ?>%"></span>
                </div>
                <span class="ox-pd-progress-value"><?= $storyPct ?>%</span>
            </div>
        <?php else: ?>
            <p class="ox-card-empty">No user stories yet</p>
        <?php endif; ?>
    </section>

    <!-- Current sprint -->
    <form action="<?= base_url() ?>sprint/navsprintview" method="get" class="ox-stat-card ox-pd-link" id="currentSprint-submit" title="View sprint details">
        <input type="text" name="sprint_id" value="<?= $currentSprintDetails['sprint_id'] ?? 0 ?>" hidden>
        <header class="ox-stat-head">
            <span class="ox-stat-icon ox-tone-violet"><i class="icon-zap" aria-hidden="true"></i></span>
            <h2>Current sprint</h2>
            <i class="icon-arrow-up-right ox-pd-go" aria-hidden="true"></i>
        </header>
        <?php if (! empty($currentSprintDetails)): ?>
            <div class="ox-stat-metrics">
                <div class="ox-metric">
                    <span class="ox-metric-value"><?= esc($sprintVersion) ?></span>
                    <span class="ox-metric-label">Version</span>
                </div>
                <div class="ox-metric ox-pd-dates">
                    <span class="ox-pd-date"><i class="icon-calendar" aria-hidden="true"></i><?= esc($fmtDate($currentSprintDetails['start_date'] ?? null)) ?></span>
                    <span class="ox-pd-date"><i class="icon-flag" aria-hidden="true"></i><?= esc($fmtDate($currentSprintDetails['end_date'] ?? null)) ?></span>
                    <span class="ox-metric-label">Start · End</span>
                </div>
            </div>
            <div class="ox-pd-progress">
                <div class="ox-progress-track" role="progressbar" aria-label="Sprint progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $sprintPct ?>">
                    <span style="width: <?= $sprintPct ?>%"></span>
                </div>
                <span class="ox-pd-progress-value"><?= esc((string) ($currentSprintDetails['sprint_completed'] ?? 0)) ?>%</span>
            </div>
        <?php else: ?>
            <p class="ox-card-empty">No sprint is currently active.</p>
        <?php endif; ?>
    </form>

    <!-- Pending tasks in the current sprint (opens the task list below) -->
    <section class="ox-stat-card ox-pd-link" id="pendingTasksCard" p-sprint-id="<?= $currentSprintDetails['sprint_id'] ?? 0 ?>" p-product-id="<?= $data['productId'] ?? 0 ?>" title="View pending tasks">
        <header class="ox-stat-head">
            <span class="ox-stat-icon ox-tone-amber"><i class="icon-check-square" aria-hidden="true"></i></span>
            <h2>Pending tasks <span class="ox-badge-count"><?= $pendingTotal ?></span></h2>
            <i class="icon-chevron-right ox-pd-go" aria-hidden="true"></i>
        </header>
        <?php if (! empty($pendingTask)): ?>
            <div class="ox-stat-metrics">
                <?php foreach ($pendingTask as $task): ?>
                    <?php $p = $priorityLabels[$task['priority'] ?? ''] ?? ['label' => 'Other', 'tone' => '']; ?>
                    <div class="ox-metric">
                        <span class="ox-metric-value"><?= (int) ($task['pending_tasks'] ?? 0) ?></span>
                        <span class="ox-metric-label"><?php if ($p['tone']): ?><span class="ox-sdot ox-sdot-<?= $p['tone'] ?>"></span><?php endif; ?><?= $p['label'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($pendingTotal > 0): ?>
                <div class="ox-stack-bar" aria-hidden="true">
                    <?php foreach ($pendingTask as $task): ?>
                        <span class="is-<?= strtolower($task['priority'] ?? 'other') ?>" style="flex: <?= (int) ($task['pending_tasks'] ?? 0) ?>"></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p class="ox-card-empty">No pending tasks found.</p>
        <?php endif; ?>
    </section>
</div>

<!-- Pending task list, filled by productDashboard.js -->
<section class="ox-panel ox-pd-pending" id="pendingTasksContainer" aria-live="polite">
    <div class="ox-pd-pending-grid">
        <div id="pendingTasks"></div>
        <div id="pendingTasksStatus">
            <h4 class="ox-pd-subtitle">Tasks breakdown</h4>
            <canvas id="pendingTasksChart"></canvas>
        </div>
    </div>
</section>

<div class="ox-pd-grid">
    <!-- Sprint hours -->
    <section class="ox-panel ox-pd-span-8">
        <header class="ox-panel-head">
            <h2 class="ox-panel-title"><i class="icon-activity" aria-hidden="true"></i> Sprint hours</h2>
            <span class="ox-pd-chip">Sprint version <?= esc($sprintVersion) ?></span>
        </header>
        <div class="ox-pd-chart"><canvas id="sprintHoursChart"></canvas></div>
    </section>

    <!-- Overall backlog progress -->
    <section class="ox-panel ox-pd-span-4">
        <header class="ox-panel-head">
            <h2 class="ox-panel-title"><i class="icon-pie-chart" aria-hidden="true"></i> Backlog progress</h2>
        </header>
        <div class="ox-pd-chart"><canvas id="backlogProgressChart"></canvas></div>
    </section>

    <!-- Velocity by sprints -->
    <section class="ox-panel ox-pd-span-6">
        <header class="ox-panel-head">
            <?php
                $velocityData = json_decode($getTotalTaskHoursPerSprint, true);
                $totalHours = array_sum($velocityData);
                $totalSprints = count($velocityData) == 0 ? 1 : count($velocityData);
                $overallVelocity = round($totalHours / $totalSprints, 2);
            ?>
            <h2 class="ox-panel-title"><i class="icon-trending-up" aria-hidden="true"></i> Velocity by sprints</h2>
            <p class="ox-pd-stat">Team velocity <strong><?= $overallVelocity ?></strong> hrs</p>
        </header>
        <div class="ox-pd-chart"><canvas id="velocitySprintChart"></canvas></div>
    </section>

    <!-- Story spent hours -->
    <section class="ox-panel ox-pd-span-6">
        <header class="ox-panel-head">
            <h2 class="ox-panel-title"><i class="icon-bar-chart-2" aria-hidden="true"></i> Stories spent hours</h2>
            <span class="ox-pd-chip">Sprint version <?= esc($sprintVersion) ?></span>
        </header>
        <div class="ox-pd-chart"><canvas id="storyCompletionChart"></canvas></div>
    </section>

    <?php $burnup = $data['burnup'] ?? null; ?>
    <?php if ($burnup && $burnup['labels'] !== []): ?>
    <!-- Sprint burnup: cumulative hours logged on sprint tasks against the sprint's estimated scope -->
    <section class="ox-panel ox-pd-span-12">
        <header class="ox-panel-head">
            <h2 class="ox-panel-title"><i class="icon-target" aria-hidden="true"></i> Sprint burnup <span class="ox-pd-chip">Sprint version <?= esc($sprintVersion) ?></span></h2>
            <p class="ox-pd-stat">Logged <strong><?= esc((string) $burnup['total']) ?> h</strong> of <?= esc((string) $burnup['scope']) ?> h estimated</p>
        </header>
        <div class="ox-pd-chart">
            <div class="ox-burnup-wrap"><canvas id="sprintBurnupChart" role="img" aria-label="Sprint burnup: cumulative hours logged against the estimated scope"></canvas></div>
            <details class="ox-pd-details">
                <summary>View as table</summary>
                <div class="table-responsive mt-2">
                    <table class="table table-sm">
                        <thead><tr><th scope="col">Day</th><th scope="col" class="text-end">Logged (cumulative h)</th><th scope="col" class="text-end">Ideal (h)</th><th scope="col" class="text-end">Scope (h)</th></tr></thead>
                        <tbody>
                        <?php foreach ($burnup['labels'] as $i => $label): ?>
                            <tr><td><?= esc($label) ?></td><td class="text-end"><?= $burnup['logged'][$i] === null ? '–' : esc((string) $burnup['logged'][$i]) ?></td><td class="text-end"><?= esc((string) $burnup['ideal'][$i]) ?></td><td class="text-end"><?= esc((string) $burnup['scope']) ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </details>
        </div>
    </section>
    <?php endif; ?>
</div>
<?php if ($burnup && $burnup['labels'] !== []): ?>
<script type="application/json" id="sprintBurnupData"><?= json_encode($burnup, JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?php endif; ?>

<!-- Current, upcoming and overdue sprints -->
<section class="ox-panel ox-pd-sprints">
    <header class="ox-panel-head">
        <h2 class="ox-panel-title"><i class="icon-list" aria-hidden="true"></i> Sprints</h2>
    </header>
    <?php if (! empty($sprintDetails)): ?>
    <div class="table-responsive">
        <table class="table ox-pd-table" id="sprintTable">
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
                        <td><?= esc($sprint['sprint_version'] ?? 'N/A') ?></td>
                        <td><?= esc($sprint['sprint_name'] ?? 'N/A') ?></td>
                        <td><?php echo isset($sprint['start_date']) ? date("d, M Y", strtotime($sprint['start_date'])) : 'N/A'; ?></td>
                        <td><?php echo isset($sprint['end_date']) ? date("d, M Y", strtotime($sprint['end_date'])) : 'N/A'; ?></td>
                        <td><?php echo $sprint['sprint_completed'] .' % ' ?? 'N/A'; ?></td>
                        <td><?= esc($sprint['sprint_duration'] ?? 'N/A') ?></td>
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
    </div>
    <div id="paginationControls" class="ox-pd-pager">
        <button id="prevPage" class="btn-outline button-secondary">Prev</button>
        <span id="pageInfo"></span>
        <button id="nextPage" class="btn-outline button-secondary">Next</button>
    </div>
    <?php else: ?>
        <p class="text-center no-sprints">No sprint details available</p>
    <?php endif; ?>
</section>


<?php
        $backlogCountsJson = json_encode($backlogCounts);
        $currentSprintDetails = json_encode($currentSprintDetails ?? []);
        $sprintHours = $data['sprintHours'] ?? [];
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
