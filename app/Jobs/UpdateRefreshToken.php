<?php

namespace App\Jobs;

use App\Models\CrmAuths;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateRefreshToken implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $companyId;

    /**
     * Create a new job instance.
     */
    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $rf = CrmAuths::where('user_id',$this->companyId)->first();
            if($rf){
                $status = $rf->urefresh();
                if($status==500){
                    dispatch((new UpdateRefreshToken($this->companyId))->onQueue(env('JOB_QUEUE_TYPE'))->delay(Carbon::now()->addMinutes(5)));
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}