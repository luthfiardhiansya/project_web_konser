<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ADMIN
        User::updateOrCreate(
            ['email' => 'admin@infomusikbdg.com'],
            [
                'name' => 'Admin InfoMusikBDG',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // ORGANIZER / PANITIA
        User::updateOrCreate(
            ['email' => 'organizer@infomusikbdg.com'],
            [
                'name' => 'Panitia InfoMusikBDG',
                'password' => Hash::make('password123'),
                'role' => 'organizer',
            ]
        );

        // SCANNER
        User::updateOrCreate(
            ['email' => 'scanner@infomusikbdg.com'],
            [
                'name' => 'Scanner InfoMusikBDG',
                'password' => Hash::make('password123'),
                'role' => 'scanner',
            ]
        );

        // USER
        User::updateOrCreate(
            ['email' => 'user@infomusikbdg.com'],
            [
                'name' => 'User',
                'password' => Hash::make('password123'),
                'role' => 'user',
            ]
        );
    }
}