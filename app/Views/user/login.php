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
    <link href="<?= ASSERT_PATH ?>assets/css/login/login.css?v=9" rel="stylesheet">

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

                <p class="ox-auth-help">Trouble signing in? Contact your Scrum admin.</p>
            </div>

            <footer class="ox-auth-footer">
                © <?= date('Y') ?> Orvexa · Orchestrate work. Accelerate execution.
            </footer>
        </section>

        <aside class="ox-auth-stage" aria-hidden="true">
            <div class="ox-stage-copy">
                <p class="ox-stage-eyebrow">Orvexa for Scrum teams</p>
                <h2>Orchestrate work.<br><span>Accelerate execution.</span></h2>
                <p>Backlog, sprints, ceremonies and reports — one calm place for the whole team.</p>
            </div>

            <div class="ox-stage-preview">
                <div class="ox-mock ox-mock-sprint">
                    <div class="ox-mock-head">
                        <span class="ox-mock-tag">Sprint 12</span>
                        <span class="ox-mock-live">In progress</span>
                    </div>
                    <div class="ox-mock-sprint-body">
                        <div class="ox-ring" style="--p: 68">
                            <strong>68%</strong>
                            <small>done</small>
                        </div>
                        <dl>
                            <div><dt>Tasks</dt><dd>24 / 35</dd></div>
                            <div><dt>Days left</dt><dd>6</dd></div>
                            <div><dt>Velocity</dt><dd>42 pts</dd></div>
                        </dl>
                    </div>
                    <div class="ox-mock-roles">
                        <span>PO</span><span>SM</span><span>DEV</span><span>DEV</span>
                    </div>
                </div>

                <div class="ox-mock ox-mock-burn">
                    <div class="ox-mock-head">
                        <span class="ox-mock-title">Burndown</span>
                        <span class="ox-mock-delta">On track</span>
                    </div>
                    <svg viewBox="0 0 220 90" preserveAspectRatio="none">
                        <path class="ox-burn-grid" d="M0 22.5h220M0 45h220M0 67.5h220"/>
                        <path class="ox-burn-ideal" d="M0 8 L220 86"/>
                        <path class="ox-burn-area" d="M0 8 L30 14 L60 26 L90 30 L120 44 L150 52 L150 90 L0 90Z"/>
                        <path class="ox-burn-line" d="M0 8 L30 14 L60 26 L90 30 L120 44 L150 52"/>
                        <circle class="ox-burn-dot" cx="150" cy="52" r="4"/>
                    </svg>
                </div>

                <div class="ox-mock ox-mock-toast">
                    <span class="ox-toast-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    </span>
                    <div>
                        <strong>Daily scrum</strong>
                        <small>Starts in 10 min · 15 min sync</small>
                    </div>
                </div>
            </div>

            <ul class="ox-stage-flow">
                <li>Backlog</li>
                <li>Planning</li>
                <li>Sprint</li>
                <li>Review</li>
                <li>Retro</li>
            </ul>
        </aside>
    </main>

    <script src="<?= ASSERT_PATH ?>assets/js/login/login.js?v=2"></script>
</body>
</html>
