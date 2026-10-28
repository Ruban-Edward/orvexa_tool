<?= $this->include('layout/header') ?>
<?php if (session()->getFlashdata('success') || session()->getFlashdata('error')): ?>
    <div id="globalFlashMessage" class="global-flash-message alert <?= session()->getFlashdata('success') ? 'alert-success' : 'alert-danger' ?>" role="alert">
        <?= esc(session()->getFlashdata('success') ?: session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
        setTimeout(function () {
            const alert = document.getElementById('globalFlashMessage');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        }, 3000);
    </script>
<?php endif; ?>
<?= $this->include('layout/sidebar') ?>
<div class="ox-main-col">
    <header class="ox-topbar">
        <button class="ox-iconbtn ox-hamburger" id="hamburger-icon" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="sidebar">
            <i class="icon-menu" aria-hidden="true"></i>
        </button>
        <a class="ox-topbar-logo" href="<?= ASSERT_PATH ?>dashboard/dashboardView" aria-label="Orvexa home">
            <img src="<?= ASSERT_PATH ?>assets/images/logo/orvexa_06_app_icon.png" alt="Orvexa">
        </a>

        <?php if (! empty($breadcrumbs)): ?>
            <nav class="ox-breadcrumbs bread" aria-label="Breadcrumb">
                <?php $lastKey = array_key_last($breadcrumbs); ?>
                <?php foreach ($breadcrumbs as $key => $url): ?>
                    <?php if ($key === $lastKey || $url === '' || $url === null): ?>
                        <span <?= $key === $lastKey ? 'aria-current="page"' : '' ?>><?= esc(ucfirst($key)) ?></span>
                    <?php else: ?>
                        <a href="<?= esc($url, 'attr') ?>"><?= esc(ucfirst($key)) ?></a>
                    <?php endif; ?>
                    <?php if ($key !== $lastKey): ?><i class="icon-chevron-right" aria-hidden="true"></i><?php endif; ?>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <div class="ox-topbar-actions">
            <button type="button" class="ox-search-trigger" id="oxSearchTrigger" aria-haspopup="dialog" aria-controls="oxSearch" aria-label="Search (Ctrl+K)">
                <i class="icon-search" aria-hidden="true"></i>
                <span class="ox-search-trigger-text">Search…</span>
                <kbd class="ox-kbd" data-shortcut>Ctrl K</kbd>
            </button>
            <div class="ox-notify">
                <button type="button" class="ox-iconbtn notification-icon" id="notification-icon" aria-label="Notifications" aria-controls="meetingDetails">
                    <i class="icon-bell" aria-hidden="true"></i>
                </button>
                <div class="sidebar-right ox-popover" id="meetingDetails">
                    <div class="cls-meetings">
                        <?= $this->include('layout/meetingDetails') ?>
                    </div>
                </div>
            </div>

            <?php $firstName = (string) session()->get('first_name'); ?>
            <div class="dropdown">
                <button class="ox-user-btn" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="avatar"><?= esc(strtoupper(substr($firstName, 0, 1))) ?></span>
                    <span class="ox-user-name"><?= esc(ucfirst($firstName)) ?></span>
                    <i class="icon-chevron-down" aria-hidden="true"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                    <li class="ox-dropdown-header">
                        <span class="avatar"><?= esc(strtoupper(substr($firstName, 0, 1))) ?></span>
                        <div>
                            <strong><?= esc(ucfirst($firstName)) ?></strong>
                            <small>Signed in</small>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= ASSERT_PATH ?>profile"><i class="icon-user" aria-hidden="true"></i> My profile</a></li>
                    <li><a class="dropdown-item" href="<?= ASSERT_PATH ?>mywork"><i class="icon-check-square" aria-hidden="true"></i> My work</a></li>
                    <li><button type="button" class="dropdown-item" onclick="Ox.showShortcuts()"><i class="icon-command" aria-hidden="true"></i> Keyboard shortcuts <kbd class="ox-kbd ms-auto">?</kbd></button></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= ASSERT_PATH ?>logout"><i class="icon-log-out" aria-hidden="true"></i> Sign out</a></li>
                </ul>
            </div>
        </div>
    </header>

    <main id="main" class="main-content" tabindex="-1">
        <?php if (isset($title) && $title !== ''): ?>
            <div class="ox-page-header">
                <h1 class="cls-h2"><?= $title ?></h1>
            </div>
        <?php endif; ?>

        <?php if (isset($view)) {
            echo $this->include($view);
        } ?>
    </main>
</div>

<div class="ox-search-dialog" id="oxSearch" role="dialog" aria-modal="true" aria-label="Search" hidden>
    <div class="ox-search-panel">
        <div class="ox-search-field">
            <i class="icon-search" aria-hidden="true"></i>
            <input type="search" id="oxSearchInput" aria-label="Search everything" placeholder="Search products, backlog, stories, tasks, sprints…" autocomplete="off"
                   role="combobox" aria-expanded="false" aria-controls="oxSearchResults" aria-autocomplete="list">
            <kbd class="ox-kbd">Esc</kbd>
        </div>
        <div class="ox-search-results" id="oxSearchResults" role="listbox" aria-label="Search results"></div>
        <div class="ox-search-foot">
            <span><kbd class="ox-kbd">↑</kbd><kbd class="ox-kbd">↓</kbd> to move</span>
            <span><kbd class="ox-kbd">Enter</kbd> to open</span>
            <span>Tip: type <b>#123</b> to find an item by ID</span>
        </div>
    </div>
</div>
<?= $this->include('layout/footer') ?>
