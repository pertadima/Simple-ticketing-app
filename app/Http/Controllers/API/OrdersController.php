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
use App\Models\VoucherRedemptions;
use App\Models\Vouchers;
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
            'tickets.*.quantity' => 'required|integer|min:1',
            'voucher_code' => 'nullable|string|max:255'
        ]);
    
        return DB::transaction(function () use ($request) {
            $user = $request->user();
            $voucher = null;
            $errors = [];
            $orderData = $this->validateAndProcessOrder($request->tickets, $user, $voucher);
    
            if ($request->filled('voucher_code')) {
                $voucherValidation = $this->validateVoucher($request->voucher_code, $user, $request->tickets);
                
                if (isset($voucherValidation['errors'])) {
                    $errors = array_merge($errors, $voucherValidation['errors']);
                } else {
                    $voucher = $voucherValidation['voucher'];
                }
            }

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
                'status' => OrderStatus::PENDING
            ]);
    
            $order->orderDetails()->createMany($orderData['items']);
    
             // Update voucher usage
            if ($voucher) {
                $voucher->increment('used_count');
                VoucherRedemptions::create([
                    'voucher_id' => $voucher->voucher_id,
                    'user_id' => $user->user_id,
                    'order_id' => $order->order_id
                ]);
            }

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

    private function validateVoucher(?string $code, Users $user, array $tickets): array
    {
        $voucher = Vouchers::where('code', $code)->first();

        if (!$voucher) {
            return ['errors' => ['Invalid voucher code']];
        }

        // Check voucher validity
        if (now()->gt($voucher->valid_until)) {
            return ['errors' => ['Voucher has expired']];
        }

        if ($voucher->used_count >= $voucher->usage_limit) {
            return ['errors' => ['Voucher has been fully redeemed']];
        }

        // Check user redemption limit
        $userRedemptions = VoucherRedemptions::where('voucher_id', $voucher->voucher_id)
            ->where('user_id', $user->user_id)
            ->count();

        if ($userRedemptions >= $voucher->usage_limit) {
            return ['errors' => ['You have already used this voucher']];
        }

        // Check event-specific validation
        if ($voucher->type === 'specific') {
            $eventIds = collect($tickets)
                ->map(fn($t) => Tickets::find($t['ticket_id'])->event_id)
                ->unique();

            if ($eventIds->count() > 1 || $eventIds->first() != $voucher->event_id) {
                return ['errors' => ['Voucher is not valid for selected events']];
            }
        }

        return ['voucher' => $voucher];
    }

    private function validateAndProcessOrder(array $tickets, Users $user, ?Vouchers $voucher)
    {
        if (!$user->email_verified) {
            return ['errors' => ["You must verify your email before making a purchase"]];
        }

        $errors = [];
        $items = [];
        $total = 0;
        $discount = 0;

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

        if ($voucher) {
            $discount = match ($voucher->discount_type) {
                'percentage' => $total * ($voucher->discount / 100),
                'fixed' => min($voucher->discount, $total),
                default => 0
            };
    
            $total -= $discount;
        }
        
        return [
            'total' => $total,
            'items' => $items,
            'id_card_type' => $item['card_type'] ?? null,
            'id_card_number' => $item['card_number'] ?? null
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
            'id_card_type' => $item['card_type'] ?? null,
            'id_card_number' => $item['card_number'] ?? null
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
