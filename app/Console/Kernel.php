<?php

namespace App\Console;

use App\Jobs\UserConnectionLibraryJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('queue:work', [
            //'--max-time' => 300,
            '--queue'=>'default',
            '--timeout'=>550,
        ])->withoutOverlapping();
        $schedule->job(new UserConnectionLibraryJob())->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');

    }
}
