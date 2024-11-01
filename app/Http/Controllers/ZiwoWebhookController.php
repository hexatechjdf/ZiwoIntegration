<?php

namespace App\Http\Controllers;

use App\Helper\CRM;
use App\Jobs\SubmitCallResponseJob;
use App\Models\CallLog;
use App\Models\LocationPhone;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\CallService;
use Carbon\Carbon;

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
            $callStatus = [
                "CALL_REJECTED" => 'failed',
                "ORIGINATOR_CANCEL" =>  "canceled",
                "NORMAL_CLEARING" =>  "completed"
            ];
            $currentPayloadCallStatus = $payload['hangupCause'] ?? 'NORMAL_CLEARING';
            
            $callData = [
                'location_id' => $defaultLocationId,
                'call_id' => $payload['callID'],
                'user_id' => $payload['user_id'] ?? 1,
                'user_name' => $payload['user_name'] ?? 'Test Lead',
                'user_email' => $payload['user_email'] ?? 'test@gmail.com',
                'call_duration' => $payload['duration'],
                'call_status' =>  $callStatus[$currentPayloadCallStatus] ?? "completed",
                'call_direction' => $payload['direction'] ?? 'outbound',
                'attachment' => $payload['recordingFile'] ?? null,
                'from_phone' => $from_number,
                'contact_phone' => $to_number,
                'contact_id' => '',
                'conversation_id' => '',
                'from_webhook' => true
            ];
            SubmitCallResponseJob::dispatch($callData, $company)->delay(Carbon::now()->addMinutes(10));
        }
        \Log::info("Webhook--> " . json_encode($request->all()));
    }
}
