<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InstitutionQuota;

class InstitutionQuotaSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 50; $i++) {
            InstitutionQuota::create([
                'institution_id' => $i,
                'period_id' => $i,
                'quota' => 5,
                'used' => 0,
            ]);
        }
    }
}
