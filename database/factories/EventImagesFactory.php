<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Events;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventImages>
 */
class EventImagesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Events::factory(),
            'image_url' => 'https://picsum.photos/800/600?random='.$this->faker->numberBetween(1, 1000),
        ];
    }
}
