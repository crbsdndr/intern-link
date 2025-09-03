<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DeveloperSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'developer@example.com'],
            [
                'name' => 'Developer User',
                'phone' => '0800000002',
                'password' => Hash::make('password'),
                'role' => 'developer',
            ]
        );
    }
}
