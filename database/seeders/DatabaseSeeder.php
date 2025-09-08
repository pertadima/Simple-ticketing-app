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
            CategorySeeder::class,
            TicketCategoriesSeeder::class,
            TicketTypesSeeder::class,
            EventsSeeder::class,
            VouchersSeeder::class,
            UsersSeeder::class
        ]);
    }
}
