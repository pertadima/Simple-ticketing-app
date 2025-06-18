<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventsCategoryResource;
use App\Models\EventCategories;
use Illuminate\Http\Request;

class EventsCategoryController extends Controller
{
    public function index(Request $request)
    {
         return response()->json([
            'data' => [
                'categories' => EventsCategoryResource::collection(EventCategories::all())
            ]
        ]);
    }
}
