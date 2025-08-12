<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\EventTicketType;
use App\Models\Events;
use App\Models\TicketTypes;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventTicketType>
 */
class EventTicketTypeFactory extends Factory
{
    protected $model = EventTicketType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Events::factory(),
            'type_id' => TicketTypes::factory(),
            'has_seat_number' => $this->faker->boolean(70), // 70% chance of having seat numbers
        ];
    }

    /**
     * Indicate that this ticket type has seat numbers.
     */
    public function withSeatNumbers(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_seat_number' => true,
        ]);
    }

    /**
     * Indicate that this ticket type does not have seat numbers.
     */
    public function withoutSeatNumbers(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_seat_number' => false,
        ]);
    }
}
