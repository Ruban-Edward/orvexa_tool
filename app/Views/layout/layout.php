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
                    <li><a class="dropdown-item" href="<?= ASSERT_PATH ?>logout"><i class="icon-log-out" aria-hidden="true"></i> Sign out</a></li>
                </ul>
            </div>
        </div>
    </header>

    <main id="main" class="main-content">
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
<?= $this->include('layout/footer') ?>
