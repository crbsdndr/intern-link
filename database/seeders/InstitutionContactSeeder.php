<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InstitutionContact;

class InstitutionContactSeeder extends Seeder
{
    public function run(): void
    {
        InstitutionContact::create([
            'institution_id' => 1,
            'name' => 'John Doe',
            'email' => 'contact@techcorp.example',
            'phone' => '021000000',
            'position' => 'HR',
            'is_primary' => true,
        ]);
    }
}
