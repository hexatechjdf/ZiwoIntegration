<?php

namespace App\Console;

use App\Jobs\ProcessRefreshToken;
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
        $schedule->command('queue:work --queue="' . env('JOB_QUEUE_TYPE', 'default') . '" --sleep=1')
        ->withoutOverlapping();
        $schedule->job(new ProcessRefreshToken())->everyFiveMinutes();
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
