<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TicketCategories;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketCategories>
 */
class TicketCategoriesFactory extends Factory
{
    protected $model = TicketCategories::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'VIP',
            'Premium',
            'Standard',
            'Economy',
            'General Admission',
            'Early Bird',
            'Student',
            'Senior'
        ];

        return [
            'name' => $this->faker->unique()->randomElement($categories),
        ];
    }
}
