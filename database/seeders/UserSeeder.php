<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 1; $i <= 50; $i++) {
            User::create([
                'name' => $faker->name,
                'email' => "student{$i}@example.com",
                'phone' => sprintf('081%07d', $i),
                'password' => Hash::make('password'),
                'role' => 'student',
            ]);
        }

        for ($i = 1; $i <= 50; $i++) {
            User::create([
                'name' => $faker->name,
                'email' => "supervisor{$i}@example.com",
                'phone' => sprintf('082%07d', $i),
                'password' => Hash::make('password'),
                'role' => 'supervisor',
            ]);
        }
    }
}
