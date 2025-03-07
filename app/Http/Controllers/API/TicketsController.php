<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Events;
use Illuminate\Http\Request;
use App\Http\Resources\EventsResource;
use App\Models\Tickets;

use groupedCategory;

class TicketsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Events $event)
    {
        $tickets = $event->tickets()
        ->with(['category', 'type'])
        ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'event' => new EventsResource($event),
                'tickets' => $this->groupedCategory($tickets)
            ]
        ]);
    }


    private function groupedCategory($tickets) {
        return $tickets->groupBy('category.name')->map(function ($items, $category) {
            return [
                'category' => $category,
                'tickets' => $items->map(function ($ticket) {
                    return [
                        'id' => $ticket->id,
                        'type' => $ticket->type->name,
                        'price' => $ticket->price,
                        'quota' => $ticket->quota,
                        'min_age' => $ticket->min_age,
                        'requires_id_verification' => (bool)$ticket->requires_id_verification
                    ];
                })
            ];
        })->values();
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
