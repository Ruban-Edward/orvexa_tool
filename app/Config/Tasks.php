<?php

/**
 * Tasks.php
 *
 * @author    Gokul
 * @category  Config
 * @created   25 July 2024
 * @purpose  this class is used to write the all cron tasks.
 */

declare (strict_types = 1);

/**
 * This file is part of CodeIgniter Tasks.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Config;

use App\Services\EmailRemainder;
use CodeIgniter\Tasks\Config\Tasks as BaseTasks;
use CodeIgniter\Tasks\Scheduler;

class Tasks extends BaseTasks
{
    /**
     * --------------------------------------------------------------------------
     * Should performance metrics be logged
     * --------------------------------------------------------------------------
     *
     * If true, will log the time it takes for each task to run.
     * Requires the settings table to have been created previously.
     */
    public bool $logPerformance = false;

    /**
     * --------------------------------------------------------------------------
     * Maximum performance logs
     * --------------------------------------------------------------------------
     *
     * The maximum number of logs that should be saved per Task.
     * Lower numbers reduced the amount of database required to
     * store the logs.
     */
    public int $maxLogsPerTask = 10;

    /**
     * This function is used to handle all the cron tasks.
     */
    public function init(Scheduler $schedule)
    {
        // Define a scheduled task to send email reminders
        $schedule->call(function () {
            $remainderMail = new EmailRemainder();
            $result = $remainderMail->remainderMail();
            echo $result;
        })->everyFifteenMinutes();

    }
}
