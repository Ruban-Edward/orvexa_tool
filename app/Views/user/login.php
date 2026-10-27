<!--
Author: Stervin Richard
Email: stervinrichard74@gmail.com
Created Date: 7 July 2024
Updated Date: 16 July 2024
-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff">
    <title>Sign in · Orvexa</title>

    <link rel="shortcut icon" href="<?= ASSERT_PATH ?>support/compiled/png/fav_icon.png" type="image/x-icon">
    <link rel="stylesheet" href="<?= ASSERT_PATH ?>assets/css/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= ASSERT_PATH ?>support/compiled/css/app.css?v=2">
    <link rel="preload" href="<?= ASSERT_PATH ?>assets/fonts/inter/Inter-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link href="<?= ASSERT_PATH ?>assets/css/login/login.css?v=7" rel="stylesheet">

    <?= view('layout/csrf') ?>
    <script src="<?= ASSERT_PATH ?>support/extensions/jquery/jquery.min.js"></script>
</head>
<body class="ox-auth">
    <main class="ox-auth-shell">
        <section class="ox-auth-panel">
            <header class="ox-auth-brand">
                <img src="<?= ASSERT_PATH ?>assets/images/logo/orvexa_05_horizontal_logo.png" alt="Orvexa">
            </header>

            <div class="ox-auth-card">
                <h1 id="login-or-signup-msg">Welcome back</h1>
                <p class="ox-auth-lead">Sign in to your Orvexa workspace.</p>

                <?php if (session()->has('error')): ?>
                    <div class="ox-auth-alert cls-wrong-username-password" role="alert">
                        <svg viewBox="0 0 20 20" aria-hidden="true"><path fill="currentColor" d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm0 4a1 1 0 0 1 1 1v4a1 1 0 1 1-2 0V7a1 1 0 0 1 1-1Zm0 8.5a1.25 1.25 0 1 1 0-2.5 1.25 1.25 0 0 1 0 2.5Z"/></svg>
                        <span><?= esc(session()->getFlashdata('error')) ?></span>
                    </div>
                <?php endif ?>

                <form id="loginForm" class="needs-validation" method="post" action="login" novalidate>
                    <div class="ox-field">
                        <label for="username">Username</label>
                        <input type="text" id="username" class="form-control" placeholder="Enter your username"
                            name="username" autocomplete="username" autofocus required>
                        <div class="invalid-feedback">Username is required</div>
                        <?php if (session()->has('validation')): ?>
                            <div class="cls-error-msg"><?= esc(session()->getFlashdata('validation')['username'] ?? '') ?></div>
                        <?php endif ?>
                    </div>

                    <div class="ox-field">
                        <label for="password">Password</label>
                        <div class="cls-password">
                            <input type="password" id="password" class="form-control" placeholder="Enter your password"
                                name="password" autocomplete="current-password" required>
                            <i class="bi bi-eye-slash" id="togglePassword" role="button" tabindex="0" aria-label="Show or hide password"></i>
                            <div class="invalid-feedback">Password is required</div>
                        </div>
                        <?php if (session()->has('validation')): ?>
                            <div class="cls-error-msg"><?= esc(session()->getFlashdata('validation')['password'] ?? '') ?></div>
                        <?php endif ?>
                    </div>

                    <button type="submit" class="btn btn-primary ox-auth-submit">Sign in</button>
                </form>
            </div>

            <footer class="ox-auth-footer">
                © <?= date('Y') ?> Orvexa · Orchestrate work. Accelerate execution.
            </footer>
        </section>

        <aside class="ox-auth-visual" aria-hidden="true">
            <picture>
                <source srcset="<?= ASSERT_PATH ?>assets/images/login/login_visual.webp" type="image/webp">
                <img src="<?= ASSERT_PATH ?>assets/images/login/login_visual.jpg" alt="">
            </picture>
        </aside>
    </main>

    <script src="<?= ASSERT_PATH ?>assets/js/login/login.js"></script>
</body>
</html>
