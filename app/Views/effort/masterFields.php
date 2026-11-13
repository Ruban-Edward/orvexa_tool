<?php
/**
 * Activity, Main activity and Log type selects for an effort form.
 *
 * @var array  $masters EffortMasterModel::getActiveGrouped()
 * @var string $prefix  id prefix, unique on the page
 * @var string $col     column class for each field
 */
$col ??= 'col-md-4';
$fields = [
    'activity'      => ['Activity', 'activity_id', 'Activity'],
    'main_activity' => ['Main activity', 'main_activity_id', 'MainActivity'],
    'log_type'      => ['Log type', 'log_type_id', 'LogType'],
];
?>
<?php foreach ($fields as $type => [$label, $name, $suffix]): ?>
    <div class="<?= esc($col, 'attr') ?>">
        <label for="<?= esc($prefix . $suffix, 'attr') ?>" class="form-label"><?= esc($label) ?></label>
        <select class="form-select" id="<?= esc($prefix . $suffix, 'attr') ?>" name="<?= $name ?>" required>
            <?php if ($type !== 'log_type'): ?>
                <option value="">--- Please select ---</option>
            <?php endif; ?>
            <?php foreach ($masters[$type] ?? [] as $i => $entry): ?>
                <option value="<?= (int) $entry['effort_master_id'] ?>" <?= ($type === 'log_type' && $i === 0) ? 'selected' : '' ?>><?= esc($entry['master_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
<?php endforeach; ?>
