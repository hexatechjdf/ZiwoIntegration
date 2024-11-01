<?php

namespace App\Http\Controllers;

use App\Helper\CRM;
use App\Models\ZiwoDetail;
use Illuminate\Http\Request;
use App\Http\Requests\ZiwoTokenRequest;
use App\Services\ZiwoService;
use App\Repositories\UserRepository;
use App\Http\Requests\CallRequest;
use App\Jobs\SubmitCallResponseJob;
use App\Models\CallLog;
use App\Models\User;
use App\Services\CallService;
use Illuminate\Support\Facades\Auth;

class ZiwoDetailController extends Controller
{
    protected $ziwoService;
    protected $userRepository;
    protected $callService;

    public function __construct(ZiwoService $ziwoService, UserRepository $userRepository, CallService $callService)
    {
        $this->ziwoService = $ziwoService;
        $this->userRepository = $userRepository;
        $this->callService = $callService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = loginUser();
        $ziwo_details = ZiwoDetail::whereNull('location_id')->first();
        return view('admin.ziwo.index', compact('ziwo_details'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $ziwo_details  = ZiwoDetail::where('id',$request->id)->first();
        if(!$ziwo_details)
        {
            $ziwo_details = new ZiwoDetail();
        }
        $ziwo_details->username = $request->username;
        $ziwo_details->password = $request->password;
        $ziwo_details->ziwo_account_name = $request->ziwo_account_name;
        $ziwo_details->endpoint = $request->endpoint;
        $ziwo_details->save();
        return response()->json(['success' => true, 'message' => 'Data saved successfully']);
        
    }

    /**
     * Display the specified resource.
     */
    public function show(ZiwoDetail $ziwoDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ZiwoDetail $ziwoDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ZiwoDetail $ziwoDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ZiwoDetail $ziwoDetail)
    {
        //
    }

    public function getToken(ZiwoTokenRequest $request)
    {
        $company = User::first();
       
        $locationId = $request->get('location_id');

        // Handle location-based user creation if locationId exists
        if ($locationId) {
            $this->userRepository->findOrCreateLocationUser($locationId);
        }

        // Get the token from the service layer
        $tokenResponse = $this->ziwoService->getToken($company->id, $locationId);

        if ($tokenResponse) {
            return $tokenResponse; // Return token as JSON response
        }

        return response()->json(['error' => 'Failed to authenticate or retrieve token.'], 404);
    }

    public function submitCallResponse(Request $request)
    {
        SubmitCallResponseJob::dispatch($request->all(),null);
        return response()->json([
            'message' => 'Call processed successfully.',
        ], 200);
    }
     public function deleteCallLogs(Request $request)
    {
        $days_to_delete = CRM::getDefault('call_logs_days');
        if($days_to_delete > 0 )
        {
            $date_threshold = now()->subDays($days_to_delete);
            CallLog::where('created_at', '<', $date_threshold)->delete();
            return response()->json(['message' => 'Old call logs deleted successfully.']);
        }
        return response()->json(['message' => 'No logs deleted. Days to delete must be greater than zero.']);
    }
}
