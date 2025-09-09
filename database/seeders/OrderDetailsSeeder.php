<?php

namespace Database\Seeders;

use App\Enums\IdCardType;
use App\Models\OrderDetails;
use App\Models\Orders;
use App\Models\Seats;
use App\Models\Tickets;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Orders::all();
        $tickets = Tickets::all();
        
        if ($orders->isEmpty() || $tickets->isEmpty()) {
            $this->command->warn('No orders or tickets found. Please run OrdersSeeder and ensure tickets exist first.');
            return;
        }

        foreach ($orders as $order) {
            // Each order gets 1-4 different ticket types
            $ticketCount = rand(1, 4);
            $selectedTickets = $tickets->random(min($ticketCount, $tickets->count()));
            $totalAmount = 0;
            
            foreach ($selectedTickets as $ticket) {
                $quantity = rand(1, 3); // 1-3 tickets per type
                $pricePerTicket = $ticket->price;
                $totalPrice = $pricePerTicket * $quantity;
                $totalAmount += $totalPrice;
                
                // Handle seat assignments if ticket requires it
                $seatIds = $this->getSeatsForTicket($ticket, $quantity);
                
                if (!empty($seatIds)) {
                    // Create separate order detail for each seat
                    foreach ($seatIds as $seatId) {
                        $idCardData = $this->getIdCardDataForOrderDetail($order, $ticket);
                        
                        OrderDetails::create([
                            'order_id' => $order->order_id,
                            'ticket_id' => $ticket->ticket_id,
                            'quantity' => 1, // Always 1 for seated tickets
                            'price' => $pricePerTicket,
                            'id_card_number' => $idCardData['number'],
                            'id_card_type' => $idCardData['type'],
                            'seat_id' => $seatId,
                            'created_at' => $order->created_at,
                            'updated_at' => $order->updated_at,
                        ]);
                        
                        // Mark seat as booked
                        Seats::where('seat_id', $seatId)->update(['is_booked' => true]);
                    }
                } else {
                    // Create single order detail for non-seated tickets
                    $idCardData = $this->getIdCardDataForOrderDetail($order, $ticket);
                    
                    OrderDetails::create([
                        'order_id' => $order->order_id,
                        'ticket_id' => $ticket->ticket_id,
                        'quantity' => $quantity,
                        'price' => $totalPrice,
                        'id_card_number' => $idCardData['number'],
                        'id_card_type' => $idCardData['type'],
                        'seat_id' => null,
                        'created_at' => $order->created_at,
                        'updated_at' => $order->updated_at,
                    ]);
                }
            }
            
            // Apply discount if exists
            if ($order->discount_amount > 0) {
                $totalAmount = max(0, $totalAmount - $order->discount_amount);
            }
            
            // Update order total amount
            $order->update(['total_amount' => $totalAmount]);
        }
        
        $this->command->info('Order details seeded successfully with realistic seat assignments.');
    }

    /**
     * Get available seats for a ticket if it requires seats
     */
    private function getSeatsForTicket(Tickets $ticket, int $quantity): array
    {
        // Check if this ticket's event and type combination has seats
        $availableSeats = Seats::where('event_id', $ticket->event_id)
            ->where('type_id', $ticket->type_id)
            ->where('is_booked', false)
            ->limit($quantity)
            ->pluck('seat_id')
            ->toArray();
            
        // Only return seats if we have enough available
        if (count($availableSeats) >= $quantity) {
            return $availableSeats;
        }
        
        return [];
    }

    /**
     * Get ID card data for order detail based on ticket requirements
     */
    private function getIdCardDataForOrderDetail(Orders $order, Tickets $ticket): array
    {
        // If ticket requires ID verification, always provide ID
        if ($ticket->requires_id_verification) {
            $type = $this->getRandomIdCardType();
            return [
                'type' => $type,
                'number' => $this->generateIdNumber($type)
            ];
        }
        
        // 30% chance to add ID even if not required
        if (rand(1, 100) <= 30) {
            $type = $this->getRandomIdCardType();
            return [
                'type' => $type,
                'number' => $this->generateIdNumber($type)
            ];
        }
        
        return ['type' => null, 'number' => null];
    }

    /**
     * Get random ID card type
     */
    private function getRandomIdCardType(): string
    {
        $types = [
            'national_id',      // Match database enum value
            'passport',         // Match database enum value  
            'driver_license',   // Match database enum value
        ];
        
        return $types[array_rand($types)];
    }

    /**
     * Generate random ID number based on type
     */
    private function generateIdNumber(string $type = null): string
    {
        $randomType = $type ?? $this->getRandomIdCardType();
        
        return match ($randomType) {
            'national_id' => sprintf('%016d', rand(1000000000000000, 9999999999999999)), // National ID
            'passport' => chr(rand(65, 90)) . chr(rand(65, 90)) . sprintf('%07d', rand(1000000, 9999999)), // Passport
            'driver_license' => sprintf('DL-%08d', rand(10000000, 99999999)), // Driver License
            default => sprintf('%016d', rand(1000000000000000, 9999999999999999)), // Default to national ID
        };
    }
}
