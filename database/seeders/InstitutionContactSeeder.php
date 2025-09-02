<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InstitutionContact;
use Faker\Factory as Faker;

class InstitutionContactSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        for ($i = 1; $i <= 50; $i++) {
            InstitutionContact::create([
                'institution_id' => $i,
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->phoneNumber,
                'position' => $faker->jobTitle,
                'is_primary' => true,
            ]);
        }
    }
}
