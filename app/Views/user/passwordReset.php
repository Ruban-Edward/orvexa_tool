<?php
/**
 * Forgot password (mode "request"), choose a new password (mode "reset"), or a dead link
 * (mode "invalid"). Shares the sign-in page's card and stage.
 *
 * @var string $mode
 */
$titles = [
    'request' => ['Forgot your password?', 'Enter your username or work email and we will send you a link to choose a new password.'],
    'reset'   => ['Choose a new password', 'Pick a password of at least 8 characters for ' . ($username ?? '') . '.'],
    'invalid' => ['This link has expired', 'Reset links work once and for ' . \App\Services\PasswordResetService::TTL_MINUTES . ' minutes. Request a new one below.'],
];
[$heading, $lead] = $titles[$mode];
$fieldErrors = session()->getFlashdata('password_errors') ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#102018">
    <meta name="referrer" content="no-referrer">
    <title>Reset password · Orvexa</title>

    <link rel="shortcut icon" href="<?= ASSERT_PATH ?>support/compiled/png/fav_icon.png" type="image/x-icon">
    <link rel="stylesheet" href="<?= ASSERT_PATH ?>assets/css/bootstrap/css/bootstrap.min.css">
    <link rel="preload" href="<?= ASSERT_PATH ?>assets/fonts/inter/Inter-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link href="<?= ASSERT_PATH ?>assets/css/login/login.css?v=10" rel="stylesheet">
    <?php foreach (['tokens', 'glass', 'motion', 'background', 'login/login-glass'] as $oxSheet): ?>
    <link rel="stylesheet" href="<?= ASSERT_PATH ?>assets/css/<?= $oxSheet ?>.css?v=<?= filemtime(FCPATH . 'assets/css/' . $oxSheet . '.css') ?>">
    <?php endforeach; ?>

    <?= view('layout/csrf') ?>
</head>
<body class="ox-auth">
    <main class="ox-auth-shell">
        <section class="ox-auth-panel">
            <header class="ox-auth-brand">
                <img src="<?= ASSERT_PATH ?>assets/images/logo/orvexa_05_horizontal_logo.png" alt="Orvexa" width="142" height="40">
            </header>

            <div class="ox-auth-card">
                <p class="ox-auth-eyebrow">Account recovery</p>
                <h1><?= esc($heading) ?></h1>
                <p class="ox-auth-lead"><?= esc($lead) ?></p>

                <?php if (session()->has('error')): ?>
                    <div class="ox-auth-alert" role="alert">
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

                <?php if ($mode === 'reset'): ?>
                    <form class="needs-validation ox-reset-form" method="post" action="<?= ASSERT_PATH ?>reset-password" novalidate>
                        <input type="hidden" name="token" value="<?= esc($token, 'attr') ?>">
                        <input type="text" name="username" value="<?= esc($username, 'attr') ?>" autocomplete="username" hidden>
                        <div class="ox-field">
                            <label for="new_password">New password</label>
                            <div class="ox-input">
                                <svg class="ox-input-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                                <input type="password" id="new_password" class="form-control" name="new_password"
                                    autocomplete="new-password" minlength="8" maxlength="72" autofocus required>
                                <div class="invalid-feedback">Use at least 8 characters</div>
                            </div>
                            <div class="cls-error-msg"><?= esc($fieldErrors['new_password'] ?? '') ?></div>
                        </div>
                        <div class="ox-field">
                            <label for="confirm_password">Confirm new password</label>
                            <div class="ox-input">
                                <svg class="ox-input-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                                <input type="password" id="confirm_password" class="form-control" name="confirm_password"
                                    autocomplete="new-password" minlength="8" maxlength="72" required>
                                <div class="invalid-feedback">Repeat the new password</div>
                            </div>
                            <div class="cls-error-msg"><?= esc($fieldErrors['confirm_password'] ?? '') ?></div>
                        </div>
                        <button type="submit" class="ox-auth-submit">
                            <span class="ox-spinner" aria-hidden="true"></span>
                            <span class="ox-submit-label">Save new password</span>
                        </button>
                    </form>
                <?php elseif (! empty($mailReady)): ?>
                    <form class="needs-validation ox-reset-form" method="post" action="<?= ASSERT_PATH ?>forgot-password" novalidate>
                        <div class="ox-field">
                            <label for="identifier">Username or email</label>
                            <div class="ox-input">
                                <svg class="ox-input-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <input type="text" id="identifier" class="form-control" name="identifier" maxlength="150"
                                    autocomplete="username" autocapitalize="none" spellcheck="false" autofocus required>
                                <div class="invalid-feedback">Enter your username or email</div>
                            </div>
                        </div>
                        <button type="submit" class="ox-auth-submit">
                            <span class="ox-spinner" aria-hidden="true"></span>
                            <span class="ox-submit-label">Send reset link</span>
                        </button>
                    </form>
                <?php else: ?>
                    <p class="ox-auth-help">Email is not set up on this server. Ask your Scrum admin to reset your password.</p>
                <?php endif ?>

                <p class="ox-auth-help"><a href="<?= ASSERT_PATH ?>login">Back to sign in</a></p>
            </div>

            <footer class="ox-auth-footer">
                © <?= date('Y') ?> Orvexa · Orchestrate work. Accelerate execution.
            </footer>
        </section>

        <?= view('user/authStage') ?>
    </main>

    <script>
    document.querySelectorAll('.ox-reset-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var confirm = form.querySelector('#confirm_password');
            if (confirm) {
                confirm.setCustomValidity(confirm.value === form.querySelector('#new_password').value ? '' : 'mismatch');
            }
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                var submit = form.querySelector('.ox-auth-submit');
                submit.classList.add('is-loading');
                submit.setAttribute('aria-busy', 'true');
            }
            form.classList.add('was-validated');
        });
    });
    </script>
</body>
</html>
