<?php
// for count the delayed products and on-track products
$ontrackProducts = 0;
$delayedProducts = 0;
$delayedProductsId = [];
foreach ($data['on_track_and_delay'] as $value) {
    if ($value['delay'] > 0) {
        $delayedProducts += 1;
        $delayedProductsId[] = $value['r_product_id'];
    } else {
        $ontrackProducts += 1;
    }
}

$projectsWithSprints = [];
$activeSprintsCount = 0;
$pendingTask = 0;
$pendingTaskWithCompletion = [];
if (is_array($data['sprints'])) {
    foreach ($data['sprints'] as $value) {
        // check the variables is set or not
        if (isset($value['r_product_id'], $value['sprint_id'], $value['sprint_version'])) {
            // check projectsWithSprints variable has product_id with sprint_id as an key
            if (! isset($projectsWithSprints[$value['r_product_id']][$value['sprint_id']])) {
                $projectsWithSprints[$value['r_product_id']][$value['sprint_id']] = [
                    $value['sprint_version'],
                    $value['delay'],
                    $value['end_date'],
                ];
                $activeSprintsCount += 1;
            }
        }
        // check the task is set, still pending and not deleted
        if (
            isset($value['task_status'], $value['r_task_id'], $value['sprint_task_deleted'], $value['task_deleted'])
            && in_array($value['task_status'], $data['pending_task_status'])
            && $value['sprint_task_deleted'] == 'N'
            && $value['task_deleted'] == 'N'
        ) {
            $pendingTask += 1;
            $pendingTaskWithCompletion[$value['r_task_id']] = $value['completed_percentage'];
        }
    }
}
$completionRate = ! empty($pendingTaskWithCompletion)
    ? (int) round(array_sum($pendingTaskWithCompletion) / count($pendingTaskWithCompletion))
    : 0;

