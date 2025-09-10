<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Events;
use PHPUnit\Framework\Attributes\Ticket;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // First, create roles and admin users for admin panel
            RoleSeeder::class,
            AdminUserSeeder::class,
            
            // Then seed the rest of the data
            CategorySeeder::class,
            TicketCategoriesSeeder::class,
            TicketTypesSeeder::class,
            EventsSeeder::class,
            EventTicketTypeSeeder::class,
            SeatsSeeder::class,
            VouchersSeeder::class,
            UsersSeeder::class,  // This will seed API users
            OrdersSeeder::class,
            OrderDetailsSeeder::class,
        ]);
    }
}
