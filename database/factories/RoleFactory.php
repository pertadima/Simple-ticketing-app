<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrator',
                'description' => 'Full system access with all permissions',
                'permissions' => [
                    'users.view', 'users.create', 'users.edit', 'users.delete',
                    'events.view', 'events.create', 'events.edit', 'events.delete',
                    'orders.view', 'orders.create', 'orders.edit', 'orders.delete',
                    'tickets.view', 'tickets.create', 'tickets.edit', 'tickets.delete',
                    'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
                    'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
                    'admin_users.view', 'admin_users.create', 'admin_users.edit', 'admin_users.delete',
                    'dashboard.view', 'system.manage'
                ]
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'General admin access with most permissions',
                'permissions' => [
                    'users.view', 'users.create', 'users.edit',
                    'events.view', 'events.create', 'events.edit', 'events.delete',
                    'orders.view', 'orders.edit',
                    'tickets.view', 'tickets.create', 'tickets.edit',
                    'categories.view', 'categories.create', 'categories.edit',
                    'dashboard.view'
                ]
            ],
            [
                'name' => 'event_manager',
                'display_name' => 'Event Manager',
                'description' => 'Manages events, tickets and categories',
                'permissions' => [
                    'events.view', 'events.create', 'events.edit',
                    'tickets.view', 'tickets.create', 'tickets.edit',
                    'categories.view', 'categories.create', 'categories.edit',
                    'dashboard.view'
                ]
            ],
            [
                'name' => 'viewer',
                'display_name' => 'Viewer',
                'description' => 'Read-only access to view data',
                'permissions' => [
                    'users.view', 'events.view', 'orders.view', 'tickets.view',
                    'categories.view', 'dashboard.view'
                ]
            ]
        ];

        $role = fake()->randomElement($roles);
        
        return [
            'name' => $role['name'],
            'display_name' => $role['display_name'],
            'description' => $role['description'],
            'permissions' => $role['permissions'],
            'is_active' => true,
        ];
    }

    /**
     * Create a super admin role.
     */
    public function superAdmin(): static
    {
        return $this->state([
            'name' => 'super_admin',
            'display_name' => 'Super Administrator',
            'description' => 'Full system access with all permissions',
            'permissions' => [
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'events.view', 'events.create', 'events.edit', 'events.delete',
                'orders.view', 'orders.create', 'orders.edit', 'orders.delete',
                'tickets.view', 'tickets.create', 'tickets.edit', 'tickets.delete',
                'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
                'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
                'admin_users.view', 'admin_users.create', 'admin_users.edit', 'admin_users.delete',
                'dashboard.view', 'system.manage'
            ]
        ]);
    }

    /**
     * Indicate that the role is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
