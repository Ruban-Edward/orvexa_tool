<?php
$currentPath = trim(uri_string(), '/');

// Each item: url, icon (Feather name), label, optional permission and the path prefixes that mark it active
$navSections = [
    [
        'label' => null,
        'items' => [
            ['url' => 'dashboard/dashboardView', 'icon' => 'grid', 'label' => 'Dashboard', 'match' => ['dashboard']],
            ['url' => 'mywork', 'icon' => 'check-square', 'label' => 'My work', 'match' => ['mywork']],
        ],
    ],
    [
        'label' => 'Workspace',
        'items' => [
            ['url' => 'backlog/productbacklogs', 'icon' => 'package', 'label' => 'Products', 'permission' => 'backlog/productbacklogs', 'match' => ['backlog']],
            ['url' => 'sprint/sprintlist', 'icon' => 'zap', 'label' => 'Sprints', 'permission' => 'sprint/sprintlist', 'match' => ['sprint']],
            ['url' => 'meeting/calendar', 'icon' => 'calendar', 'label' => 'Calendar', 'permission' => 'meeting/calendar', 'match' => ['meeting']],
        ],
    ],
    [
        'label' => 'Reports',
        'items' => [
            ['url' => 'report/backlogreport/backlog', 'icon' => 'layers', 'label' => 'Backlog report', 'permission' => 'report/backlogreport/backlog', 'match' => ['report/backlogreport']],
            ['url' => 'report/sprintreport/sprint', 'icon' => 'trending-up', 'label' => 'Sprint report', 'permission' => 'report/sprintreport/sprint', 'match' => ['report/sprintreport']],
            ['url' => 'report/meetingreport/meet', 'icon' => 'clock', 'label' => 'Meeting report', 'permission' => 'report/meetingreport/meet', 'match' => ['report/meetingreport']],
        ],
    ],
    [
        'label' => 'Administration',
        'items' => [
            ['url' => 'admin/manageUser', 'icon' => 'users', 'label' => 'Users', 'permission' => 'admin/manageUser', 'match' => ['admin/manageUser']],
            ['url' => 'admin/setPermissionPage', 'icon' => 'shield', 'label' => 'Permissions', 'permission' => 'admin/setPermissionPage', 'match' => ['admin/setPermissionPage']],
            ['url' => 'admin/adminSettings', 'icon' => 'settings', 'label' => 'Settings', 'permission' => 'admin/adminSettings', 'match' => ['admin/adminSettings']],
        ],
    ],
];

$isActive = static function (array $prefixes) use ($currentPath): bool {
    foreach ($prefixes as $prefix) {
        if ($currentPath === $prefix || str_starts_with($currentPath, $prefix . '/')) {
            return true;
        }
    }

    return false;
};
?>
<!-- Sidebar -->
<aside id="sidebar" class="ox-sidebar" aria-label="Main navigation">
    <div class="ox-sidebar-brand">
        <a href="<?= ASSERT_PATH ?>dashboard/dashboardView" class="ox-brand-link" aria-label="Orvexa home">
            <img class="ox-brand-full" src="<?= ASSERT_PATH ?>assets/images/logo/orvexa_05_horizontal_logo.png" alt="Orvexa">
            <img class="ox-brand-mark" src="<?= ASSERT_PATH ?>assets/images/logo/orvexa_06_app_icon.png" alt="Orvexa">
        </a>
    </div>

    <nav class="ox-sidenav">
        <?php foreach ($navSections as $section): ?>
            <?php
            $items = array_filter($section['items'], static fn ($item) => ! isset($item['permission']) || has_permission($item['permission']));
            if ($items === []) {
                continue;
            }
            ?>
            <div class="ox-sidenav-section">
                <?php if ($section['label'] !== null): ?>
                    <div class="ox-sidenav-label"><?= esc($section['label']) ?></div>
                <?php endif; ?>
                <ul class="ox-sidenav-list">
                    <?php foreach ($items as $item): ?>
                        <?php $active = $isActive($item['match']); ?>
                        <li>
                            <a href="<?= ASSERT_PATH . $item['url'] ?>"
                               class="ox-sidenav-link<?= $active ? ' is-active' : '' ?>"
                               <?= $active ? 'aria-current="page"' : '' ?>
                               data-tooltip="<?= esc($item['label'], 'attr') ?>">
                                <i class="icon-<?= $item['icon'] ?>" aria-hidden="true"></i>
                                <span><?= esc($item['label']) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </nav>

    <div class="ox-sidebar-footer">
        <?php if (has_permission('userGuide')): ?>
            <a href="https://www.orvexa.com/docs/" target="_blank" rel="noopener" class="ox-sidenav-link" data-tooltip="User guide">
                <i class="icon-book-open" aria-hidden="true"></i>
                <span>User guide</span>
            </a>
        <?php endif; ?>
        <button id="sidebar-toggle" class="ox-sidenav-link ox-collapse-btn" type="button" aria-label="Collapse navigation" aria-expanded="true" data-tooltip="Expand">
            <i class="icon-sidebar" aria-hidden="true"></i>
            <span>Collapse</span>
        </button>
    </div>
</aside>
