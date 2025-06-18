<?php

namespace Database\Seeders;

use App\Models\EventCategories;
use Illuminate\Database\Seeder;
use App\Models\Events;
use App\Models\TicketCategories;
use App\Models\Tickets;
use App\Models\TicketTypes;

class EventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Events::factory()
        ->count(10)
        ->hasImages(3)
        ->create()
        ->each(function ($event) {
            $categories = TicketCategories::pluck('category_id');
            $types = TicketTypes::pluck('type_id');
            
            // Create unique combinations
            $combinations = collect($categories)
                ->crossJoin($types)
                ->shuffle()
                ->take(5);

            foreach ($combinations as $combination) {
                Tickets::factory()->create([
                    'event_id' => $event->event_id,
                    'category_id' => $combination[0],
                    'type_id' => $combination[1],
                ]);
            }

            $eventCategories = EventCategories::inRandomOrder()->take(2)->pluck('category_id');
            dd($eventCategories);
            $event->categories()->syncWithoutDetaching($eventCategories);
        });
    }
}
