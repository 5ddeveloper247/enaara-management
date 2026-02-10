<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $testPassword = 'password';

        $users = [
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make($testPassword),
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make($testPassword),
            ],
            [
                'name' => 'Demo User',
                'email' => 'demo@example.com',
                'password' => Hash::make($testPassword),
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => $user['password'],
                ]
            );
        }
    }
}
