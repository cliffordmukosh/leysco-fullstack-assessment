<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/customers.json');

        if (!file_exists($path)) {
            $this->command->error('customers.json not found!');
            return;
        }

        $customers = json_decode(file_get_contents($path), true);

        foreach ($customers as $data) {
            Customer::create([
                'name' => $data['name'],
                'type' => $data['type'],
                'category' => $data['category'],
                'contact_person' => $data['contact_person'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'tax_id' => $data['tax_id'],
                'payment_terms' => $data['payment_terms'],
                'credit_limit' => $data['credit_limit'],
                'current_balance' => $data['current_balance'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'address' => $data['address'],
                'territory' => $data['category'] === 'A+' ? 'Premium' : 'Standard', // Example logic
            ]);
        }

        $this->command->info('Customers seeded successfully!');
    }
}