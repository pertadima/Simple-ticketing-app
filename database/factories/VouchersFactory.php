<?php

namespace Database\Factories;

use App\Models\Events;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vouchers>
 */
class VouchersFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {     
        return [
            'code' => strtoupper(fake()->bothify('????##??##')),
            'type' => fake()->randomElement(['general', 'specific']),
            'discount' => fake()->numberBetween(5, 50),
            'discount_type' => fake()->randomElement(['percentage', 'fixed']),
            'event_id' => fn (array $attributes) => 
                $attributes['type'] === 'specific' ? Events::factory() : null,
            'valid_until' => fake()->dateTimeBetween('+1 week', '+1 year'),
            'usage_limit' => fake()->numberBetween(10, 100),
            'used_count' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_until' => fake()->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }
}
