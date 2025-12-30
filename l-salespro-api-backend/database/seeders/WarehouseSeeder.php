<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/warehouses.json');

        if (!file_exists($path)) {
            $this->command->error('warehouses.json not found!');
            return;
        }

        $warehouses = json_decode(file_get_contents($path), true);

        foreach ($warehouses as $data) {
            Warehouse::create([
                'code' => $data['code'],
                'name' => $data['name'],
                'type' => $data['type'],
                'address' => $data['address'],
                'manager_email' => $data['manager_email'],
                'phone' => $data['phone'],
                'capacity' => $data['capacity'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
            ]);
        }

        $this->command->info('Warehouses seeded successfully!');
    }
}