<?php

namespace Database\Seeders;

use App\Models\EventTicketType;
use App\Models\Events;
use App\Models\TicketTypes;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EventTicketTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = Events::all();
        $ticketTypes = TicketTypes::all();

        foreach ($events as $event) {
            // Randomly select 2-4 ticket types for each event
            $selectedTypes = $ticketTypes->random(rand(2, min(4, $ticketTypes->count())));
            
            foreach ($selectedTypes as $ticketType) {
                // Determine if this combination should have seat numbers
                $hasSeatNumber = $this->shouldHaveSeatNumber($ticketType->name);
                
                EventTicketType::create([
                    'event_id' => $event->event_id,
                    'type_id' => $ticketType->type_id,
                    'has_seat_number' => $hasSeatNumber,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }

    /**
     * Determine if a ticket type should have seat numbers based on type name
     */
    private function shouldHaveSeatNumber(string $typeName): bool
    {
        // Define probability of having seat numbers based on ticket type
        $seatNumberProbabilities = [
            'regular' => 80,      // 80% chance
            'festival' => 60,     // 60% chance (festivals might be standing)
            'vip' => 95,          // 95% chance (VIP usually has assigned seats)
            'vvip' => 100,        // 100% chance (VVIP always has assigned seats)
            'special guest' => 100, // 100% chance (Special guests always have assigned seats)
        ];

        $probability = $seatNumberProbabilities[strtolower($typeName)] ?? 70; // Default 70%
        
        return rand(1, 100) <= $probability;
    }
}
