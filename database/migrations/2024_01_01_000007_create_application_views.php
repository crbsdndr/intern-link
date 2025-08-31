<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW application_details_view AS
            SELECT a.id,
                   a.student_id,
                   u.name AS student_name,
                   a.institution_id,
                   i.name AS institution_name,
                   a.period_id,
                   p.year AS period_year,
                   p.term AS period_term,
                   a.status,
                   a.submitted_at,
                   a.decision_at,
                   a.rejection_reason,
                   a.notes
            FROM applications a
            JOIN students s ON s.id = a.student_id
            JOIN users u ON u.id = s.user_id
            JOIN institutions i ON i.id = a.institution_id
            JOIN periods p ON p.id = a.period_id;
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS application_details_view;');
    }
};
