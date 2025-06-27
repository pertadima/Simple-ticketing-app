<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventsCategoryResource;
use App\Http\Resources\EventsResource;
use App\Models\EventCategories;
use App\Models\Events;
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

    public function eventsByCategory($categoryId)
    {
        $events =  Events::with(['tickets.category', 'tickets.type', 'categories'])
            ->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('event_categories.category_id', $categoryId);
            })
            ->orderBy('date', 'asc')
            ->paginate(10);

        return response()->json([
            'data' => [
                'events' => EventsResource::collection($events)
            ],
            'meta' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
                'is_next_page' => $events->currentPage() < $events->lastPage(),
                'is_prev_page' => $events->currentPage() > 1,
            ]
        ]);
    }
}
