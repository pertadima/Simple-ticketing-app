<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventsResource;
use Illuminate\Http\Request;
use App\Models\Events;
use App\Models\Seats;
use App\Models\EventTicketType;

class EventsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Events::with(['tickets.category', 'tickets.type', 'categories']);
        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }
        
        $events = $query->orderBy('date', 'asc')->paginate(10);

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
    public function show(Events $event)
    {
        $tickets = $event->tickets()
        ->with(['category', 'type'])
        ->get();

        return response()->json([
            'data' => [
                'event' => new EventsResource($event),
                'tickets' => $this->groupedCategory($tickets)
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

    private function groupedCategory($tickets) {
        return $tickets->groupBy('category.name')->map(function ($items, $category) {
            return [
                'category' => $category,
                'tickets' => $items->map(function ($ticket) {
                // Fetch has_seat_number from event_ticket_types table
                $hasSeatNumber = EventTicketType::where('event_id', $ticket->event_id)
                    ->where('type_id', $ticket->type_id)
                    ->value('has_seat_number');

                    return [
                        'id' => $ticket->ticket_id,
                        'type' => $ticket->type->name,
                        'price' => $ticket->price,
                        'quota' => $ticket->quota,
                        'requires_id_verification' => (bool)$ticket->requires_id_verification,
                        'has_seat_number' => (bool)$hasSeatNumber
                    ];
                })
            ];
        })->values();
    }

    public function showAvailableSeats($eventId, $typeId)
    {
        $seats = Seats::where('event_id', $eventId)
            ->where('type_id', $typeId)
            ->get(['seat_id', 'seat_number', 'is_booked'])
            ->map(function ($seat) {
                $seat->is_booked = (bool) $seat->is_booked;
                return $seat;
            });

        return response()->json([
            'data' => $seats
        ]);
    }
}
