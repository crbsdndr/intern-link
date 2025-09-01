<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Institution;

class InstitutionSeeder extends Seeder
{
    public function run(): void
    {
        Institution::create([
            'name' => 'Tech Corp',
            'address' => '123 Tech Street',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'website' => 'https://techcorp.example',
        ]);
    }
}
