<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Period;

class PeriodSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Period::create([
                'year' => 2020 + $i,
                'term' => ($i % 2) + 1,
            ]);
        }
    }
}
