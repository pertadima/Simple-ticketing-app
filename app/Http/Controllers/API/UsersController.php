<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrdersResource;
use Illuminate\Http\Request;
use App\Http\Resources\UsersResource;
use App\Models\ApiUser;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**´
     * Display the specified resource.
     */
    public function show(ApiUser $users)
    {
        return new UsersResource($users);
    }

    public function orders($userId)
    {
        $user = ApiUser::findOrFail($userId);
        $orders = $user->orders;

        return response()->json([
            'data' => [
                'orders' => OrdersResource::collection($orders)
            ]
        ]);
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
