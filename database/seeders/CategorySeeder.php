<?php

namespace Database\Seeders;

use App\Models\EventCategories;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $categories = ['Music', 'Tech', 'Art', 'Sports', 'Food', 'Education', 'Health', 'Travel', 'Business', 'Lifestyle', 'Comedy'];

        foreach ($categories as $name) {
            EventCategories::create(['name' => $name]);
        }
    }
}
