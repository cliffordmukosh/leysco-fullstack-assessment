<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/users.json');

        if (!file_exists($path)) {
            $this->command->error('users.json not found!');
            return;
        }

        $users = json_decode(file_get_contents($path), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('Invalid JSON in users.json: ' . json_last_error_msg());
            return;
        }

        foreach ($users as $userData) {
            User::create([
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'role' => $userData['role'],
                'permissions' => $userData['permissions'],
                'status' => $userData['status'],
            ]);
        }

        $this->command->info('Users seeded successfully!');
    }
}