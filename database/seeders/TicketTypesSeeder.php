<?php

namespace Database\Seeders;

use App\Models\TicketTypes;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TicketTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   
        $types = [
            ['name' => 'Regular'],
            ['name' => 'Festival'],
            ['name' => 'VIP'],
            ['name' => 'VVIP'],
            ['name' => 'Special Guest']
        ];

        TicketTypes::insert(array_map(function ($category) {
            return array_merge($category, [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }, $types));
    }
}
