<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Institution;
use Faker\Factory as Faker;

class InstitutionSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        for ($i = 1; $i <= 50; $i++) {
            Institution::create([
                'name' => $faker->company . " {$i}",
                'address' => $faker->streetAddress,
                'city' => $faker->city,
                'province' => $faker->state,
                'website' => $faker->url,
                'industry' => $faker->randomElement(['Technology','Manufacturing','Healthcare','Finance','Education']),
            ]);
        }
    }
}
