<?php

namespace App\Http\Controllers\API;

use App\Enums\OrderStatus;
use App\Enums\IdCardType;
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
        $errors = [];
        $items = [];
        $total = 0;
    
        // Pre-check email verification
        if (!$user->email_verified) {
            return ['errors' => ["You must verify your email before making a purchase"]];
        }
    
        // Aggregate quantities per ticket and get all ticket IDs
        $ticketQuantities = [];
        $ticketIds = [];
        foreach ($tickets as $item) {
            $ticketId = $item['ticket_id'];
            $ticketQuantities[$ticketId] = ($ticketQuantities[$ticketId] ?? 0) + $item['quantity'];
            $ticketIds[] = $ticketId;
        }
    
        // Get all tickets with lock to prevent race conditions
        $ticketsCollection = Tickets::with(['type', 'category'])
            ->whereIn('ticket_id', array_unique($ticketIds))
            ->lockForUpdate()
            ->get()
            ->keyBy('ticket_id');
    
        // Validate ticket quotas first
        foreach ($ticketQuantities as $ticketId => $quantity) {
            $ticket = $ticketsCollection[$ticketId] ?? null;
            
            if (!$ticket) {
                $errors[] = "Ticket ID {$ticketId} does not exist";
                continue;
            }
    
            if ($ticket->quota < $quantity) {
                $errors[] = "Ticket ID {$ticketId} has only {$ticket->quota} available";
            }
        }
    
        // Validate ID requirements and unique card numbers
        $idCards = [];
        foreach ($tickets as $index => $item) {
            $ticket = $ticketsCollection[$item['ticket_id']];
            
            if ($ticket->requires_id_verification) {
                $cardNumber = $item['card_number'] ?? null;
                $cardType = $item['card_type'] ?? null;
    
                // Validate card presence and type
                if (empty($cardNumber)) {
                    $errors[] = "Card number required for {$ticket->type->name} - {$ticket->category->name}";
                }
                
                if (empty($cardType) || !in_array($cardType, IdCardType::values())) {
                    $validTypes = implode(', ', IdCardType::values());
                    $errors[] = "Invalid card type for ticket ID {$ticket->ticket_id}. Valid types: {$validTypes}";
                }
    
                // Check quantity limit for ID-required tickets
                if ($item['quantity'] > 1) {
                    $errors[] = "Ticket ID {$ticket->ticket_id} (ID required) limited to 1 per purchase";
                }
    
                // Track card numbers per ticket type
                $key = "{$cardNumber}";
                if (isset($idCards[$key])) {
                    $errors[] = "Duplicate card number {$cardNumber}";
                }
                $idCards[$key] = true;
            }
        }
    
        if (!empty($errors)) {
            return ['errors' => $errors];
        }
    
        // Process valid order
        foreach ($tickets as $item) {
            $ticket = $ticketsCollection[$item['ticket_id']];
            $quantity = $item['quantity'];
            
            // Create order item
            $subtotal = $ticket->price * $quantity;
            $total += $subtotal;
            
            $items[] = [
                'ticket_id' => $ticket->ticket_id,
                'quantity' => $quantity,
                'price' => $ticket->price,
                'subtotal' => $subtotal
            ];
    
            // Update inventory
            $ticket->decrement('quota', $quantity);
            $ticket->increment('sold_count', $quantity);
        }
    
        return [
            'total' => $total,
            'items' => $items,
            'card_type' => $item['card_type'] ?? null,
            'card_number' => $item['card_number'] ?? null
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
