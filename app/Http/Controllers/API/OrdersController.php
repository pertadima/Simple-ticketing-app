<?php

namespace App\Http\Controllers\API;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrdersResource;
use App\Models\Orders;
use App\Models\Tickets;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\ApiErrorHelper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrdersController extends Controller
{
    use AuthorizesRequests;
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
            'tickets.*.quantity' => 'required|integer|min:1'
        ]);
    
        return DB::transaction(function () use ($request) {
            $user = $request->user();
            $orderData = $this->validateAndProcessOrder($request->tickets, $user);
    
            if (isset($orderData['errors'])) {
                $apiErrorHelper = new ApiErrorHelper();
                return response()->json($apiErrorHelper->formatError(
                    title: 'Failed to create order',
                    status: 422,
                    detail: 'Order cannot be created due to the following errors',
                    errors: $orderData['errors']
                ), 422);
            }
            
            $order = Orders::create([
                'user_id' => $user->user_id,
                'total_amount' => $orderData['total'],
                'status' => OrderStatus::PENDING,
                'id_card_type' => $orderData['card_type'],
                'id_card_number' => $orderData['card_number']
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

               // ID verification check
        if (!$user->email_verified) {
            $errors[] = "You must verified your email first before making a purchase";
            return ['errors' => $errors];
        }

        foreach ($tickets as $index => $item) {
            $ticket = Tickets::findOrFail($item['ticket_id']);

            // Check ticket availability
            if ($ticket->quota < $item['quantity']) {   
                $errors[] = "Ticket ID {$ticket->ticket_id} has only {$ticket->quota} available";
            }

            if ($ticket->requires_id_verification) {                
                if ($item['quantity'] > 1) {
                    $errors[] = "Ticket ID {$ticket->ticket_id} with ID verification can only be purchased one at a time";
                }

                if (empty($item['card_number'])) {
                    $errors[] = "Card number is required for ticket type {$ticket->type->name} category {$ticket->category->name}";
                }

                if (empty($item['card_type']) || !in_array($item['card_type'], ['driving_license', 'national', 'passport'])) {
                    $errors[] = "Card type is required and must be one of 'driving_license', 'national', or 'passport' for ticket ID {$ticket->ticket_id}";
                }
            }

            if (!empty($errors)) {
                return ['errors' => $errors];
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
            'items' => $items,
            'card_type' => $item['card_type'] ?? null,
            'card_number' => $item['card_number'] ?? null,
        ];
    }

    public function markAsPaid(Orders $order)
    {
        $this->authorize('update', $order);

        $apiErrorHelper = new ApiErrorHelper();
        if ($order->status !== OrderStatus::PENDING) {
            return response()->json($apiErrorHelper->formatError(
                title: 'Invalid Order Status',
                status: 422,
                detail: 'Order cannot be paid in its current status',
            ), 422);
        }

        return DB::transaction(function () use ($order) {
            $order->update([
                'status' => OrderStatus::PAID,
                'paid_at' => now()
            ]);

            return new OrdersResource($order->fresh()->load('orderDetails.ticket'));
        });
    }
}
