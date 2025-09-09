<?php

namespace Database\Seeders;

use App\Models\Events;
use App\Models\EventTicketType;
use App\Models\Seats;
use App\Models\TicketTypes;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SeatsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eventTicketTypes = \App\Models\EventTicketType::where('has_seat_number', true)->get();

        foreach ($eventTicketTypes as $eventTicketType) {
            $ticketType = $eventTicketType->type;
            
            // Generate seats based on ticket type
            $seatCount = $this->getSeatCountByType($ticketType->name);
            $seatPrefix = $this->getSeatPrefixByType($ticketType->name);
            
            for ($i = 1; $i <= $seatCount; $i++) {
                $seatNumber = $seatPrefix . str_pad($i, 3, '0', STR_PAD_LEFT);
                
                // Randomly make some seats booked (about 30% chance)
                $isBooked = rand(1, 100) <= 30;
                
                Seats::create([
                    'event_id' => $eventTicketType->event_id,
                    'type_id' => $eventTicketType->type_id,
                    'seat_number' => $seatNumber,
                    'is_booked' => $isBooked,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }

    /**
     * Get seat count based on ticket type
     */
    private function getSeatCountByType(string $typeName): int
    {
        return match (strtolower($typeName)) {
            'regular' => 100,
            'festival' => 150,
            'vip' => 50,
            'vvip' => 20,
            'special guest' => 10,
            default => 50,
        };
    }

    /**
     * Get seat prefix based on ticket type
     */
    private function getSeatPrefixByType(string $typeName): string
    {
        return match (strtolower($typeName)) {
            'regular' => 'REG-',
            'festival' => 'FES-',
            'vip' => 'VIP-',
            'vvip' => 'VVIP-',
            'special guest' => 'SG-',
            default => 'GEN-',
        };
    }
}
