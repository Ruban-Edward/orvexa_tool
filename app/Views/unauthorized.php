<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access denied · Orvexa</title>
    <link rel="shortcut icon" href="<?= base_url() ?>support/compiled/png/fav_icon.png" type="image/x-icon">
    <style>
        @font-face {
            font-family: "Inter";
            font-weight: 400 700;
            font-display: swap;
            src: url("<?= base_url() ?>assets/fonts/inter/Inter-latin.woff2") format("woff2");
        }

        * { box-sizing: border-box; }

        body {
            display: grid;
            place-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 24px;
            background: #f7faf3;
            color: #2c3a32;
            font-family: "Inter", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .card {
            width: 100%;
            max-width: 440px;
            padding: 40px 32px;
            border: 1px solid #e3eadc;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(16, 27, 21, 0.05);
            text-align: center;
        }

        .icon {
            display: grid;
            place-items: center;
            width: 56px;
            height: 56px;
            margin: 0 auto 20px;
            border-radius: 14px;
            background: #fef3f2;
            color: #d92d20;
        }

        .code {
            margin: 0 0 6px;
            color: #66746c;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0 0 8px;
            color: #102018;
            font-size: 22px;
            font-weight: 650;
            letter-spacing: -0.01em;
        }

        p {
            margin: 0 0 28px;
            color: #66746c;
            font-size: 14px;
            line-height: 1.55;
        }

        .actions {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            height: 38px;
            padding: 0 16px;
            border: 1px solid #cdd6d0;
            border-radius: 8px;
            background: #fff;
            color: #102018;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
        }

        .button:hover { background: #f9fbfa; }

        .button.primary {
            border-color: #34c466;
            background: #42d674;
            color: #102018;
        }

        .button.primary:hover { background: #34c466; }
    </style>
</head>
<body>
    <main class="card">
        <div class="icon" aria-hidden="true">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <p class="code">Error 401</p>
        <h1>You don't have access to this page</h1>
        <p>Your role doesn't include permission for this area. Ask an administrator if you think you should have access.</p>
        <div class="actions">
            <a href="javascript:history.back()" class="button">Go back</a>
            <a href="<?= base_url() ?>dashboard/dashboardView" class="button primary">Go to dashboard</a>
        </div>
    </main>
</body>
</html>
