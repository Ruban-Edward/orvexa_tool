<?php

namespace App\Commands;

use App\Services\InviteMailer;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Gives an account a one-time temporary password from the server's command line, e.g. the
 * first Super Admin of a fresh install (the seed data has no usable passwords). The user must
 * choose a new password at the next sign-in.
 */
class SetPassword extends BaseCommand
{
    protected $group = 'Orvexa';
    protected $name = 'orvexa:set-password';
    protected $description = 'Set a one-time temporary password for a user.';
    protected $usage = 'orvexa:set-password <username>';

    public function run(array $params)
    {
        $username = trim((string) ($params[0] ?? CLI::prompt('Username')));
        $db = db_connect();
        $user = $db->table('orvexa_user')
            ->select('user_id, first_name')
            ->where('LOWER(external_username)', strtolower($username))
            ->where('is_deleted', 'N')
            ->get()->getRowArray();
        if (!$user) {
            CLI::error("No active user named \"{$username}\".");

            return EXIT_ERROR;
        }

        $password = InviteMailer::temporaryPassword();
        $fields = ['password' => password_hash($password, PASSWORD_DEFAULT), 'updated_date' => date('Y-m-d H:i:s')];
        if ($db->fieldExists('password_reset_required', 'orvexa_user')) {
            $fields['password_reset_required'] = 'Y';
        }
        $db->table('orvexa_user')->where('user_id', $user['user_id'])->update($fields);

        CLI::write("Temporary password for {$username}: {$password}", 'green');
        CLI::write('It works once: the user is asked to choose a new password after signing in.');

        return EXIT_SUCCESS;
    }
}
