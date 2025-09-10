<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // =============================
        // 0) Extensions, ENUMs, helpers
        // =============================
        DB::statement("CREATE EXTENSION IF NOT EXISTS citext;");

        DB::statement(<<<'SQL'
        DO $$
        BEGIN
          IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'user_role_enum') THEN
            CREATE TYPE user_role_enum AS ENUM ('student','supervisor','admin','developer');
          END IF;
          IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'application_status_enum') THEN
            CREATE TYPE application_status_enum AS ENUM ('draft','submitted','under_review','accepted','rejected','cancelled');
          END IF;
          IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'internship_status_enum') THEN
            CREATE TYPE internship_status_enum AS ENUM ('planned','ongoing','completed','terminated');
          END IF;
          IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'monitor_type_enum') THEN
            CREATE TYPE monitor_type_enum AS ENUM ('weekly','issue','final','other');
          END IF;
        END $$;
        SQL);

        DB::statement(<<<'SQL'
        CREATE OR REPLACE FUNCTION set_updated_at()
        RETURNS trigger LANGUAGE plpgsql AS $$
        BEGIN
          NEW.updated_at := now();
          RETURN NEW;
        END $$;
        SQL);

        DB::statement(<<<'SQL'
        CREATE OR REPLACE FUNCTION validate_quota_not_over()
        RETURNS trigger LANGUAGE plpgsql AS $$
        BEGIN
          IF NEW.used > NEW.quota THEN
            RAISE EXCEPTION 'Quota exceeded: used=% > quota=% for institution_quotas.%', NEW.used, NEW.quota, NEW.id;
          END IF;
          RETURN NEW;
        END $$;
        SQL);

        // =============================
        // 1) Reference: periods
        // =============================
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->smallInteger('term');
            $table->unique(['year','term']);
        });
        DB::statement("ALTER TABLE periods ADD CONSTRAINT chk_period_year CHECK (year BETWEEN 2000 AND 2100);");
        DB::statement("ALTER TABLE periods ADD CONSTRAINT chk_period_term CHECK (term BETWEEN 1 AND 4);");

        // =============================
        // 2) Core tables
        // =============================
        // USERS (Laravel default + phone + role). Email -> CITEXT, role -> enum.
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique(); // cast to CITEXT below
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('password', 255);
            $table->string('role')->default('student'); // cast to enum below
            $table->rememberToken();
            $table->timestampsTz();
        });
        DB::statement("ALTER TABLE users ALTER COLUMN email TYPE CITEXT;");
        DB::statement("ALTER TABLE users ALTER COLUMN role DROP DEFAULT;");
        DB::statement("ALTER TABLE users ALTER COLUMN role TYPE user_role_enum USING role::user_role_enum;");
        DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'student'::user_role_enum;");
        DB::statement(<<<'SQL'
        CREATE TRIGGER trg_users_updated_at
        BEFORE UPDATE ON users
        FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        SQL);

        // STUDENTS
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('student_number', 50)->unique();
            $table->string('national_sn', 50)->unique();
            $table->string('major', 100);
            $table->string('class', 100)->index();
            $table->string('batch', 9);
            $table->text('notes')->nullable();
            $table->text('photo')->nullable();
            $table->timestampsTz();
            $table->unique('user_id');
        });
        DB::statement(<<<'SQL'
        CREATE TRIGGER trg_students_updated_at
        BEFORE UPDATE ON students
        FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        SQL);

        // SUPERVISORS
        Schema::create('supervisors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('supervisor_number', 50)->unique();
            $table->string('department', 100);
            $table->text('notes')->nullable();
            $table->text('photo')->nullable();
            $table->timestampsTz();
            $table->unique('user_id');
        });
        DB::statement(<<<'SQL'
        CREATE TRIGGER trg_supervisors_updated_at
        BEFORE UPDATE ON supervisors
        FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        SQL);

        // INSTITUTIONS
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->text('website')->nullable();
            $table->string('industry', 100)->index();
            $table->text('notes')->nullable();
            $table->string('photo', 255)->nullable();
            $table->timestampsTz();
        });
        DB::statement(<<<'SQL'
        CREATE TRIGGER trg_institutions_updated_at
        BEFORE UPDATE ON institutions
        FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        SQL);

        // INSTITUTION CONTACTS
        Schema::create('institution_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('position', 100)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestampsTz();
            $table->unique(['institution_id','email']);
        });
        DB::statement(<<<'SQL'
        CREATE TRIGGER trg_institution_contacts_updated_at
        BEFORE UPDATE ON institution_contacts
        FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        SQL);

        // INSTITUTION QUOTAS
        Schema::create('institution_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('periods')->restrictOnDelete();
            $table->integer('quota');
            $table->integer('used')->default(0);
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->unique(['institution_id','period_id']);
        });
        DB::statement("ALTER TABLE institution_quotas ADD CONSTRAINT chk_quota_nonneg CHECK (quota >= 0);");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_inst_quota_period ON institution_quotas (institution_id, period_id);");
        DB::statement(<<<'SQL'
        CREATE TRIGGER trg_inst_quota_validate BEFORE INSERT OR UPDATE ON institution_quotas
        FOR EACH ROW EXECUTE FUNCTION validate_quota_not_over();
        SQL);
        DB::statement(<<<'SQL'
        CREATE TRIGGER trg_institution_quotas_updated_at BEFORE UPDATE ON institution_quotas
        FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        SQL);

        // APPLICATIONS
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('institution_id')->constrained('institutions')->restrictOnDelete();
            $table->foreignId('period_id')->constrained('periods')->restrictOnDelete();
            $table->string('status')->default('submitted'); // cast to enum below
            $table->timestampTz('submitted_at')->useCurrent();
            $table->timestampTz('decision_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->unique(['student_id','institution_id','period_id'], 'uq_application_unique_per_period');
        });
        DB::statement("ALTER TABLE applications ALTER COLUMN status DROP DEFAULT;");
        DB::statement("ALTER TABLE applications ALTER COLUMN status TYPE application_status_enum USING status::application_status_enum;");
        DB::statement("ALTER TABLE applications ALTER COLUMN status SET DEFAULT 'submitted'::application_status_enum;");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_applications_student ON applications (student_id, period_id);");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_applications_institution ON applications (institution_id, period_id);");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_applications_status ON applications (status);");
        DB::statement(<<<'SQL'
        CREATE TRIGGER trg_applications_updated_at BEFORE UPDATE ON applications
        FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        SQL);

        // APPLICATION STATUS HISTORY
        Schema::create('application_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->timestampTz('changed_at')->useCurrent();
            $table->foreignId('changed_by')->nullable()->constrained('users');
        });
        DB::statement("ALTER TABLE application_status_history ALTER COLUMN from_status TYPE application_status_enum USING from_status::application_status_enum;");
        DB::statement("ALTER TABLE application_status_history ALTER COLUMN to_status TYPE application_status_enum USING to_status::application_status_enum;");

        // INTERNSHIPS
        Schema::create('internships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('institution_id')->constrained('institutions')->restrictOnDelete();
            $table->foreignId('period_id')->constrained('periods')->restrictOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('planned'); // cast to enum below
            $table->timestampsTz();
            $table->unique('application_id');
        });
        DB::statement("ALTER TABLE internships ALTER COLUMN status DROP DEFAULT;");
        DB::statement("ALTER TABLE internships ALTER COLUMN status TYPE internship_status_enum USING status::internship_status_enum;");
        DB::statement("ALTER TABLE internships ALTER COLUMN status SET DEFAULT 'planned'::internship_status_enum;");
        DB::statement("ALTER TABLE internships ADD CONSTRAINT chk_dates_valid CHECK (end_date IS NULL OR start_date IS NULL OR end_date >= start_date);");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_internships_student ON internships (student_id, period_id);");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_internships_institution ON internships (institution_id, period_id);");
        DB::statement(<<<'SQL'
        CREATE TRIGGER trg_internships_updated_at BEFORE UPDATE ON internships
        FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        SQL);
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS uq_internships_student_period ON internships(student_id, period_id);");

        // INTERNSHIP ↔ SUPERVISORS (N–M)
        Schema::create('internship_supervisors', function (Blueprint $table) {
            $table->foreignId('internship_id')->constrained('internships')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->constrained('supervisors')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->primary(['internship_id','supervisor_id']);
        });
        DB::statement("CREATE INDEX IF NOT EXISTS idx_internship_supervisors_primary ON internship_supervisors (internship_id) WHERE is_primary;");

        // MONITORING LOGS (dengan score)
        Schema::create('monitoring_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_id')->constrained('internships')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('supervisors')->nullOnDelete();
            $table->date('log_date')->useCurrent();
            $table->smallInteger('score')->nullable();
            $table->string('title', 150)->nullable();
            $table->text('content');
            $table->string('type')->default('weekly'); // cast to enum below
            $table->timestampsTz();
        });
        DB::statement("ALTER TABLE monitoring_logs ADD CONSTRAINT chk_score_range CHECK (score IS NULL OR score BETWEEN 0 AND 100);");
        DB::statement("ALTER TABLE monitoring_logs ALTER COLUMN type DROP DEFAULT;");
        DB::statement("ALTER TABLE monitoring_logs ALTER COLUMN type TYPE monitor_type_enum USING type::monitor_type_enum;");
        DB::statement("ALTER TABLE monitoring_logs ALTER COLUMN type SET DEFAULT 'weekly'::monitor_type_enum;");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_monitoring_logs_internship ON monitoring_logs (internship_id, log_date);");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_monitoring_logs_supervisor ON monitoring_logs (supervisor_id, log_date);");
        DB::statement(<<<'SQL'
        CREATE TRIGGER trg_monitoring_logs_updated_at BEFORE UPDATE ON monitoring_logs
        FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        SQL);

        // CACHE TABLES
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        // JOB TABLES
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // SESSIONS TABLE
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // =============================
        // 3) Business-rule triggers
        // =============================
        DB::statement(<<<'SQL'
        CREATE OR REPLACE FUNCTION enforce_role_student() RETURNS trigger AS $$
        DECLARE r user_role_enum;
        BEGIN
          SELECT role INTO r FROM users WHERE id = NEW.user_id FOR UPDATE;
          IF r <> 'student' THEN RAISE EXCEPTION 'User % is not role=student', NEW.user_id; END IF;
          RETURN NEW;
        END $$ LANGUAGE plpgsql;
        SQL);
        DB::statement(<<<'SQL'
        CREATE TRIGGER trg_students_role BEFORE INSERT OR UPDATE OF user_id ON students
        FOR EACH ROW EXECUTE FUNCTION enforce_role_student();
        SQL);

        DB::statement(<<<'SQL'
        CREATE OR REPLACE FUNCTION enforce_role_supervisor() RETURNS trigger AS $$
        DECLARE r user_role_enum;
        BEGIN
          SELECT role INTO r FROM users WHERE id = NEW.user_id FOR UPDATE;
          IF r <> 'supervisor' THEN RAISE EXCEPTION 'User % is not role=supervisor', NEW.user_id; END IF;
          RETURN NEW;
        END $$ LANGUAGE plpgsql;
        SQL);
        DB::statement(<<<'SQL'
        CREATE TRIGGER trg_supervisors_role BEFORE INSERT OR UPDATE OF user_id ON supervisors
        FOR EACH ROW EXECUTE FUNCTION enforce_role_supervisor();
        SQL);

        DB::statement(<<<'SQL'
        CREATE OR REPLACE FUNCTION log_app_status_change() RETURNS trigger AS $$
        BEGIN
          IF TG_OP='UPDATE' AND NEW.status IS DISTINCT FROM OLD.status THEN
            INSERT INTO application_status_history(application_id, from_status, to_status)
            VALUES (NEW.id, OLD.status, NEW.status);
          END IF;
          RETURN NEW;
        END $$ LANGUAGE plpgsql;
        SQL);
        DB::statement(<<<'SQL'
        CREATE TRIGGER trg_app_log_status AFTER UPDATE OF status ON applications
        FOR EACH ROW EXECUTE FUNCTION log_app_status_change();
        SQL);

        DB::statement(<<<'SQL'
        CREATE OR REPLACE FUNCTION ensure_quota_exists_on_accept() RETURNS trigger AS $$
        DECLARE v_exists INTEGER;
        BEGIN
          IF (TG_OP='INSERT' AND NEW.status='accepted') OR
             (TG_OP='UPDATE' AND NEW.status='accepted' AND (OLD.status IS DISTINCT FROM 'accepted')) THEN
             SELECT 1 INTO v_exists FROM institution_quotas
               WHERE institution_id = NEW.institution_id AND period_id = NEW.period_id
               FOR UPDATE;
             IF v_exists IS NULL THEN
               RAISE EXCEPTION 'Quota not set for institution_id=% period_id=%', NEW.institution_id, NEW.period_id;
             END IF;
          END IF;
          RETURN NEW;
        END $$ LANGUAGE plpgsql;
        SQL);
        DB::statement(<<<'SQL'
        CREATE TRIGGER trg_app_check_quota_exists BEFORE INSERT OR UPDATE OF status ON applications
        FOR EACH ROW EXECUTE FUNCTION ensure_quota_exists_on_accept();
        SQL);

        DB::statement(<<<'SQL'
        CREATE OR REPLACE FUNCTION bump_quota_used() RETURNS trigger AS $$
        BEGIN
          IF TG_OP='UPDATE' THEN
            IF OLD.status <> 'accepted' AND NEW.status='accepted' THEN
              UPDATE institution_quotas SET used = used + 1
              WHERE institution_id = NEW.institution_id AND period_id = NEW.period_id;
            ELSIF OLD.status='accepted' AND NEW.status <> 'accepted' THEN
              UPDATE institution_quotas SET used = GREATEST(0, used - 1)
              WHERE institution_id = NEW.institution_id AND period_id = NEW.period_id;
            END IF;
          END IF;
          RETURN NEW;
        END $$ LANGUAGE plpgsql;
        SQL);
        DB::statement(<<<'SQL'
        CREATE TRIGGER trg_app_bump_quota AFTER UPDATE OF status ON applications
        FOR EACH ROW EXECUTE FUNCTION bump_quota_used();
        SQL);

        DB::statement(<<<'SQL'
        CREATE OR REPLACE FUNCTION enforce_internship_from_accepted_application()
        RETURNS trigger LANGUAGE plpgsql AS $$
        DECLARE app RECORD;
        BEGIN
          SELECT * INTO app FROM applications WHERE id = NEW.application_id FOR UPDATE;
          IF app IS NULL THEN RAISE EXCEPTION 'Application % not found', NEW.application_id; END IF;
          IF app.status <> 'accepted' THEN RAISE EXCEPTION 'Application % is not accepted', NEW.application_id; END IF;
          NEW.student_id := app.student_id;
          NEW.institution_id := app.institution_id;
          NEW.period_id := app.period_id;
          RETURN NEW;
        END $$;
        SQL);
        DB::statement(<<<'SQL'
        CREATE TRIGGER trg_internship_enforce_app BEFORE INSERT OR UPDATE OF application_id ON internships
        FOR EACH ROW EXECUTE FUNCTION enforce_internship_from_accepted_application();
        SQL);
    }

    public function down(): void
    {
        // Drop business-rule triggers
        DB::statement("DROP TRIGGER IF EXISTS trg_internship_enforce_app ON internships;");
        DB::statement("DROP TRIGGER IF EXISTS trg_app_bump_quota ON applications;");
        DB::statement("DROP TRIGGER IF EXISTS trg_app_check_quota_exists ON applications;");
        DB::statement("DROP TRIGGER IF EXISTS trg_app_log_status ON applications;");
        DB::statement("DROP TRIGGER IF EXISTS trg_supervisors_role ON supervisors;");
        DB::statement("DROP TRIGGER IF EXISTS trg_students_role ON students;");

        // Drop table triggers
        DB::statement("DROP TRIGGER IF EXISTS trg_monitoring_logs_updated_at ON monitoring_logs;");
        DB::statement("DROP TRIGGER IF EXISTS trg_internships_updated_at ON internships;");
        DB::statement("DROP TRIGGER IF EXISTS trg_applications_updated_at ON applications;");
        DB::statement("DROP TRIGGER IF EXISTS trg_institution_quotas_updated_at ON institution_quotas;");
        DB::statement("DROP TRIGGER IF EXISTS trg_inst_quota_validate ON institution_quotas;");
        DB::statement("DROP TRIGGER IF EXISTS trg_institution_contacts_updated_at ON institution_contacts;");
        DB::statement("DROP TRIGGER IF EXISTS trg_institutions_updated_at ON institutions;");
        DB::statement("DROP TRIGGER IF EXISTS trg_supervisors_updated_at ON supervisors;");
        DB::statement("DROP TRIGGER IF EXISTS trg_students_updated_at ON students;");
        DB::statement("DROP TRIGGER IF EXISTS trg_users_updated_at ON users;");

        // Drop tables (reverse order for FK safety)
        Schema::dropIfExists('monitoring_logs');
        Schema::dropIfExists('internship_supervisors');
        Schema::dropIfExists('internships');
        Schema::dropIfExists('application_status_history');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('institution_quotas');
        Schema::dropIfExists('institution_contacts');
        Schema::dropIfExists('institutions');
        Schema::dropIfExists('supervisors');
        Schema::dropIfExists('students');
        Schema::dropIfExists('users');
        Schema::dropIfExists('periods');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');

        // (Optional) Drop functions/enums if you are sure nothing else uses them
        // DB::statement("DROP FUNCTION IF EXISTS enforce_internship_from_accepted_application();");
        // DB::statement("DROP FUNCTION IF EXISTS bump_quota_used();");
        // DB::statement("DROP FUNCTION IF EXISTS ensure_quota_exists_on_accept();");
        // DB::statement("DROP FUNCTION IF EXISTS log_app_status_change();");
        // DB::statement("DROP FUNCTION IF EXISTS enforce_role_supervisor();");
        // DB::statement("DROP FUNCTION IF EXISTS enforce_role_student();");
        // DB::statement("DROP FUNCTION IF EXISTS validate_quota_not_over();");
        // DB::statement("DROP FUNCTION IF EXISTS set_updated_at();");
        // DB::statement("DROP TYPE IF EXISTS monitor_type_enum;");
        // DB::statement("DROP TYPE IF EXISTS internship_status_enum;");
        // DB::statement("DROP TYPE IF EXISTS application_status_enum;");
        // DB::statement("DROP TYPE IF EXISTS user_role_enum;");
    }
};
