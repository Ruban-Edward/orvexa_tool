<?= view("layout/csrf") ?>
<link rel="shortcut icon" href="<?= ASSERT_PATH ?>support/compiled/png/fav_icon.png" type="image/x-icon">
    <link rel="stylesheet" href="<?= ASSERT_PATH ?>support/compiled/css/app.css?v=2">
    <link rel="stylesheet" href="<?= ASSERT_PATH ?>support/extensions/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSERT_PATH ?>support/compiled/css/iconly.css">
    <link rel="stylesheet" href="<?= ASSERT_PATH ?>support/compiled/css/styles.css?v=2">
    <link rel="stylesheet" href="<?= ASSERT_PATH ?>support/extensions/sweetalert2/sweetalert2.css">
    <link rel="stylesheet" href="<?= ASSERT_PATH ?>support/compiled/css/tippy.css">
    <link rel="stylesheet" href="<?= ASSERT_PATH ?>support/extensions/flatpickr/flatpickr.min.css">

    <?php if (isset($view) && is_file(FCPATH . 'assets/css/' . $view . '.css')) {
        echo "<link rel='stylesheet' href='" . CSS_PATH . $view . ".css?v=" . filemtime(FCPATH . 'assets/css/' . $view . '.css') . "'>";
    } ?>
    <link rel="stylesheet" href="<?= ASSERT_PATH ?>assets/css/app-theme.css?v=15">
    <link rel="stylesheet" href="<?= ASSERT_PATH ?>assets/css/orvexa-pro.css?v=1">
    <link rel="preload" href="<?= ASSERT_PATH ?>assets/fonts/inter/Inter-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= ASSERT_PATH ?>assets/css/orvexa-ui.css?v=<?= filemtime(FCPATH . 'assets/css/orvexa-ui.css') ?>">

    <!-- Libraries every page uses -->
    <script src="<?= ASSERT_PATH ?>support/extensions/jquery/jquery.min.js"></script>
    <script src="<?= ASSERT_PATH ?>support/static/js/initTheme.js"></script>
    <script>document.documentElement.setAttribute('data-bs-theme', 'light');</script>
    <script src="<?= ASSERT_PATH ?>support/compiled/js/popper.js"></script>
    <script src="<?= ASSERT_PATH ?>support/compiled/js/commonJSHelper.js"></script>
    <script src="<?= ASSERT_PATH ?>support/extensions/parsleyjs/parsley.min.js"></script>
    <script src="<?= ASSERT_PATH ?>support/static/js/pages/parsley.js"></script>
    <script src="<?= ASSERT_PATH ?>support/extensions/sweetalert2/sweetalert2.js"></script>
    <script src="<?= ASSERT_PATH ?>assets/js/dashboard/multiselect-dropdown.js"></script>

    <!-- Heavy libraries only on the pages that need them (Config\PageAssets) -->
    <?php foreach (config('PageAssets')->scriptsFor($view ?? null) as $script): ?>
    <script src="<?= ASSERT_PATH . $script ?>"></script>
    <?php endforeach; ?>
