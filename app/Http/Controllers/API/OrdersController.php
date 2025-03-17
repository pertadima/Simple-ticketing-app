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
        if (!$user->email_verified) {
            return ['errors' => ["You must verify your email before making a purchase"]];
        }

        $errors = [];
        $items = [];
        $total = 0;

        // 1. Aggregate quantities and get tickets in single collection pipeline
        $ticketData = collect($tickets)
            ->groupBy('ticket_id')
            ->map(function ($group, $ticketId) {
                return [
                    'total_quantity' => $group->sum('quantity'),
                    'items' => $group,
                    'ticket_id' => $ticketId
                ];
            });

        // 2. Get tickets with lock and validate quotas
        $ticketsCollection = Tickets::with(['type', 'category'])
            ->whereIn('ticket_id', $ticketData->keys())
            ->lockForUpdate()
            ->get()
            ->keyBy('ticket_id');

        // 3. Single validation loop combining quota and ID checks
        $idCards = [];
        $ticketData->each(function ($data) use (&$errors, $ticketsCollection, &$idCards) {
            $ticket = $ticketsCollection[$data['ticket_id']] ?? null;
            
            // Ticket existence check
            if (!$ticket) {
                $errors[] = "Ticket ID {$data['ticket_id']} does not exist";
                return;
            }

            // Quota validation
            if ($ticket->quota < $data['total_quantity']) {
                $errors[] = "Ticket ID {$ticket->ticket_id} has only {$ticket->quota} available";
            }

            // ID verification checks
            if ($ticket->requires_id_verification) {
                collect($data['items'])->each(function ($item) use ($ticket, &$errors, &$idCards) {
                    $this->validateIdRequirements($item, $ticket, $errors, $idCards);
                });
            }
        });

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        // 4. Single processing loop with inventory update
        $ticketData->each(function ($data) use (&$items, &$total, $ticketsCollection) {
            $ticket = $ticketsCollection[$data['ticket_id']];
            
            collect($data['items'])->each(function ($item) use ($ticket, &$items, &$total) {
                $this->processItem($item, $ticket, $items, $total);
            });

            $ticket->decrement('quota', $data['total_quantity']);
            $ticket->increment('sold_count', $data['total_quantity']);
        });

        return [
            'total' => $total,
            'items' => $items,
            'card_type' => $item['card_type'] ?? null,
            'card_number' => $item['card_number'] ?? null
        ];
    }

    private function validateIdRequirements($item, $ticket, &$errors, &$idCards)
    {
        $cardNumber = $item['card_number'] ?? null;
        $cardType = $item['card_type'] ?? null;

        // Validate card presence
        if (empty($cardNumber)) {
            $errors[] = "Card number required for {$ticket->type->name} - {$ticket->category->name}";
        }

        // Validate card type
        if (empty($cardType) || !in_array($cardType, IdCardType::values())) {
            $validTypes = implode(', ', IdCardType::values());
            $errors[] = "Invalid card type for ticket ID {$ticket->ticket_id}. Valid types: {$validTypes}";
        }

        // Validate quantity limit
        if ($item['quantity'] > 1) {
            $errors[] = "Ticket ID {$ticket->ticket_id} (ID required) limited to 1 per purchase";
        }

        // Check duplicate cards
        $key = "{$ticket->ticket_id}-{$cardNumber}";
        if (isset($idCards[$key])) {
            $errors[] = "Duplicate card number {$cardNumber} for ticket ID {$ticket->ticket_id}";
        }
        $idCards[$key] = true;
    }

    private function processItem($item, $ticket, &$items, &$total)
    {
        $subtotal = $ticket->price * $item['quantity'];
        $total += $subtotal;

        $items[] = [
            'ticket_id' => $ticket->ticket_id,
            'quantity' => $item['quantity'],
            'price' => $ticket->price,
            'subtotal' => $subtotal
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
