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
use App\Models\Seats;
use App\Models\EventTicketType;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;

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
    
            if ($request->filled('voucher_code')) {
                $voucherValidation = $this->validateVoucher($request->voucher_code, $user, $request->tickets);
                
                if (isset($voucherValidation['errors'])) {
                    $errors = array_merge($errors, $voucherValidation['errors']);
                } else {
                    $voucher = $voucherValidation['voucher'];
                }
            }

            $orderData = $this->validateAndProcessOrder($request->tickets, $user, $voucher);

            if (isset($orderData['errors'])) {
                $apiErrorHelper = new ApiErrorHelper();
                return response()->json($apiErrorHelper->formatError(
                    title: 'Failed to create order',
                    status: 422,
                    detail: 'Order cannot be created due to the following errors',
                    errors: $orderData['errors']
                ), 422);
            }
            
            Log::info("Order data processed successfully for user: {$user->user_id} and discount: {$orderData['discount']}");
            $order = Orders::create([
                'user_id' => $user->user_id,
                'total_amount' => $orderData['total'],
                'discount_amount' => $orderData['discount'],
                'status' => OrderStatus::PENDING
            ]);
    
            $order->orderDetails()->createMany($orderData['items']);
    
             // Update voucher usage
            if ($voucher) {
                $voucher->increment('used_count');
                VoucherRedemptions::create([
                    'voucher_id' => $voucher->id,
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

             // Seat number validation
            $hasSeatNumber = EventTicketType::where('event_id', $ticket->event_id)
                ->where('type_id', $ticket->type_id)
                ->value('has_seat_number');

            if ($hasSeatNumber) {
                foreach ($data['items'] as $item) {
                    if (empty($item['seat_number'])) {
                        $errors[] = "Seat number required for ticket ID {$ticket->ticket_id}";
                    } else {
                        // Check if seat is available
                        $seat = Seats::where([
                            'event_id' => $ticket->event_id,
                            'type_id' => $ticket->type_id,
                            'seat_number' => $item['seat_number'],
                            'is_booked' => false
                        ])->lockForUpdate()->first();

                        if (!$seat) {
                            $errors[] = "Seat {$item['seat_number']} is not available";
                        }
                    }
                }
            }
        });

        // 4. Single processing loop with inventory update
        $ticketData->each(function ($data) use (&$items, &$total, $ticketsCollection) {
            $ticket = $ticketsCollection[$data['ticket_id']];
            $hasSeatNumber = EventTicketType::where('event_id', $ticket->event_id)
                ->where('type_id', $ticket->type_id)
                ->value('has_seat_number');

            collect($data['items'])->each(function ($item) use ($ticket, &$items, &$total, $hasSeatNumber) {
                
                if ($hasSeatNumber && !empty($item['seat_number'])) {
                    $seat = Seats::where([
                        'event_id' => $ticket->event_id,
                        'type_id' => $ticket->type_id,
                        'seat_number' => $item['seat_number'],
                        'is_booked' => false
                    ])->lockForUpdate()->first();

                    if ($seat) {
                        $seat->is_booked = true;
                        $seat->save();
                        $item['seat_id'] = $seat->seat_id; // Pass seat_id to order detail
                    } else {
                        $errors[] = "Seat {$item['seat_number']} is already booked";
                    }
                }

                $this->processItem($item, $ticket, $items, $total);
            });

            $ticket->decrement('quota', $data['total_quantity']);
            $ticket->increment('sold_count', $data['total_quantity']);
        });

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        if ($voucher) {
            Log::info("Applying voucher: {$voucher->code} || type : {$voucher->discount_type} || discount: {$voucher->discount}");
            $discount = match ($voucher->discount_type) {
                'percentage' => $total * ($voucher->discount / 100),
                'fixed' => min($voucher->discount, $total),
                default => 0
            };
    
            Log::info("Voucher applied: {$voucher->code}, discount: {$discount}");
        }
        
        return [
            'total' => $total,
            'items' => $items,
            'discount' => $discount,
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

        if (!empty($cardNumber)) {
            $eventId = $ticket->event_id;
            $alreadyUsed = DB::table('order_details')
                ->join('tickets', 'order_details.ticket_id', '=', 'tickets.ticket_id')
                ->where('tickets.event_id', $eventId)
                ->where('order_details.id_card_number', $cardNumber)
                ->exists();

            if ($alreadyUsed) {
                $errors[] = "ID card number {$cardNumber} has already been used for this event";
            }
        }
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
            'id_card_number' => $item['card_number'] ?? null,
            'seat_id' => $item['seat_id'] ?? null
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
