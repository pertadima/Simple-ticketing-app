<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrdersResource;
use App\Models\Orders;
use App\Models\Tickets;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tickets' => 'required|array|min:1',
            'tickets.*.ticket_id' => 'required|exists:tickets,ticket_id',
            'tickets.*.quantity' => 'required|integer|min:1',
        ]);
    
        return DB::transaction(function () use ($request) {
            $user = $request->user();
            $orderData = $this->validateAndProcessOrder($request->tickets, $user);
    
            $order = Orders::create([
                'user_id' => $user->user_id,
                'total_amount' => $orderData['total'],
                'status' => 'pending'
            ]);
    
            $order->orderDetails()->createMany($orderData['items']);
    
            return new OrdersResource($order->load('orderDetails.ticket'));
        });
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

    private function validateAndProcessOrder(array $tickets, Users $user)
    {
        $total = 0;
        $items = [];
        $errors = [];

        foreach ($tickets as $index => $item) {
            $ticket = Tickets::findOrFail($item['ticket_id']);

            // Check ticket availability
            if ($ticket->quota < $item['quantity']) {   
                $errors[] = "Ticket ID {$ticket->ticket_id} has only {$ticket->quota} available";
            }

            // ID verification check
            if ($ticket->requires_id_verification && !$user->id_verified) {
                $errors[] = "Ticket ID {$ticket->ticket_id} requires ID verification";
            }

            // Age verification
            if ($ticket->min_age && $user->age() < $ticket->min_age) {
                $errors[] = "Ticket ID {$ticket->ticket_id} requires minimum age of {$ticket->min_age}";
            }

            if (!empty($errors)) {
                abort(422, ['errors' => $errors]);
            }

            $subtotal = $ticket->price * $item['quantity'];
            $total += $subtotal;

            $items[] = [
                'ticket_id' => $ticket->ticket_id,
                'quantity' => $item['quantity'],
                'price' => $ticket->price,
                'subtotal' => $subtotal
            ];

            // Update ticket inventory
            $ticket->decrement('quota', $item['quantity']);
            $ticket->increment('sold_count', $item['quantity']);
        }

        return [
            'total' => $total,
            'items' => $items
        ];
    }
}
