<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff">
    <title><?= isset($title) && $title !== '' ? esc(strip_tags((string) $title)) . ' · Orvexa' : 'Orvexa' ?></title>
    <?= $this->include('layout/headerLinks') ?>
</head>

<body class="ox-app">
    <script>
        // Restore the collapsed sidebar before first paint to avoid a layout jump
        try {
            if (localStorage.getItem('ox-sidebar-collapsed') === '1' && window.innerWidth >= 1200) {
                document.body.classList.add('sidebar-collapsed');
            }
        } catch (e) {}
    </script>
    <div id="loader" role="status" aria-live="polite">
        <div class="app-loader-mark">
            <img src="<?= ASSERT_PATH ?>assets/images/logo/orvexa_06_app_icon.png" alt="">
        </div>
        <span class="app-loader-label">Loading</span>
    </div>
    <div class="ox-progress" id="oxProgress" aria-hidden="true"></div>
