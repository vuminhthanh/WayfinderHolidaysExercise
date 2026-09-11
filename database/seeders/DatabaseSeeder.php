<?php

namespace Database\Seeders;

use App\Models\Tour;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Tour::query()->create([
            'name' => 'Hanoi Discovery',
            'slug' => 'hanoi-discovery',
        ]);

        Tour::query()->create([
            'name' => 'Ha Long Bay Escape',
            'slug' => 'ha-long-bay-escape',
        ]);
    }
}
