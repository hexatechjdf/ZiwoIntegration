<?php

namespace App\Http\Controllers;

use App\Http\Requests\ZiwoTokenRequest;
use DataTables;

use App\Models\User;
use App\Models\ZiwoDetail;
use App\Repositories\UserRepository;
use App\Services\ZiwoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    protected $ziwoService;
    protected $userRepository;

    public function __construct(ZiwoService $ziwoService, UserRepository $userRepository)
    {
        $this->ziwoService = $ziwoService;
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       
        return view('admin.location.index');
    }
    public function getTableData(Request $req)
    {
        $items = User::whereNotNull('location_id')->where('role', User::ROLE_LOCATION);
        return Datatables::eloquent($items)
        ->editColumn('action', function ($user) {
            return '<button class="btn btn-primary btn-toggle-integration" data-id="' . $user->id . '" data-status="' . ($user->integration_status ? 'off' : 'on') . '">
                ' . ($user->integration_status ? 'Integration Off' : 'Integration On') . '
            </button>';
        })
        ->setRowId(function ($item) {
            return "row_" . $item->id;
        })
        ->rawColumns(['action'])
        ->make(true);
    }
  
    public function toggleIntegration(Request $request, $id)
    {
        $location = User::find($id);

        if ($location) {
            // Toggle the integration status
            $location->integration_status = !$location->integration_status;
            $location->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
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
        $forceChange = $request->forceChange ??false;
        $tokenResponse = $this->ziwoService->getToken($company->id, $locationId , $forceChange);

        if ($tokenResponse) {
            return $tokenResponse; // Return token as JSON response
        }

        return response()->json(['error' => 'Failed to authenticate or retrieve token.'], 404);
    }
}
