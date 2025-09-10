<?php

namespace Database\Seeders;

use App\Models\ApiUser;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ApiUser::factory()
        ->count(10)
        ->create();
    }
}
