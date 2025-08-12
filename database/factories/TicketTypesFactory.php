<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TicketTypes;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketTypes>
 */
class TicketTypesFactory extends Factory
{
    protected $model = TicketTypes::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            'Adult',
            'Child',
            'Student',
            'Senior',
            'Family',
            'Group',
            'Corporate',
            'Individual'
        ];

        return [
            'name' => $this->faker->unique()->randomElement($types),
        ];
    }
}
