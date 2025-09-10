<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
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
                ],
                'is_active' => true,
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
                ],
                'is_active' => true,
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
                ],
                'is_active' => true,
            ],
            [
                'name' => 'order_manager',
                'display_name' => 'Order Manager',
                'description' => 'Manages orders and customer transactions',
                'permissions' => [
                    'users.view',
                    'orders.view', 'orders.edit',
                    'tickets.view',
                    'dashboard.view'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'viewer',
                'display_name' => 'Viewer',
                'description' => 'Read-only access to view data',
                'permissions' => [
                    'users.view', 'events.view', 'orders.view', 'tickets.view',
                    'categories.view', 'dashboard.view'
                ],
                'is_active' => true,
            ]
        ];

        foreach ($roles as $roleData) {
            \App\Models\Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }
    }
}
