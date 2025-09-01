<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApplicationStatusHistorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('application_status_history')->insert([
            'application_id' => 1,
            'from_status' => 'submitted',
            'to_status' => 'accepted',
        ]);
    }
}
