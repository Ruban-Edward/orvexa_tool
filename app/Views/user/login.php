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
    <meta name="theme-color" content="#102018">
    <title>Sign in · Orvexa</title>

    <link rel="shortcut icon" href="<?= ASSERT_PATH ?>support/compiled/png/fav_icon.png" type="image/x-icon">
    <link rel="stylesheet" href="<?= ASSERT_PATH ?>assets/css/bootstrap/css/bootstrap.min.css">
    <link rel="preload" href="<?= ASSERT_PATH ?>assets/fonts/inter/Inter-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link href="<?= ASSERT_PATH ?>assets/css/login/login.css?v=10" rel="stylesheet">
    <?php foreach (['tokens', 'glass', 'motion', 'background', 'login/login-glass'] as $oxSheet): ?>
    <link rel="stylesheet" href="<?= ASSERT_PATH ?>assets/css/<?= $oxSheet ?>.css?v=<?= filemtime(FCPATH . 'assets/css/' . $oxSheet . '.css') ?>">
    <?php endforeach; ?>

    <?= view('layout/csrf') ?>
    <script src="<?= ASSERT_PATH ?>support/extensions/jquery/jquery.min.js"></script>
</head>
<body class="ox-auth">
    <main class="ox-auth-shell">
        <section class="ox-auth-panel">
            <header class="ox-auth-brand">
                <img src="<?= ASSERT_PATH ?>assets/images/logo/orvexa_05_horizontal_logo.png" alt="Orvexa" width="142" height="40">
            </header>

            <div class="ox-auth-card">
                <p class="ox-auth-eyebrow">Scrum workspace</p>
                <h1 id="login-or-signup-msg">Welcome back</h1>
                <p class="ox-auth-lead">Sign in to plan sprints, refine the backlog and keep your team in sync.</p>

                <?php if (session()->has('error')): ?>
                    <div class="ox-auth-alert cls-wrong-username-password" role="alert">
                        <svg viewBox="0 0 20 20" aria-hidden="true"><path fill="currentColor" d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm0 4a1 1 0 0 1 1 1v4a1 1 0 1 1-2 0V7a1 1 0 0 1 1-1Zm0 8.5a1.25 1.25 0 1 1 0-2.5 1.25 1.25 0 0 1 0 2.5Z"/></svg>
                        <span><?= esc(session()->getFlashdata('error')) ?></span>
                    </div>
                <?php endif ?>
                <?php if (session()->has('success')): ?>
                    <div class="ox-auth-alert is-success" role="status">
                        <svg viewBox="0 0 20 20" aria-hidden="true"><path fill="currentColor" d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm3.7 6.2-4.2 4.6a1 1 0 0 1-1.5 0l-2-2.1a1 1 0 1 1 1.5-1.4l1.2 1.3 3.5-3.8a1 1 0 1 1 1.5 1.4Z"/></svg>
                        <span><?= esc(session()->getFlashdata('success')) ?></span>
                    </div>
                <?php endif ?>

                <form id="loginForm" class="needs-validation" method="post" action="login" novalidate>
                    <div class="ox-field">
                        <label for="username">Username</label>
                        <div class="ox-input">
                            <svg class="ox-input-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <input type="text" id="username" class="form-control" placeholder="Your Orvexa username"
                                name="username" autocomplete="username" autocapitalize="none" spellcheck="false" autofocus required>
                            <div class="invalid-feedback">Enter your username</div>
                        </div>
                        <?php if (session()->has('validation')): ?>
                            <div class="cls-error-msg"><?= esc(session()->getFlashdata('validation')['username'] ?? '') ?></div>
                        <?php endif ?>
                    </div>

                    <div class="ox-field">
                        <label for="password">Password</label>
                        <div class="ox-input cls-password">
                            <svg class="ox-input-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                            <input type="password" id="password" class="form-control" placeholder="Your password"
                                name="password" autocomplete="current-password" required>
                            <button type="button" class="ox-reveal" id="togglePassword" aria-label="Show password" aria-pressed="false">
                                <svg class="ox-eye" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="ox-eye-off" viewBox="0 0 24 24" aria-hidden="true"><path d="M10.6 5.1A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a17.6 17.6 0 0 1-3.2 4.2M6.6 6.6A17.4 17.4 0 0 0 2 12s3.6 7 10 7a9.7 9.7 0 0 0 5.4-1.6"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/><path d="m2 2 20 20"/></svg>
                            </button>
                            <div class="invalid-feedback">Enter your password</div>
                        </div>
                        <p class="ox-caps" id="capsWarning" hidden>Caps Lock is on</p>
                        <?php if (session()->has('validation')): ?>
                            <div class="cls-error-msg"><?= esc(session()->getFlashdata('validation')['password'] ?? '') ?></div>
                        <?php endif ?>
                    </div>

                    <button type="submit" class="ox-auth-submit" id="signInButton">
                        <span class="ox-spinner" aria-hidden="true"></span>
                        <span class="ox-submit-label">Sign in</span>
                    </button>
                </form>

                <?php if (\App\Services\InviteMailer::isConfigured()): ?>
                    <p class="ox-auth-help"><a href="<?= ASSERT_PATH ?>forgot-password">Forgot your password?</a></p>
                <?php else: ?>
                    <p class="ox-auth-help">Trouble signing in? Contact your Scrum admin.</p>
                <?php endif ?>
            </div>

            <footer class="ox-auth-footer">
                © <?= date('Y') ?> Orvexa · Orchestrate work. Accelerate execution.
            </footer>
        </section>

        <?= view('user/authStage') ?>
    </main>

    <script src="<?= ASSERT_PATH ?>assets/js/login/login.js?v=2"></script>
</body>
</html>
