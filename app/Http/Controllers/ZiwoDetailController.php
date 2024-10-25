<?php

namespace App\Http\Controllers;

use App\Models\ZiwoDetail;
use Illuminate\Http\Request;
use App\Http\Requests\ZiwoTokenRequest;
use App\Services\ZiwoService;
use App\Repositories\UserRepository;
use App\Http\Requests\CallRequest;
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
        $ziwo = new ZiwoDetail();
        $ziwo->username = $request->username;
        $ziwo->password = $request->password;
        $ziwo->endpoint = $request->endpoint;
        $ziwo->save();
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
        $company = Auth::user();
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
        // Call service to process the payload
        $company = Auth::user() ?? User::first();
        $result = $this->callService->handleCall($request->all(), $company);
        return response()->json([
            'message' => 'Call processed successfully.',
            'data' => $result,
        ], 200);
    }
}