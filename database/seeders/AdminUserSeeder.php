<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default super admin user
        $superAdmin = \App\Models\AdminUser::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Administrator',
                'email' => 'admin@example.com',
                'password' => 'password', // This will be hashed automatically
                'email_verified_at' => now(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Assign super admin role
        $superAdminRole = \App\Models\Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdmin->roles()->syncWithoutDetaching([$superAdminRole->role_id]);
        }

        // Create a regular admin user
        $admin = \App\Models\AdminUser::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Admin Manager',
                'email' => 'manager@example.com',
                'password' => 'password', // This will be hashed automatically
                'email_verified_at' => now(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Assign admin role
        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        if ($adminRole) {
            $admin->roles()->syncWithoutDetaching([$adminRole->role_id]);
        }

        // Create an event manager user
        $eventManager = \App\Models\AdminUser::firstOrCreate(
            ['email' => 'events@example.com'],
            [
                'name' => 'Event Manager',
                'email' => 'events@example.com',
                'password' => 'password', // This will be hashed automatically
                'email_verified_at' => now(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Assign event manager role
        $eventManagerRole = \App\Models\Role::where('name', 'event_manager')->first();
        if ($eventManagerRole) {
            $eventManager->roles()->syncWithoutDetaching([$eventManagerRole->role_id]);
        }
    }
}
