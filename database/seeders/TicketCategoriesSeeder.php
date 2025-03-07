<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TicketCategories;
use Illuminate\Support\Carbon;

class TicketCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   
        $categories = [
            ['name' => 'Early Bird'],
            ['name' => 'Normal Price'],
            ['name' => 'Group Discount'],
            ['name' => 'Last Minute'],
            ['name' => 'VIP Package']
        ];

        TicketCategories::insert(array_map(function ($category) {
            return array_merge($category, [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }, $categories));
    }
}
