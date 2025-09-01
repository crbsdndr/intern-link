<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supervisor;
use Faker\Factory as Faker;

class SupervisorSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        for ($i = 1; $i <= 10; $i++) {
            Supervisor::create([
                'user_id' => 10 + $i,
                'supervisor_number' => 'SP-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'department' => $faker->randomElement(['Engineering', 'Business', 'Design']),
            ]);
        }
    }
}
