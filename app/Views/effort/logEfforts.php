<?php
/**
 * logEfforts.php
 *
 * @category   View
 * @purpose    The signed-in user's spent time grouped by day, plus the "Log effort" modal.
 */
$fmtHours = static function ($hours): string {
    $minutes = (int) round((float) $hours * 60);

    return intdiv($minutes, 60) . ':' . str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT);
};
$masters = $data['masters'];
$userId = (int) $data['userId'];
$rangeLabel = date('d M Y', strtotime($data['from'])) . ' – ' . date('d M Y', strtotime($data['to']));
?>
<div class="ox-efforts" id="logEfforts">
    <div class="ox-pagebar">
        <form class="ox-efforts-range" method="get" action="<?= ASSERT_PATH ?>efforts" aria-label="Date range">
            <label class="visually-hidden" for="effortFrom">From</label>
            <input type="date" class="form-control" id="effortFrom" name="from" value="<?= esc($data['from'], 'attr') ?>" max="<?= esc($data['today'], 'attr') ?>">
            <span class="ox-muted" aria-hidden="true">to</span>
            <label class="visually-hidden" for="effortTo">To</label>
            <input type="date" class="form-control" id="effortTo" name="to" value="<?= esc($data['to'], 'attr') ?>" max="<?= esc($data['today'], 'attr') ?>">
            <button type="submit" class="btn btn-secondary">Apply</button>
        </form>
        <div class="ox-pagebar-actions">
            <button type="button" class="btn btn-primary" id="openLogEffort">
                <i class="icon-plus" aria-hidden="true"></i> Log effort
            </button>
        </div>
    </div>

    <section class="ox-panel">
        <header class="ox-panel-head">
            <h2 class="ox-panel-title">
                <span class="ox-stat-icon ox-tone-green"><i class="icon-watch" aria-hidden="true"></i></span>
                Spent time
                <span class="ox-muted ox-efforts-sum">(<?= esc($rangeLabel) ?>: <?= $fmtHours($data['total']) ?> h)</span>
            </h2>
        </header>

        <?php if ($data['days'] === []): ?>
            <div class="ox-empty">
                <i class="icon-watch" aria-hidden="true"></i>
                <h4>No effort logged in this range</h4>
                <p>Use “Log effort” to record the time you spent today.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table ox-efforts-table">
                    <thead>
                        <tr>
                            <th scope="col" class="ox-col-activity">Activity</th>
                            <th scope="col" class="ox-col-project">Project</th>
                            <th scope="col">Comment</th>
                            <th scope="col" class="text-end ox-col-hours">Hours</th>
                            <th scope="col" class="text-end ox-col-actions"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <?php foreach ($data['days'] as $day => $group): ?>
                        <tbody>
                            <tr class="ox-efforts-day">
                                <td colspan="3">
                                    <i class="icon-calendar" aria-hidden="true"></i>
                                    <?= esc(date('l, d M Y', strtotime($day))) ?>
                                    <span class="ox-efforts-count"><?= count($group['efforts']) ?> <?= count($group['efforts']) === 1 ? 'entry' : 'entries' ?></span>
                                </td>
                                <td class="text-end"><span class="ox-efforts-total"><?= $fmtHours($group['total']) ?> h</span></td>
                                <td></td>
                            </tr>
                            <?php foreach ($group['efforts'] as $effort): ?>
                                <?php
                                $taskUrl = null;
                                if ($effort['r_task_id'] && $effort['product_id']) {
                                    $taskUrl = 'backlog/tasks?' . http_build_query(array_filter([
                                        'pid'   => $effort['product_id'],
                                        'pblid' => $effort['backlog_item_id'],
                                        'usid'  => $effort['user_story_id'],
                                    ]));
                                }
                                $isMeeting = ! empty($effort['r_meeting_details_id']);
                                $edit = [
                                    'id'           => (int) $effort['effort_log_id'],
                                    'task'         => $effort['r_task_id'] ? (int) $effort['r_task_id'] : '',
                                    'taskLabel'    => $effort['r_task_id'] ? trim(($effort['product_name'] ?? '') . ' · #' . (int) $effort['r_task_id'] . ': ' . mb_strimwidth(trim((string) $effort['task_title']), 0, 90, '…')) : '',
                                    'meeting'      => $isMeeting ? (string) ($effort['meeting_title'] ?? 'Meeting') : '',
                                    'spentOn'      => $effort['spent_on'],
                                    'hours'        => $fmtHours($effort['hours']),
                                    'comments'     => (string) ($effort['comments'] ?? ''),
                                    'activity'     => $effort['r_activity_id'] ? (int) $effort['r_activity_id'] : '',
                                    'mainActivity' => $effort['r_main_activity_id'] ? (int) $effort['r_main_activity_id'] : '',
                                    'logType'      => $effort['r_log_type_id'] ? (int) $effort['r_log_type_id'] : '',
                                ];
                                ?>
                                <tr>
                                    <td class="ox-efforts-activity">
                                        <?php if (! empty($effort['activity_name'])): ?>
                                            <span class="ox-tag ox-tag-green"><?= esc($effort['activity_name']) ?></span>
                                        <?php elseif ($isMeeting): ?>
                                            <span class="ox-tag ox-tag-blue">Meeting</span>
                                        <?php else: ?>
                                            <span class="ox-tag ox-tag-gray">Not set</span>
                                        <?php endif; ?>
                                        <?php if (! empty($effort['main_activity_name'])): ?>
                                            <span class="ox-efforts-sub"><?= esc($effort['main_activity_name']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="ox-efforts-project">
                                        <span class="ox-efforts-product"><?= esc($effort['product_name'] ?? '—') ?></span>
                                        <?php if ($effort['r_task_id']): ?>
                                            <span class="ox-efforts-sub">
                                                <?php if ($taskUrl): ?><a href="<?= ASSERT_PATH . esc($taskUrl, 'attr') ?>">#<?= (int) $effort['r_task_id'] ?></a><?php else: ?>#<?= (int) $effort['r_task_id'] ?><?php endif; ?>
                                                <?= esc($effort['task_title'] ?? '') ?>
                                            </span>
                                        <?php elseif ($isMeeting): ?>
                                            <span class="ox-efforts-sub"><i class="icon-users" aria-hidden="true"></i> <?= esc($effort['meeting_title'] ?? '') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="ox-efforts-comment" data-label="Comment">
                                        <?php if (($effort['comments'] ?? '') !== ''): ?>
                                            <div class="ox-efforts-comment-text" data-bs-title="<?= esc($effort['comments'], 'attr') ?>"><?= esc($effort['comments']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end ox-efforts-hours" data-label="Hours"><?= $fmtHours($effort['hours']) ?></td>
                                    <td class="ox-efforts-actions">
                                        <button type="button" class="btn btn-link ox-icon-action" data-edit-effort="<?= esc(json_encode($edit, JSON_UNESCAPED_UNICODE), 'attr') ?>" title="Edit" aria-label="Edit this effort">
                                            <i class="icon-edit-2" aria-hidden="true"></i>
                                        </button>
                                        <button type="button" class="btn btn-link ox-icon-action" data-delete-effort="<?= (int) $effort['effort_log_id'] ?>" data-meeting="<?= $isMeeting ? '1' : '0' ?>" title="Delete" aria-label="Delete this effort">
                                            <i class="icon-trash-2" aria-hidden="true"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>

<div class="modal fade" id="logEffortModal" tabindex="-1" aria-labelledby="logEffortModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="logEffortForm" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="logEffortModalTitle">Log effort</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="effortId" name="effort_id" value="">
                    <div class="row g-3">
                        <div class="col-12" id="effortMeetingRow" hidden>
                            <label for="effortMeeting" class="form-label">Meeting</label>
                            <input type="text" class="form-control" id="effortMeeting" readonly>
                        </div>
                        <div class="col-12" id="effortTaskRow">
                            <label for="effortTask" class="form-label">Task <span class="ox-optional">(optional)</span></label>
                            <select class="form-select" id="effortTask" name="task_id">
                                <option value="">— No task —</option>
                                <?php foreach ($data['tasks'] as $task): ?>
                                    <option value="<?= (int) $task['task_id'] ?>"><?= esc($task['product_name']) ?> · #<?= (int) $task['task_id'] ?>: <?= esc(mb_strimwidth(trim((string) $task['task_title']), 0, 90, '…')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="effortUser" class="form-label">User</label>
                            <input type="text" class="form-control" id="effortUser" value="<?= esc($data['userName'] !== '' ? $data['userName'] . ' (me)' : 'Me', 'attr') ?>" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="effortSpentOn" class="form-label">Spent on</label>
                            <input type="date" class="form-control" id="effortSpentOn" name="spent_on" value="<?= esc($data['today'], 'attr') ?>" max="<?= esc($data['today'], 'attr') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="effortHours" class="form-label">Hours</label>
                            <input type="text" class="form-control" id="effortHours" name="hours" placeholder="h:mm" inputmode="decimal"
                                   pattern="^(\d{1,2}:[0-5]\d|\d{1,2}(\.\d{1,2})?)$" required aria-describedby="effortHoursHelp">
                            <div class="form-text" id="effortHoursHelp">For example 1:30 or 1.5</div>
                        </div>
                        <div class="col-12">
                            <label for="effortComments" class="form-label">Comments</label>
                            <textarea class="form-control" id="effortComments" name="comments" rows="2" maxlength="1000"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label for="effortActivity" class="form-label">Activity</label>
                            <select class="form-select" id="effortActivity" name="activity_id" required>
                                <option value="">--- Please select ---</option>
                                <?php foreach ($masters['activity'] as $entry): ?>
                                    <option value="<?= (int) $entry['effort_master_id'] ?>"><?= esc($entry['master_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="effortMainActivity" class="form-label">Main activity</label>
                            <select class="form-select" id="effortMainActivity" name="main_activity_id" required>
                                <option value="">--- Please select ---</option>
                                <?php foreach ($masters['main_activity'] as $entry): ?>
                                    <option value="<?= (int) $entry['effort_master_id'] ?>"><?= esc($entry['master_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="effortLogType" class="form-label">Log type</label>
                            <select class="form-select" id="effortLogType" name="log_type_id" required>
                                <?php foreach ($masters['log_type'] as $i => $entry): ?>
                                    <option value="<?= (int) $entry['effort_master_id'] ?>" <?= $i === 0 ? 'selected' : '' ?>><?= esc($entry['master_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary" data-mode="another" id="effortAddAnother">Create and add another</button>
                    <button type="submit" class="btn btn-primary" data-mode="close" id="effortSubmit">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
