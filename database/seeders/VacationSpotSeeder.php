<?php

namespace Database\Seeders;

use App\Models\VacationSpot;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class VacationSpotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        VacationSpot::factory()->count(10)->create();
    }
}
