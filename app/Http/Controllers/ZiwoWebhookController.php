<?php

namespace App\Http\Controllers;

use App\Helper\CRM;
use App\Helper\Dropshipzone;
use App\Models\CallLog;
use App\Models\LocationPhone;
use App\Models\Product;
use App\Models\User;
use App\Models\WebhookLog;
use App\Services\CallService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ZiwoWebhookController extends Controller
{
    protected $callService;
    public function __construct(CallService $callService)
    {
        $this->callService = $callService;
    }
    public function callback(Request $request)
    {
        $company = User::first();
        $payload = $request->content;
        if(strtolower($payload['direction']) == 'inbound')
        {
            $to_number = $payload['didCalled'];
            $from_number = $payload['calerIDNumber'];
        }
        if (strtolower($payload['direction']) == 'outbound') {
            $from_number = $payload['didCalled'];
            $to_number = $payload['calerIDNumber'];
        }
        $call_logs = CallLog::where("call_id",$payload['call_id'])->first();
        if(!$call_logs)
        {
            $locationPhone = LocationPhone::where('phone', $from_number)->first();
            if($locationPhone)
            {
                $defaultLocationId = $locationPhone->location_id;
            }
            else
            {
                $defaultLocationId = CRM::getDefault('agency_main_location','');
            }
            $callData = [
                'location_id' => $defaultLocationId,
                'call_id' => $payload['callID'],
                'user_id' => $payload['user_id'] ?? 1,
                'user_name' => $payload['user_name'] ?? 'Test Lead',
                'user_email' => $payload['user_email'] ?? 'test@gmail.com',
                'call_duration' => $payload['duration'],
                'call_status' => $payload['call_status'],
                'call_direction' => $payload['direction'] ?? 'outbound',
                'attachment' => $payload['recordingFile'] ?? null,
                'from_phone' => $from_number,
                'contact_phone' => $to_number,
                'contact_id' => '',
                'conversation_id' => '',
                'from_webhook' => true
            ];
            $result = $this->callService->handleCall($callData, $company);
        }
        \Log::info("Webhook--> " . json_encode($request->all()));
    }
}
