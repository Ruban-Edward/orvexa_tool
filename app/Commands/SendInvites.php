<?php

namespace App\Commands;

use App\Services\InviteMailer;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Sends user invite emails that are still queued (for example when the web
 * request could not send them). Safe to run from cron, e.g. every 5 minutes.
 */
class SendInvites extends BaseCommand
{
    protected $group = 'Tasks';
    protected $name = 'invites:send';
    protected $description = 'Send queued user invite emails.';

    public function run(array $params)
    {
        if (!InviteMailer::isConfigured()) {
            CLI::error('Email is not set up (Config\Email: fromEmail / SMTPHost).');

            return EXIT_ERROR;
        }
        [$sent, $failed] = (new InviteMailer())->sendPending((int) ($params[0] ?? 200));
        CLI::write("Invites sent: {$sent}, failed: {$failed}");

        return EXIT_SUCCESS;
    }
}
