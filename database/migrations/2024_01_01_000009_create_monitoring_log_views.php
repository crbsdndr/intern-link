<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW v_monitoring_log_summary AS
            SELECT
              ml.id                    AS monitoring_log_id,
              ml.log_date,
              ml.type                  AS log_type,
              ml.score,
              COALESCE(ml.title, NULL) AS title,
              ml.content,
              ml.supervisor_id,
              it.id                    AS internship_id,
              u.name                   AS student_name,
              inst.name                AS institution_name,
              usv.name                 AS supervisor_name,
              p.year                   AS period_year,
              p.term                   AS period_term,
              it.status                AS internship_status
            FROM monitoring_logs ml
            JOIN internships it       ON it.id = ml.internship_id
            JOIN students s           ON s.id = it.student_id
            JOIN users u              ON u.id = s.user_id
            JOIN institutions inst    ON inst.id = it.institution_id
            JOIN periods p            ON p.id = it.period_id
            LEFT JOIN supervisors sv  ON sv.id = ml.supervisor_id
            LEFT JOIN users usv       ON usv.id = sv.user_id;
        SQL);

        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW v_monitoring_log_detail AS
            SELECT
              ml.id            AS monitoring_log_id,
              ml.log_date,
              ml.type          AS log_type,
              ml.score,
              ml.title,
              ml.content,
              ml.supervisor_id,
              it.id            AS internship_id,
              it.status        AS internship_status,
              it.start_date,
              it.end_date,
              u.name           AS student_name,
              inst.name        AS institution_name,
              p.year           AS period_year,
              p.term           AS period_term,
              usv.name         AS supervisor_name
            FROM monitoring_logs ml
            JOIN internships it       ON it.id = ml.internship_id
            JOIN students s           ON s.id = it.student_id
            JOIN users u              ON u.id = s.user_id
            JOIN institutions inst    ON inst.id = it.institution_id
            JOIN periods p            ON p.id = it.period_id
            LEFT JOIN supervisors sv  ON sv.id = ml.supervisor_id
            LEFT JOIN users usv       ON usv.id = sv.user_id;
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_monitoring_log_detail;');
        DB::statement('DROP VIEW IF EXISTS v_monitoring_log_summary;');
    }
};
