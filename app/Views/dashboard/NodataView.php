<?php
// $data names what is missing ("Product", "Records", …); null means a generic "no data" state.
$subject = $data ?? null;
$copy = [
    'Product' => ['icon-package', 'No products yet', 'You are not a member of any product. Ask an admin to add you to one.'],
    'Records' => ['icon-file-text', 'No records to report', 'Nothing matches this report yet. Records appear here once work is logged.'],
];
[$icon, $heading, $text] = $copy[$subject] ?? ['icon-inbox', 'Nothing here yet', 'There is no data to show for this page. Go back and choose another item.'];
?>
<div class="ox-empty ox-panel ox-empty-page">
    <i class="<?= $icon ?>" aria-hidden="true"></i>
    <h4><?= esc($heading) ?></h4>
    <p><?= esc($text) ?></p>
</div>
