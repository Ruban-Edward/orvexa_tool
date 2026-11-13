<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset your Orvexa password</title>
</head>
<body style="margin:0;padding:24px 12px;background:#f7faf3;font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#27362d;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;margin:0 auto;">
        <tr>
            <td style="padding:0 0 16px;font-size:20px;font-weight:700;color:#102018;">Orvexa</td>
        </tr>
        <tr>
            <td style="background:#ffffff;border:1px solid #e3eadc;border-radius:12px;padding:28px;">
                <h1 style="margin:0 0 12px;font-size:20px;color:#102018;">Hi <?= esc($firstName) ?>,</h1>
                <p style="margin:0 0 18px;font-size:14px;line-height:1.6;">Someone asked to reset the password for the Orvexa account <b><?= esc($username) ?></b>. Use the button below to choose a new one.</p>
                <p style="margin:22px 0;text-align:center;">
                    <a href="<?= esc($resetUrl, 'attr') ?>" style="display:inline-block;padding:11px 22px;background:#42d674;color:#102018;font-size:14px;font-weight:700;text-decoration:none;border-radius:8px;">Choose a new password</a>
                </p>
                <p style="margin:0 0 12px;font-size:13px;line-height:1.6;color:#5e6e64;">The link works once and expires in <?= (int) $ttlMinutes ?> minutes.</p>
                <p style="margin:0;font-size:13px;line-height:1.6;color:#5e6e64;">If you did not ask for this, ignore this email. Your current password keeps working.</p>
            </td>
        </tr>
    </table>
</body>
</html>
