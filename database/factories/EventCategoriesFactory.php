<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventCategories>
 */
class EventCategoriesFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\EventCategories::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Music',
            'Sports',
            'Technology',
            'Food & Drink',
            'Arts & Culture',
            'Business',
            'Health & Wellness',
            'Education',
            'Fashion',
            'Travel',
            'Entertainment',
            'Science',
            'Literature',
            'Photography',
            'Gaming'
        ];

        return [
            'name' => $this->faker->unique()->randomElement($categories),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    /**
     * Create a music category
     */
    public function music(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Music',
        ]);
    }

    /**
     * Create a sports category
     */
    public function sports(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Sports',
        ]);
    }

    /**
     * Create a technology category
     */
    public function technology(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Technology',
        ]);
    }
}
