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
        // Super Admin (Developer)
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@softluciones.co',
            'password' => Hash::make('password'),
            'role' => 'super-admin',
        ]);

        // Administrador (Owner of a Parking)
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'created_by' => $superAdmin->id,
            'max_parkings' => 2,
        ]);

        // Create a Parking
        $parking1 = \App\Models\Parking::create([
            'name' => 'Parqueadero Central',
            'nit' => '900123456-1',
            'address' => 'Calle 10 # 5-20',
            'phone' => '3001234567',
            'schedule' => 'Lunes a Domingo: 6:00 AM - 10:00 PM',
            'admin_id' => $admin->id,
        ]);

        // Operario
        $operario = User::create([
            'name' => 'Operario 1',
            'email' => 'operario@parqueadero.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'created_by' => $admin->id,
        ]);

        // Associate Operario to Parking
        $parking1->operators()->attach($operario->id);

        // Vehicle Types
        $carType = VehicleType::create([
            'name' => 'Carro',
            'icon' => '🚗',
            'capacity' => 50,
            'parking_id' => $parking1->id,
        ]);

        $motoType = VehicleType::create([
            'name' => 'Moto',
            'icon' => '🏍️',
            'capacity' => 100,
            'parking_id' => $parking1->id,
        ]);

        $biciType = VehicleType::create([
            'name' => 'Bicicleta',
            'icon' => '🚲',
            'capacity' => 20,
            'parking_id' => $parking1->id,
        ]);

        // Rates
        Rate::create([
            'vehicle_type_id' => $carType->id,
            'price_per_hour' => 3000,
            'fraction_price' => 1000,
            'parking_id' => $parking1->id,
        ]);

        Rate::create([
            'vehicle_type_id' => $motoType->id,
            'price_per_hour' => 1500,
            'fraction_price' => 500,
            'parking_id' => $parking1->id,
        ]);

        Rate::create([
            'vehicle_type_id' => $biciType->id,
            'price_per_hour' => 500,
            'fraction_price' => 200,
            'parking_id' => $parking1->id,
        ]);
    }
}
