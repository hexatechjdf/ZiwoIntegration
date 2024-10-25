<?php

namespace App\Http\Controllers;

use App\Helper\Dropshipzone;
use App\Models\Product;
use App\Models\User;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CrmWebhookController extends Controller
{

    public function __construct()
    {
        $this->middleware('web');
    }
    public function callback(Request $request)
    {
        $crm_webhook_type = $request->type;
        if($crm_webhook_type == 'LocationCreate')
        {
            $location = User::where('location_id', $request->id??0)->first();
            if(!$location){$location = new User();}
            $location->name = $request->name ?? 'Test User of '.$request->id;
            $location->email = $request->email ?? $location . '@presave.net';
            $location->password = bcrypt($request->id);
            $location->role = User::ROLE_LOCATION;
            $location->ghl_api_key = '-';
            $location->location_id = $request->id ?? 0; 
            $location->save();
        }
        \Log::info("Webhook--> " . json_encode($request->all()));
    }
}