// for get backlog items priority
$backlogPriority = array_column($data['backlogs'], 'pblsCount', 'priority');
$backlogTotal = array_sum($backlogPriority);
?>
<div class="ox-dashboard">
    <div class="ox-stat-grid">
        <section class="ox-stat-card">
            <header class="ox-stat-head">
                <span class="ox-stat-icon ox-tone-green"><i class="icon-package" aria-hidden="true"></i></span>
                <h2>Product portfolio</h2>
            </header>
            <div class="ox-stat-metrics">
                <button type="button" class="ox-metric" onclick="orderByRunningSprint('active')" title="Show the active products">
                    <span class="ox-metric-value"><?= count($data['on_track_and_delay']) ?></span>
                    <span class="ox-metric-label">Active</span>
                </button>
                <button type="button" class="ox-metric" onclick="orderByRunningSprint('onTrack')" title="Show the on-track products">
                    <span class="ox-metric-value"><?= $ontrackProducts ?></span>
                    <span class="ox-metric-label"><span class="ox-sdot ox-sdot-green"></span>On track</span>
                </button>
                <button type="button" class="ox-metric" onclick="orderByRunningSprint('delayed')" title="Show the delayed products">
                    <span class="ox-metric-value <?= $delayedProducts > 0 ? 'is-danger' : '' ?>"><?= $delayedProducts ?></span>
                    <span class="ox-metric-label"><span class="ox-sdot ox-sdot-red"></span>Delayed</span>
                </button>
            </div>
        </section>

        <section class="ox-stat-card">
            <header class="ox-stat-head">
                <span class="ox-stat-icon ox-tone-blue"><i class="icon-zap" aria-hidden="true"></i></span>
                <h2>Sprint performance</h2>
            </header>
            <div class="ox-stat-metrics">
                <div class="ox-metric">
                    <span class="ox-metric-value"><?= $activeSprintsCount ?></span>
                    <span class="ox-metric-label">Active sprints</span>
                </div>
                <div class="ox-metric">
                    <span class="ox-metric-value"><?= $pendingTask ?></span>
                    <span class="ox-metric-label">Pending tasks</span>
                </div>
                <div class="ox-metric">
                    <span class="ox-metric-value"><?= $completionRate ?><small>%</small></span>
                    <span class="ox-metric-label">Completion</span>
                </div>
            </div>
            <div class="ox-progress-track" role="progressbar" aria-label="Completion rate" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $completionRate ?>">
                <span style="width: <?= $completionRate ?>%"></span>
            </div>
        </section>

        <section class="ox-stat-card">
            <header class="ox-stat-head">
                <span class="ox-stat-icon ox-tone-amber"><i class="icon-layers" aria-hidden="true"></i></span>
                <h2>Global backlog <span class="ox-badge-count"><?= $backlogTotal ?></span></h2>
            </header>
            <div class="ox-stat-metrics">
                <div class="ox-metric">
                    <span class="ox-metric-value"><?= $backlogPriority['H'] ?? 0 ?></span>
                    <span class="ox-metric-label"><span class="ox-sdot ox-sdot-red"></span>High</span>
                </div>
                <div class="ox-metric">
                    <span class="ox-metric-value"><?= $backlogPriority['M'] ?? 0 ?></span>
                    <span class="ox-metric-label"><span class="ox-sdot ox-sdot-amber"></span>Medium</span>
                </div>
                <div class="ox-metric">
                    <span class="ox-metric-value"><?= $backlogPriority['L'] ?? 0 ?></span>
                    <span class="ox-metric-label"><span class="ox-sdot ox-sdot-green"></span>Low</span>
                </div>
            </div>
            <?php if ($backlogTotal > 0): ?>
                <div class="ox-stack-bar" aria-hidden="true">
                    <span class="is-high" style="flex: <?= (int) ($backlogPriority['H'] ?? 0) ?>"></span>
                    <span class="is-medium" style="flex: <?= (int) ($backlogPriority['M'] ?? 0) ?>"></span>
                    <span class="is-low" style="flex: <?= (int) ($backlogPriority['L'] ?? 0) ?>"></span>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <section class="ox-panel">
        <header class="ox-panel-head">
            <button type="button" class="ox-panel-title" onclick="orderByRunningSprint()" title="Show all products">
                Products <span class="ox-badge-count"><?= count($data['products']) ?></span>
            </button>
            <div class="ox-search">
                <i class="icon-search" aria-hidden="true"></i>
                <input type="search" class="form-control" id="searchInput" placeholder="Search products" aria-label="Search products"
                    onkeyup="filterAndReorderProducts()" onsearch="filterAndReorderProducts()">
            </div>
        </header>

        <ul class="ox-product-list" id="productList">
            <?php foreach ($data['products'] as $value): ?>
                <?php
                $productName = trim(ucfirst(strtolower($value['product_name'])));
                $isDelayed = in_array($value['product_id'], $delayedProductsId);
                ?>
                <li class="product ox-product-row" data-name="<?= esc($value['product_name'], 'attr') ?>">
                    <div class="ox-product-name cls-active-products">
                        <span class="ox-avatar-square"><?= esc(strtoupper(substr($productName, 0, 1))) ?></span>
                        <span class="ox-product-title"><?= esc($productName) ?></span>
                        <?php if ($isDelayed): ?>
                            <span class="ox-tag ox-tag-red">Delayed</span>
                        <?php endif; ?>
                    </div>
                    <div class="cls-active-sprints">
                        <div class="cls-sprints">
                            <?php if (! empty($projectsWithSprints[$value['product_id']])): ?>
                                <?php foreach (array_slice($projectsWithSprints[$value['product_id']], 0, 5, true) as $sprint_id => $sprintDetails): ?>
                                    <form action="<?= base_url() ?>sprint/navsprintview" method="get">
                                        <input type="hidden" name="sprint_id" value="<?= esc($sprint_id, 'attr') ?>">
                                        <button type="submit" class="badge ox-sprint-chip <?= $sprintDetails[1] == 1 ? 'cls-delayed-sprint is-delayed' : '' ?>"
                                            name="<?= $sprintDetails[1] == 1 ? 'delayed' : 'onTrack' ?>"
                                            title="Open sprint <?= esc($sprintDetails[0], 'attr') ?>">
                                            <span class="ox-sprint-chip-title"><i class="icon-zap" aria-hidden="true"></i> Sprint <?= esc($sprintDetails[0]) ?></span>
                                            <span class="ox-sprint-chip-meta">Ends <?= format_date($sprintDetails[2]) ?></span>
                                        </button>
                                    </form>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <form action="<?= base_url() ?>sprint/sprintlist">
                                    <button class="badge ox-sprint-chip is-idle" name="No Running Sprint" title="Open the sprint list">
                                        No running sprint
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="ox-product-action">
                        <a class="btn btn-sm ox-btn-ghost view" href="<?= ASSERT_PATH ?>dashboard/showProductDashboard/<?= (int) $value['product_id'] ?>"
                            title="Open the <?= esc($productName, 'attr') ?> dashboard">
                            Open <i class="icon-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="empty-content ox-empty" id="empty" style="display: none;">
            <i class="icon-search" aria-hidden="true"></i>
            <h4>No products found</h4>
            <p>Try a different search or filter.</p>
        </div>
    </section>
</div>

<script>
    const dashboard = "dashboard";
</script>
