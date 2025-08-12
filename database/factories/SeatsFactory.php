<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Seats;
use App\Models\Events;
use App\Models\TicketTypes;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Seats>
 */
class SeatsFactory extends Factory
{
    protected $model = Seats::class;

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
            'seat_number' => $this->faker->bothify('##?'),
            'is_booked' => $this->faker->boolean(30), // 30% chance of being booked
        ];
    }

    /**
     * Indicate that the seat is booked.
     */
    public function booked(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_booked' => true,
        ]);
    }

    /**
     * Indicate that the seat is available.
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_booked' => false,
        ]);
    }
}
