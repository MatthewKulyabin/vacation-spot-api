<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\VacationSpot;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class WishlistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $vacationSpots = VacationSpot::all();

        if (!$users->count() || !$vacationSpots->count()) {
            throw new \Exception('Users and VacationSpots must be seeded first');
        }

        foreach ($users as $user) {
            // Ensure at most 3 vacation spots
            $vacationSpotsToAttach = $vacationSpots->random(min(rand(1, 5), 3));

            $user->vacationSpots()->attach(
                $vacationSpotsToAttach->pluck('id')->toArray()
            );
        }
    }
}
