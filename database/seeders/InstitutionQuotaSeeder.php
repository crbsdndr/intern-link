<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InstitutionQuota;

class InstitutionQuotaSeeder extends Seeder
{
    public function run(): void
    {
        InstitutionQuota::create([
            'institution_id' => 1,
            'period_id' => 1,
            'quota' => 5,
            'used' => 0,
        ]);
    }
}
