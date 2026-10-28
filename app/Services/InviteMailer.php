<?php

/**
 * InviteMailer.php
 *
 * @category Service
 * @purpose  Emails new (or reset) users their sign-in details.
 *
 *           Invites are recorded in `orvexa_user_invite` without any password. When an
 *           invite is sent, a fresh temporary password is generated, emailed, and only
 *           then stored (hashed) on the user, who must change it at the next sign-in.
 *
 *           Sending happens after the HTTP response has gone to the browser (so the
 *           admin never waits on SMTP); `php spark invites:send` retries anything left.
 */

namespace App\Services;

use Config\Email as EmailConfig;
use Throwable;

class InviteMailer
{
    private const BATCH = 50;

    private static bool $scheduled = false;

    /**
     * Mail counts as set up when there is a sender and, for SMTP, a host.
     */
    public static function isConfigured(): bool
    {
        $config = config(EmailConfig::class);

        return trim($config->fromEmail) !== ''
            && ($config->protocol !== 'smtp' || trim($config->SMTPHost) !== '');
    }

    /**
     * Record invites for the given users and send them once the response is out.
     *
     * @param int[] $userIds internal user ids
     */
    public function queue(array $userIds, int $invitedBy): void
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if ($userIds === []) {
            return;
        }
        $now = date('Y-m-d H:i:s');
        db_connect()->table('orvexa_user_invite')->insertBatch(array_map(static fn ($id) => [
            'r_user_id'    => $id,
            'status'       => 'queued',
            'r_invited_by' => $invitedBy,
            'created_date' => $now,
        ], $userIds));

        // Runs after the framework has written the response; finishing the FastCGI
        // request first means the browser gets it before any SMTP work starts
        if (!self::$scheduled && !is_cli()) {
            self::$scheduled = true;
            register_shutdown_function(static function () {
                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                }
                ignore_user_abort(true);
                (new self())->sendPending();
            });
        }
    }

    /**
     * Send queued invites. Returns [sent, failed].
     */
    public function sendPending(int $limit = self::BATCH): array
    {
        $db = db_connect();
        $invites = $db->table('orvexa_user_invite i')
            ->select('i.invite_id, u.user_id, u.first_name, u.external_username, u.email_id, u.is_deleted')
            ->join('orvexa_user u', 'u.user_id = i.r_user_id')
            ->where('i.status', 'queued')
            ->orderBy('i.invite_id')
            ->limit($limit)
            ->get()->getResultArray();

        $sent = $failed = 0;
        foreach ($invites as $invite) {
            $error = $invite['is_deleted'] === 'Y' ? 'The user is inactive.' : $this->sendOne($invite);
            $db->table('orvexa_user_invite')->where('invite_id', $invite['invite_id'])->update([
                'status'    => $error === null ? 'sent' : 'failed',
                'error'     => $error,
                'sent_date' => date('Y-m-d H:i:s'),
            ]);
            $error === null ? $sent++ : $failed++;
        }

        return [$sent, $failed];
    }

    /**
     * @return string|null error message, or null when sent
     */
    private function sendOne(array $invite): ?string
    {
        if (!self::isConfigured()) {
            return 'Email is not set up.';
        }
        try {
            $password = self::temporaryPassword();
            $email = service('email', null, false);
            $email->setTo($invite['email_id']);
            $email->setSubject('Your Orvexa account');
            $email->setMailType('html');
            $email->setMessage(view('mailTemplate/userInvite', [
                'firstName' => $invite['first_name'],
                'username'  => $invite['external_username'],
                'password'  => $password,
                'signInUrl' => base_url('login'),
            ]));
            if (!$email->send(false)) {
                return 'The mail server rejected the message.';
            }
            // Only a successfully emailed password replaces the current one
            db_connect()->table('orvexa_user')->where('user_id', $invite['user_id'])->update([
                'password'                => password_hash($password, PASSWORD_DEFAULT),
                'password_reset_required' => 'Y',
                'updated_date'            => date('Y-m-d H:i:s'),
            ]);

            return null;
        } catch (Throwable $e) {
            log_message('error', 'Invite email failed: ' . $e->getMessage());

            return 'The email could not be sent.';
        }
    }

    /**
     * 12 characters without look-alikes (0/O, 1/l/I) so it can be read out or typed.
     */
    public static function temporaryPassword(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        $password = '';
        for ($i = 0; $i < 12; $i++) {
            $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $password;
    }
}
