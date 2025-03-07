<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventsResource;
use Illuminate\Http\Request;
use App\Models\Events;

class EventsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Events::with(['tickets.category', 'tickets.type'])
            ->orderBy('date', 'asc')
            ->paginate(10);

        return EventsResource::collection($events);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $id = $request->input('id');
        $event = Events::with(['tickets.category', 'tickets.type'])
        ->findOrFail($id);

        return new EventsResource($event);
   }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
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
