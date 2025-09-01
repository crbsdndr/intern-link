<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Alice Student',
            'email' => 'alice@student.test',
            'phone' => '1234567890',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        User::create([
            'name' => 'Bob Supervisor',
            'email' => 'bob@supervisor.test',
            'phone' => '0987654321',
            'password' => Hash::make('password'),
            'role' => 'supervisor',
        ]);
    }
}
