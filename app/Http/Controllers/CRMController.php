<?php

namespace App\Http\Controllers;

use App\Models\Locations;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CRMController extends Controller
{
    public function crmCallback(Request $request)
    {
        $code = $request->code ?? null;
        if ($code) {
            $user_id = null;
            if (auth()->check()) {
                $user = loginUser(); //auth user
                $user_id = $user->id;
            }else
            {
                $user = User::first();
                Auth::login($user);
                $user_id = $user->id;
            }
            else
            {
                $user = User::first();
                Auth::login($user);
                $user_id = $user->id;
            }
            $code = \CRM::crm_token($code, '');
            $code = json_decode($code);
            $user_type = $code->userType ?? 'company';
            $main = route('admin.setting'); //change with any desired
            if ($user_type) {
                $token = $user->crmauth ?? null;
                list($connected, $con) = \CRM::go_and_get_token($code, '', $user_id, $token);
                if ($connected) {
                    if(strtolower($user_type) == 'company') 
                    {
                        return redirect($main)->with('success', 'Connected Successfully');
                        return response()->json(['message' => 'Connected Successfully','data' => 'Connected']);
                    }
                    return redirect($main)->with('success', 'Connected Successfully');
                }
                if (strtolower($user_type) == 'company') {
                    return response()->json(['message' => 'Unable to connect to the company']);
                }
                return redirect($main)->with('error', json_encode($code));
            }
            return response()->json(['message' => 'Not allowed to connect']);
        }
    }

    public function crmFetchDetail(Request $request)
    {
        $user = loginUser();
        $token = $user->crmauth ?? null;
        $status = false;
        $message = 'Connect to Agency/CRM';
        $type = '';
        $detail = '';

        if ($token) {

            $type = $token->user_type;
            if ($type == \CRM::$lang_com) {
                $resp = \CRM::agencyV2($user->id, 'companies/' . $token->company_id, 'get', '', [], false, $token);
                if ($resp && property_exists($resp, 'company')) {
                    $resp = $resp->company;
                    $status = true;
                    $detail = 'Agency -> ' . $resp->id . ' - ' . $resp->name;
                }
            } else {
                $resp = \CRM::crmV2($user->id, 'locations/' . $token->location_id, 'get', '', [], false, $token->location_id, $token);
                if ($resp && property_exists($resp, 'location')) {
                    $resp = $resp->location;
                    $status = true;
                    $detail = \CRM::$lang_loc . ' -> ' . $resp->id . ' - ' . $resp->name . ' - ' . \CRM::$lang_com . ' : ' . ($resp->companyId ?? "");
                }
            }
        }
        if ($status) {
            $message = 'Re-' . $message;
        }
        return response()->json(['status' => $status, 'message' => $message, 'type' => $type, 'detail' => $detail]);

    }
    public function fetchLocations(Request $request)
    {

        //this code is only useable if need to store locations in database or connect with already saved locations in database using agency token
        $user = loginUser();
        $token = $user->crmauth ?? null;
        $status = false;
        $message = 'Connect to Agency First';
        $type = '';
        $detail = '';
        $load_more = false;
        if ($token) {

            $type = $token->user_type;
            $query = '';
            $limit = 100;
            if ($request->has('page')) {
                if ($request->page < 2) {
                    $request->page = 0;
                }
                $query .= 'skip=' . ($limit * $request->page) . '&';
            }
            $query = 'locations/search?' . $query . 'limit=' . $limit . '&companyId=' . $token->company_id;

            if ($type !== \CRM::$lang_com) {
                return response()->json(['status' => $status, 'message' => $message, 'type' => $type, 'detail' => $detail, 'loadMore' => $load_more]);
            } else {
                $detail = \CRM::agencyV2($user->id, $query, 'get', '', [], false, $token);
            }

            if ($detail && property_exists($detail, 'locations')) {
                $detail = $detail->locations;
                $load_more = count($detail) > $limit - 1;
                $ids = collect($detail)->pluck('id')->toArray();
                $locs_already = []; // Locations::whereIn('location_id', $ids)->pluck('location_id')->toArray();
                foreach ($detail as $det) {
                    if (!in_array($det->id, $locs_already)) {
                        //saveLocs($det, $user->id);
                    }
                }
                $status = true;
            }

        }

        return response()->json(['status' => $status, 'message' => $message, 'type' => $type, 'detail' => $detail, 'loadMore' => $load_more]);

    }
}
