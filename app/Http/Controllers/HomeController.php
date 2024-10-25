<?php

namespace App\Http\Controllers;

use App\Helper\CRM;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function send_notes(Request $request)
    {
        $user = Auth::user();
        $contactId = $request->contactId;
        $locationId = $request->locationId;
        if($contactId && $locationId)
        {
            $body = [
                "body" =>  $request->body
            ];
            $resp = CRM::crmV2($user->id, `contacts/`.$contactId.`/notes`,'post',json_encode($body),[],true,$locationId);
            if ($resp && property_exists($resp, 'success')) {
                return response()->json(['status' => true], 400);
            }
            return response()->json(['status' =>false], 200);
        }
    }
    public function location_info(Request $request)
    {
        $user = Auth::user();
        $locations = User::where('role',User::ROLE_LOCATION)->whereNotNull('location_id')->pluck(['location_id','integration_status']);
        return response()->json(['status' => true, 'message' => 'location information.','data' => $locations], 200);
    }
    public function addLocation(Request $request)
    {
        $user = Auth::user();
        $locations = [];
        $resp = CRM::agencyV2($user->id, 'locations/search', 'get', '', [], false);
        if ($resp && property_exists($resp, 'locations')) {
            $locations = $resp->locations;
        }
        foreach($locations  as $loc)
        {
            $location = User::where('location_id', $loc->id)->first();
            if(!$location){ $location = new User();}
            $location->name = $loc->name;
            $location->email = $loc->email;
            $location->location_id = $loc->id;
            $location->role = User::ROLE_LOCATION;
            $location->password = bcrypt($loc->id);
            $location->integration_status = 0;
            $location->ghl_api_key = '-';
            $location->save();
        }
        return response()->json(['status' => true, 'message' => 'location information.', 'data' => $locations], 200);
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = loginUser();
        if ($user->role == 1) {
            return redirect()->route("admin.setting");
        } elseif ($user->role == 2) {
            return redirect()->route("admin.integration");
        }
    }
    public function connect(Request $request)
    {
        return view('connect');
    }
}
