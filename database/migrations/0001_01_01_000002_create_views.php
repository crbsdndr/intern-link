<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW student_details_view AS
            SELECT s.id,
                   u.id AS user_id,
                   u.name,
                   u.email,
                   u.email_verified_at,
                   u.phone,
                   u.role,
                   s.student_number,
                   s.national_sn,
                   s.major,
                   s.class,
                   s.batch,
                   s.notes,
                   s.photo,
                   s.created_at,
                   s.updated_at
            FROM app.students s
            JOIN core.users u ON s.user_id = u.id;
        SQL);

        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW supervisor_details_view AS
            SELECT s.id,
                   u.id AS user_id,
                   u.name,
                   u.email,
                   u.phone,
                   u.role,
                   s.supervisor_number,
                   s.department,
                   s.notes,
                   s.photo,
                   s.created_at,
                   s.updated_at
            FROM app.supervisors s
            JOIN core.users u ON s.user_id = u.id;
        SQL);

        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW institution_details_view AS
            WITH primary_contact AS (
                SELECT DISTINCT ON (institution_id) id, institution_id, name, email, phone, position, is_primary
                FROM app.institution_contacts
                ORDER BY institution_id, is_primary DESC, id
            ),
            latest_quota AS (
                SELECT DISTINCT ON (institution_id) iq.id, iq.institution_id, iq.quota, iq.used, p.year, p.term
                FROM app.institution_quotas iq
                JOIN app.periods p ON p.id = iq.period_id
                ORDER BY iq.institution_id, p.year DESC, p.term DESC, iq.id DESC
            )
            SELECT i.id,
                   i.name,
                   i.address,
                   i.city,
                   i.province,
                   i.website,
                   i.industry,
                   i.notes,
                   i.photo,
                   pc.name AS contact_name,
                   pc.email AS contact_email,
                   pc.phone AS contact_phone,
                   pc.position AS contact_position,
                   pc.is_primary AS contact_primary,
                   lq.year AS period_year,
                   lq.term AS period_term,
                   lq.quota,
                   lq.used
            FROM app.institutions i
            LEFT JOIN primary_contact pc ON pc.institution_id = i.id
            LEFT JOIN latest_quota lq ON lq.institution_id = i.id;
        SQL);

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
                   a.created_at,
                   a.updated_at,
                   a.notes
            FROM app.applications a
            JOIN app.students s ON s.id = a.student_id
            JOIN core.users u ON u.id = s.user_id
            JOIN app.institutions i ON i.id = a.institution_id
            JOIN app.periods p ON p.id = a.period_id;
        SQL);

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
                   it.status,
                   it.created_at,
                   it.updated_at
            FROM app.internships it
            JOIN app.students s ON s.id = it.student_id
            JOIN core.users u ON u.id = s.user_id
            JOIN app.institutions i ON i.id = it.institution_id
            JOIN app.periods p ON p.id = it.period_id;
        SQL);

        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW v_monitoring_log_summary AS
            SELECT
              ml.id                    AS monitoring_log_id,
              ml.log_date,
              ml.type                  AS log_type,
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
            FROM app.monitoring_logs ml
            JOIN app.internships it       ON it.id = ml.internship_id
            JOIN app.students s           ON s.id = it.student_id
            JOIN core.users u              ON u.id = s.user_id
            JOIN app.institutions inst    ON inst.id = it.institution_id
            JOIN app.periods p            ON p.id = it.period_id
            LEFT JOIN app.supervisors sv  ON sv.id = ml.supervisor_id
            LEFT JOIN core.users usv       ON usv.id = sv.user_id;
        SQL);

        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW v_monitoring_log_detail AS
            SELECT
              ml.id            AS monitoring_log_id,
              ml.log_date,
              ml.type          AS log_type,
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
            FROM app.monitoring_logs ml
            JOIN app.internships it       ON it.id = ml.internship_id
            JOIN app.students s           ON s.id = it.student_id
            JOIN core.users u              ON u.id = s.user_id
            JOIN app.institutions inst    ON inst.id = it.institution_id
            JOIN app.periods p            ON p.id = it.period_id
            LEFT JOIN app.supervisors sv  ON sv.id = ml.supervisor_id
            LEFT JOIN core.users usv       ON usv.id = sv.user_id;
        SQL);

        DB::statement(<<<'SQL'
        CREATE OR REPLACE VIEW v_application_summary AS
        SELECT a.id AS application_id,
               s.id AS student_id,
               u.name AS student_name,
               i.name AS institution_name,
               p.year AS period_year,
               p.term AS period_term,
               a.status,
               a.submitted_at
        FROM app.applications a
        JOIN app.students s   ON s.id = a.student_id
        JOIN core.users u      ON u.id = s.user_id
        JOIN app.institutions i ON i.id = a.institution_id
        JOIN app.periods p    ON p.id = a.period_id;
        SQL);

        DB::statement(<<<'SQL'
        CREATE OR REPLACE VIEW v_internship_detail AS
        SELECT it.id AS internship_id,
               it.status AS internship_status,
               it.start_date,
               it.end_date,
               u.name AS student_name,
               i.name AS institution_name,
               p.year AS period_year,
               p.term AS period_term,
               usv.name AS primary_supervisor_name
        FROM app.internships it
        JOIN app.students s       ON s.id = it.student_id
        JOIN core.users u          ON u.id = s.user_id
        JOIN app.institutions i   ON i.id = it.institution_id
        JOIN app.periods p        ON p.id = it.period_id
        LEFT JOIN app.internship_supervisors its ON its.internship_id = it.id AND its.is_primary = TRUE
        LEFT JOIN app.supervisors sv     ON sv.id = its.supervisor_id
        LEFT JOIN core.users usv          ON usv.id = sv.user_id;
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_internship_detail;');
        DB::statement('DROP VIEW IF EXISTS v_application_summary;');
        DB::statement('DROP VIEW IF EXISTS v_monitoring_log_detail;');
        DB::statement('DROP VIEW IF EXISTS v_monitoring_log_summary;');
        DB::statement('DROP VIEW IF EXISTS internship_details_view;');
        DB::statement('DROP VIEW IF EXISTS application_details_view;');
        DB::statement('DROP VIEW IF EXISTS institution_details_view;');
        DB::statement('DROP VIEW IF EXISTS supervisor_details_view;');
        DB::statement('DROP VIEW IF EXISTS student_details_view;');
    }
};
