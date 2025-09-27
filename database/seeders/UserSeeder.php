<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario administrador
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@hotel.com',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        // Usuario cliente (opcional, para futuras funcionalidades)
        User::create([
            'name' => 'Cliente Ejemplo',
            'email' => 'cliente@hotel.com',
            'password' => bcrypt('password123'),
            'is_admin' => false,
        ]);
    }
}