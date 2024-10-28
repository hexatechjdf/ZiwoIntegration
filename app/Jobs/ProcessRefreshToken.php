<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\Dispatchable;


class ProcessRefreshToken implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $page;

    /**
     * Create a new job instance.
     */
    public function __construct($page = 1)
    {
        $this->page = $page;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $limit = 40;
            $currentPage  = $this->page - 1;
            $skip = $currentPage * $limit;
            $rl = User::where('role', User::ROLE_COMPANY)->skip($skip)->take($limit)->get();
            if ($rl->isNotEmpty()) {
                $env = env('JOB_QUEUE_TYPE');
                foreach ($rl as $r) {
                    dispatch((new UpdateRefreshToken($r->id))->onQueue($env)->delay(Carbon::now()->addSeconds(2)));
                }
                dispatch((new ProcessRefreshToken($this->page + 1))->onQueue($env)->delay(Carbon::now()->addSeconds(2)));
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
?>