<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW internship_details_view AS
            SELECT it.id,
                   it.application_id,
                   it.student_id,
                   u.name AS student_name,
                   it.institution_id,
                   i.name AS institution_name,
                   it.period_id,
                   p.year AS period_year,
                   p.term AS period_term,
                   it.start_date,
                   it.end_date,
                   it.status
            FROM internships it
            JOIN students s ON s.id = it.student_id
            JOIN users u ON u.id = s.user_id
            JOIN institutions i ON i.id = it.institution_id
            JOIN periods p ON p.id = it.period_id;
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS internship_details_view;');
    }
};
