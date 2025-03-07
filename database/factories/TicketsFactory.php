<?php

namespace Database\Factories;

use App\Models\TicketCategories;
use App\Models\Tickets;
use App\Models\TicketTypes;
use App\Models\Events;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tickets>
 */
class TicketsFactory extends Factory
{
    protected $model = Tickets::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $event = Events::inRandomOrder()->first();
    
        // Get unique category-type combinations for this event
        $existingCombinations = $event->tickets()
            ->pluck('category_id', 'type_id')
            ->toArray();
    
        do {
            $categoryId = TicketCategories::inRandomOrder()->value('category_id');
            $typeId = TicketTypes::inRandomOrder()->value('type_id');
        } while (isset($existingCombinations[$typeId]) && 
                $existingCombinations[$typeId] === $categoryId);
    
        $requiresId = $this->faker->boolean(30);
        
        return [
            'event_id' => $event->event_id,
            'category_id' => $categoryId,
            'type_id' => $typeId,
            'price' => $this->faker->numberBetween(50, 500),
            'quota' => $this->faker->numberBetween(50, 200),
            'sold_count' => 0,
            'min_age' => $requiresId ? $this->faker->numberBetween(18, 21) : null,
            'requires_id_verification' => $requiresId,
        ];
    }
}
