<?php

namespace App\Jobs;

use App\Helper\CRM;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class PushCRMTagJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    public function __construct(array $data)
    {
        $this->data = $data;
     
    }

    public function handle()
    {
        // Add tags to CRM for this contact
        try {
            CRM::addContactJobs($this->data['company_id'], $this->data['crm_contact_id'], $this->data['tags'], $this->data['custom_fields'] ?? []);
        } catch (\Exception $e) {
            Log::info('TagJobFailed',$e->getMessage());
            throw $e;
        }
    }
}
