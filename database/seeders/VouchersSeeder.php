<?php

namespace Database\Seeders;

use App\Models\Events;
use App\Models\Vouchers;
use Illuminate\Database\Seeder;

class VouchersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create general vouchers (not tied to specific events)
        Vouchers::factory()
            ->count(20)
            ->state(['type' => 'general', 'event_id' => null])
            ->create();

        // Create specific vouchers for random events
        $events = Events::pluck('event_id');
        
        if ($events->isNotEmpty()) {
            // Create 2-3 vouchers per event for some events
            $events->random(min(50, $events->count()))->each(function ($eventId) {
                Vouchers::factory()
                    ->count(rand(2, 3))
                    ->state([
                        'type' => 'specific',
                        'event_id' => $eventId
                    ])
                    ->create();
            });
        }

        // Create some expired vouchers for testing
        Vouchers::factory()
            ->count(10)
            ->expired()
            ->create();
    }
}
