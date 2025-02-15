<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Role::count() === 0) {
            throw new \Exception('Roles must be seeded first');
        }

        User::create([
            'login' => 'admin',
            'password' => Hash::make('admin'),
            'role_id' => getAdminRoleId(),
        ]);
        User::factory()->count(10)->create();
    }
}
