<?php

/**
 * PasswordResetService.php
 *
 * @category Service
 * @purpose  Self-service "Forgot password". A request emails a one-time link (valid for
 *           TTL_MINUTES); nothing changes on the account until the link is used, so a request
 *           made by someone else cannot lock the user out. The response never reveals whether
 *           an account exists, and the email is sent after the response has gone out.
 */

namespace App\Services;

use Throwable;

class PasswordResetService
{
    public const TTL_MINUTES = 60;
    private const MAX_PER_USER_PER_HOUR = 3;

    private const TABLE = 'orvexa_password_reset';

    /**
     * Starts a reset for the active user with this username or email address, if any.
     * Always returns quietly; callers show the same message either way.
     */
    public function request(string $identifier, string $ip): void
    {
        $identifier = trim($identifier);
        if ($identifier === '' || ! InviteMailer::isConfigured()) {
            return;
        }
        $db   = db_connect();
        $user = $db->table('orvexa_user')
            ->select('user_id, first_name, external_username, email_id')
            ->groupStart()->where('external_username', $identifier)->orWhere('email_id', $identifier)->groupEnd()
            ->where('is_deleted', 'N')
            ->limit(2)->get()->getResultArray();
        // An ambiguous match (same text as one user's name and another's email) sends nothing
        if (count($user) !== 1 || trim((string) $user[0]['email_id']) === '') {
            return;
        }
        $user = $user[0];

        $recent = $db->table(self::TABLE)
            ->where('r_user_id', $user['user_id'])
            ->where('created_date >=', date('Y-m-d H:i:s', time() - HOUR))
            ->countAllResults();
        if ($recent >= self::MAX_PER_USER_PER_HOUR) {
            return;
        }

        $token = bin2hex(random_bytes(32));
        $now   = date('Y-m-d H:i:s');
        // A new link replaces any earlier one that is still open
        $db->table(self::TABLE)->where('r_user_id', $user['user_id'])->where('used_at', null)->update(['used_at' => $now]);
        $db->table(self::TABLE)->insert([
            'r_user_id'    => $user['user_id'],
            'token_hash'   => hash('sha256', $token),
            'expires_at'   => date('Y-m-d H:i:s', time() + self::TTL_MINUTES * MINUTE),
            'request_ip'   => substr($ip, 0, 45),
            'created_date' => $now,
        ]);

        $send = static function () use ($user, $token) {
            (new self())->sendEmail($user, $token);
        };
        if (is_cli()) {
            $send();

            return;
        }
        register_shutdown_function(static function () use ($send) {
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
            ignore_user_abort(true);
            $send();
        });
    }

    /** The open reset row for a token, joined to its active user, or null. */
    public function findValid(string $token): ?array
    {
        if (! preg_match('/^[0-9a-f]{64}$/', $token)) {
            return null;
        }
        $row = db_connect()->table(self::TABLE . ' r')
            ->select('r.reset_id, r.r_user_id, u.first_name, u.external_username')
            ->join('orvexa_user u', 'u.user_id = r.r_user_id')
            ->where('r.token_hash', hash('sha256', $token))
            ->where('r.used_at', null)
            ->where('r.expires_at >', date('Y-m-d H:i:s'))
            ->where('u.is_deleted', 'N')
            ->get()->getRowArray();

        return $row ?: null;
    }

    /** Sets the new password and closes the link. False when the link is no longer valid. */
    public function reset(string $token, string $password): bool
    {
        $row = $this->findValid($token);
        if (! $row) {
            return false;
        }
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');
        $db->transBegin();
        // Claim the link first so two submits cannot both use it
        $db->table(self::TABLE)->where(['reset_id' => $row['reset_id'], 'used_at' => null])->update(['used_at' => $now]);
        if ($db->affectedRows() !== 1) {
            $db->transRollback();

            return false;
        }
        $db->table(self::TABLE)->where('r_user_id', $row['r_user_id'])->where('used_at', null)->update(['used_at' => $now]);
        $db->table('orvexa_user')->where('user_id', $row['r_user_id'])->update([
            'password'                => password_hash($password, PASSWORD_DEFAULT),
            'password_reset_required' => 'N',
            'updated_date'            => $now,
        ]);
        if (! $db->transStatus()) {
            $db->transRollback();

            return false;
        }

        return $db->transCommit();
    }

    private function sendEmail(array $user, string $token): void
    {
        try {
            $email = service('email', null, false);
            $email->setTo($user['email_id']);
            $email->setSubject('Reset your Orvexa password');
            $email->setMailType('html');
            $email->setMessage(view('mailTemplate/passwordReset', [
                'firstName'  => $user['first_name'],
                'username'   => $user['external_username'],
                'resetUrl'   => base_url('reset-password/' . $token),
                'ttlMinutes' => self::TTL_MINUTES,
            ]));
            if (! $email->send(false)) {
                log_message('error', 'Password reset email was rejected for user ' . (int) $user['user_id'] . '.');
            }
        } catch (Throwable $e) {
            log_message('error', 'Password reset email failed: ' . $e->getMessage());
        }
    }
}
