<?php
$groups = $data['groups'];
$sections = [
    'overdue' => ['label' => 'Overdue', 'icon' => 'alert-circle', 'tone' => 'red'],
    'week'    => ['label' => 'Due in the next 7 days', 'icon' => 'clock', 'tone' => 'amber'],
    'later'   => ['label' => 'Later or no due date', 'icon' => 'inbox', 'tone' => 'green'],
    'done'    => ['label' => 'Done in the last 30 days', 'icon' => 'check-circle', 'tone' => 'green'],
];
$openCount = count($groups['overdue']) + count($groups['week']) + count($groups['later']);
$priorityLabel = ['H' => 'High', 'M' => 'Medium', 'L' => 'Low'];
?>
<div class="ox-mywork" id="myWork">
    <div class="ox-kpis">
        <div class="ox-kpi">
            <span class="ox-kpi-value"><?= $openCount ?></span>
            <span class="ox-kpi-label">Open tasks</span>
        </div>
        <div class="ox-kpi">
            <span class="ox-kpi-value <?= $groups['overdue'] ? 'is-danger' : '' ?>"><?= count($groups['overdue']) ?></span>
            <span class="ox-kpi-label">Overdue</span>
        </div>
        <div class="ox-kpi">
            <span class="ox-kpi-value"><?= count($groups['week']) ?></span>
            <span class="ox-kpi-label">Due this week</span>
        </div>
        <div class="ox-kpi">
            <span class="ox-kpi-value"><?= count($groups['done']) ?></span>
            <span class="ox-kpi-label">Done in 30 days</span>
        </div>
    </div>

    <div class="ox-pagebar">
        <div class="ox-segmented" role="radiogroup" aria-label="Show tasks">
            <input type="radio" name="myWorkView" id="myWorkOpen" value="open" checked>
            <label for="myWorkOpen">Open</label>
            <input type="radio" name="myWorkView" id="myWorkDone" value="done">
            <label for="myWorkDone">Done</label>
        </div>
        <div class="ox-pagebar-actions">
            <?php if (count($data['products']) > 1): ?>
                <select class="form-select ox-mywork-product" id="myWorkProduct" aria-label="Filter by product">
                    <option value="">All products</option>
                    <?php foreach ($data['products'] as $product): ?>
                        <option value="<?= esc($product, 'attr') ?>"><?= esc($product) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <div class="ox-search">
                <i class="icon-search" aria-hidden="true"></i>
                <input type="search" id="myWorkSearch" class="form-control" placeholder="Filter my tasks" aria-label="Filter my tasks">
            </div>
        </div>
    </div>

    <?php foreach ($sections as $key => $section): ?>
        <section class="ox-panel ox-mywork-group" data-group="<?= $key ?>" data-view="<?= $key === 'done' ? 'done' : 'open' ?>"
                 <?= $groups[$key] === [] ? 'hidden' : '' ?>>
            <header class="ox-panel-head">
                <h2 class="ox-panel-title">
                    <span class="ox-stat-icon ox-tone-<?= $section['tone'] ?>"><i class="icon-<?= $section['icon'] ?>" aria-hidden="true"></i></span>
                    <?= esc($section['label']) ?>
                    <span class="ox-badge-count" data-count><?= count($groups[$key]) ?></span>
                </h2>
            </header>
            <ul class="ox-task-list">
                <?php foreach ($groups[$key] as $task): ?>
                    <?php
                    $title = trim((string) $task['task_title']);
                    $story = trim(strip_tags((string) $task['i_want']));
                    $sprint = $task['sprint_id'] ? (trim((string) $task['sprint_name']) ?: 'Sprint ' . $task['sprint_version']) : '';
                    $haystack = mb_strtolower($title . ' ' . $story . ' ' . $task['backlog_item_name'] . ' ' . $task['product_name'] . ' ' . $sprint);
                    ?>
                    <li class="ox-task-row" data-product="<?= esc($task['product_name'], 'attr') ?>" data-search="<?= esc($haystack, 'attr') ?>">
                        <span class="priority-<?= esc($task['priority'], 'attr') ?>" title="<?= esc($priorityLabel[$task['priority']] ?? '', 'attr') ?> priority"><?= esc($task['priority']) ?></span>
                        <a class="ox-task-main" href="<?= ASSERT_PATH . esc($task['task_url'], 'attr') ?>">
                            <span class="ox-task-title"><?= esc($title) ?></span>
                            <span class="ox-task-path">
                                <?= esc($task['product_name']) ?>
                                <i class="icon-chevron-right" aria-hidden="true"></i>
                                <?= esc($task['backlog_item_name']) ?>
                                <?php if ($story !== ''): ?>
                                    <i class="icon-chevron-right" aria-hidden="true"></i>
                                    <?= esc(mb_strimwidth($story, 0, 70, '…')) ?>
                                <?php endif; ?>
                            </span>
                        </a>
                        <span class="ox-task-meta">
                            <span class="ox-status-pill"><?= esc($task['status_name'] ?? '—') ?></span>
                            <?php if ($sprint !== ''): ?>
                                <a class="ox-tag ox-tag-gray" href="<?= ASSERT_PATH ?>sprint/navsprintview?sprint_id=<?= (int) $task['sprint_id'] ?>"><?= esc($sprint) ?></a>
                            <?php endif; ?>
                            <?php if ($task['due']): ?>
                                <span class="ox-task-due <?= $key === 'overdue' ? 'is-overdue' : '' ?>" title="Due date">
                                    <i class="icon-calendar" aria-hidden="true"></i><?= esc(date('d M', strtotime($task['due']))) ?>
                                </span>
                            <?php endif; ?>
                            <span class="ox-task-progress" title="<?= (int) $task['completed_percentage'] ?>% complete">
                                <span style="width: <?= max(0, min(100, (int) $task['completed_percentage'])) ?>%"></span>
                            </span>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endforeach; ?>

    <div class="ox-panel ox-empty ox-mywork-empty" id="myWorkEmpty" hidden>
        <i class="icon-check-square" aria-hidden="true"></i>
        <h4 data-empty-title>Nothing assigned to you</h4>
        <p data-empty-text>When someone assigns you a task, it shows up here.</p>
        <button type="button" class="btn btn-secondary btn-sm mt-3" id="myWorkClear" hidden>Clear filters</button>
    </div>
</div>
