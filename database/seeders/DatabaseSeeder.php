<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\VehicleType;
use App\Models\Rate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Administrador
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Operario
        User::create([
            'name' => 'Operario 1',
            'email' => 'operario@parqueadero.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
        ]);

        // Vehicle Types
        $car = VehicleType::create([
            'name' => 'Carro',
            'icon' => '🚗',
        ]);

        $moto = VehicleType::create([
            'name' => 'Moto',
            'icon' => '🏍️',
        ]);

        $bici = VehicleType::create([
            'name' => 'Bicicleta',
            'icon' => '🚲',
        ]);

        // Rates
        Rate::create([
            'vehicle_type_id' => $car->id,
            'price_per_hour' => 3000,
            'fraction_price' => 1500,
        ]);

        Rate::create([
            'vehicle_type_id' => $moto->id,
            'price_per_hour' => 1000,
            'fraction_price' => 500,
        ]);

        Rate::create([
            'vehicle_type_id' => $bici->id,
            'price_per_hour' => 500,
            'fraction_price' => 200,
        ]);
    }
}
