<?php

namespace App\Http\Controllers;

use App\Helper\Dropshipzone;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function __construct()
    {
        $this->middleware('web');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = loginUser();
        // Simulate third-party API response for categories (mock data)
        $categories = [];
        $response = Dropshipzone::makeRequest($user, 'get', 'categories');
        $result = json_decode($response, true);
        if (isset($result)) {
            $categories = $result;
        }
        // Filter categories based on the search term
        $search = $request->input('search');
        if ($search) {
            $categories = array_filter($categories, function ($category) use ($search) {
                return stripos($category['name'], $search) !== false;
            });
        }

        // Paginate the categories manually
        $page = $request->input('page', 1);
        $perPage = 10;
        $paginatedCategories = array_slice($categories, ($page - 1) * $perPage, $perPage);

        // Build the response format for Select2
        return response()->json([
            'data' => [
                'categories' => $paginatedCategories,
                'current_page' => $page,
                'total_pages' => ceil(count($categories) / $perPage),
            ],
        ]);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
