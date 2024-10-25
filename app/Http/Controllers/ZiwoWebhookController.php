<?php

namespace App\Http\Controllers;

use App\Helper\Dropshipzone;
use App\Models\CallLog;
use App\Models\Product;
use App\Models\User;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ZiwoWebhookController extends Controller
{

    public function __construct()
    {
        $this->middleware('web');
    }
    public function callback(Request $request)
    {
        $payload = $request->direction;
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
            
        }
        \Log::info("Webhook--> " . json_encode($request->all()));
    }
}
