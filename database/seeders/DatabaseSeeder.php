<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario de prueba con contraseña "password"
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Usuario Prueba',
                'password' => bcrypt('password'),
            ]
        );

        // Crear segundo usuario de prueba
        User::firstOrCreate(
            ['email' => 'demo@pokedex.com'],
            [
                'name' => 'Demo User',
                'password' => bcrypt('demo1234'),
            ]
        );
    }
}
