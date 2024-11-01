<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\CallService;
class SubmitCallResponseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $requestData;
    protected $company;
    /**
     * Create a new job instance.
     */
    public function __construct(array $requestData , $company = null)
    {
        $this->requestData = $requestData;
        $this->company = $company;
    }

    /**
     * Execute the job.
     */
    public function handle(CallService $callService)
    {
        \Log::info("Processing call response job...");
        $companyObj = $this->company;
        if(!isset($companyObj))
        {
            $companyObj = User::first();
        }
        $result = $callService->handleCall($this->requestData, $companyObj);
        \Log::info("Call processed successfully.", ['result' => $result]);
    }
}
