-- =========================================================
-- PostgreSQL schema (full) dengan manajemen kuota "aktif"
-- Aktif = {draft, submitted, under_review, accepted}
-- Non-aktif = {rejected, cancelled}
-- Fitur:
--  - Bump kuota pada INSERT/UPDATE/DELETE + pindah institusi/period
--  - Validasi used <= quota
--  - Cek eksistensi kuota ketika status menjadi aktif
--  - Limit aplikasi aktif per siswa per period (default 3)
--  - TTL hold_expires_at untuk cegah hoarding (butuh scheduler)
-- =========================================================

BEGIN;

-- 1) Extensions & ENUM types
CREATE EXTENSION IF NOT EXISTS citext;

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

-- 2) Helpers umum
CREATE OR REPLACE FUNCTION set_updated_at()
RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
  NEW.updated_at := now();
  RETURN NEW;
END $$;

-- Aktif vs non-aktif
CREATE OR REPLACE FUNCTION is_active_status(app_status application_status_enum)
RETURNS boolean LANGUAGE sql IMMUTABLE AS $$
  SELECT app_status IN ('draft','submitted','under_review','accepted')
$$;

-- 3) Validasi kuota
CREATE OR REPLACE FUNCTION validate_quota_not_over()
RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
  IF NEW.used > NEW.quota THEN
    RAISE EXCEPTION 'Quota exceeded: used=% > quota=% for institution_quotas.%', NEW.used, NEW.quota, NEW.id;
  END IF;
  RETURN NEW;
END $$;

-- 4) Master data
CREATE TABLE IF NOT EXISTS periods (
  id           bigserial PRIMARY KEY,
  year         integer NOT NULL,
  term         smallint NOT NULL,
  CONSTRAINT uq_periods_year_term UNIQUE (year, term),
  CONSTRAINT chk_period_year CHECK (year BETWEEN 2000 AND 2100),
  CONSTRAINT chk_period_term CHECK (term BETWEEN 1 AND 4)
);

CREATE TABLE IF NOT EXISTS users (
  id                 bigserial PRIMARY KEY,
  name               varchar(255) NOT NULL,
  email              citext NOT NULL UNIQUE,
  email_verified_at  timestamptz NULL,
  phone              varchar(15) NULL,
  password           varchar(255) NOT NULL,
  role               user_role_enum NOT NULL DEFAULT 'student',
  remember_token     varchar(100) NULL,
  created_at         timestamptz NOT NULL DEFAULT now(),
  updated_at         timestamptz NOT NULL DEFAULT now()
);
CREATE TRIGGER trg_users_updated_at
BEFORE UPDATE ON users
FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TABLE IF NOT EXISTS students (
  id              bigserial PRIMARY KEY,
  user_id         bigint NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  student_number  varchar(50) NOT NULL UNIQUE,
  national_sn     varchar(50) NOT NULL UNIQUE,
  major           varchar(100) NOT NULL,
  "class"         varchar(100) NOT NULL,
  batch           varchar(9) NOT NULL,
  notes           text NULL,
  photo           text NULL,
  created_at      timestamptz NOT NULL DEFAULT now(),
  updated_at      timestamptz NOT NULL DEFAULT now(),
  CONSTRAINT uq_students_user UNIQUE (user_id)
);
CREATE INDEX IF NOT EXISTS idx_students_class ON students ("class");
CREATE TRIGGER trg_students_updated_at
BEFORE UPDATE ON students
FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TABLE IF NOT EXISTS supervisors (
  id                  bigserial PRIMARY KEY,
  user_id             bigint NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  supervisor_number   varchar(50) NOT NULL UNIQUE,
  department          varchar(100) NOT NULL,
  notes               text NULL,
  photo               text NULL,
  created_at          timestamptz NOT NULL DEFAULT now(),
  updated_at          timestamptz NOT NULL DEFAULT now(),
  CONSTRAINT uq_supervisors_user UNIQUE (user_id)
);
CREATE TRIGGER trg_supervisors_updated_at
BEFORE UPDATE ON supervisors
FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- institutions
CREATE TABLE IF NOT EXISTS institutions (
  id         bigserial PRIMARY KEY,
  name       varchar(150) NOT NULL UNIQUE,
  address    text NULL,
  city       varchar(100) NULL,
  province   varchar(100) NULL,
  website    text NULL,
  industry   varchar(100) NOT NULL,
  notes      text NULL,
  photo      varchar(255) NULL,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);
CREATE INDEX IF NOT EXISTS idx_institutions_industry ON institutions (industry);
CREATE TRIGGER trg_institutions_updated_at
BEFORE UPDATE ON institutions
FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- institution_contacts
CREATE TABLE IF NOT EXISTS institution_contacts (
  id              bigserial PRIMARY KEY,
  institution_id  bigint NOT NULL REFERENCES institutions(id) ON DELETE CASCADE,
  name            varchar(150) NOT NULL,
  email           varchar(255) NULL,
  phone           varchar(50) NULL,
  position        varchar(100) NULL,
  is_primary      boolean NOT NULL DEFAULT FALSE,
  created_at      timestamptz NOT NULL DEFAULT now(),
  updated_at      timestamptz NOT NULL DEFAULT now(),
  CONSTRAINT uq_inst_contacts UNIQUE (institution_id, email)
);
CREATE TRIGGER trg_institution_contacts_updated_at
BEFORE UPDATE ON institution_contacts
FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- institution_quotas
CREATE TABLE IF NOT EXISTS institution_quotas (
  id              bigserial PRIMARY KEY,
  institution_id  bigint NOT NULL REFERENCES institutions(id) ON DELETE CASCADE,
  period_id       bigint NOT NULL REFERENCES periods(id) ON DELETE RESTRICT,
  quota           integer NOT NULL,
  used            integer NOT NULL DEFAULT 0,
  notes           text NULL,
  created_at      timestamptz NOT NULL DEFAULT now(),
  updated_at      timestamptz NOT NULL DEFAULT now(),
  CONSTRAINT uq_inst_quota UNIQUE (institution_id, period_id),
  CONSTRAINT chk_quota_nonneg CHECK (quota >= 0)
);
CREATE INDEX IF NOT EXISTS idx_inst_quota_period ON institution_quotas (institution_id, period_id);
CREATE TRIGGER trg_inst_quota_validate
BEFORE INSERT OR UPDATE ON institution_quotas
FOR EACH ROW EXECUTE FUNCTION validate_quota_not_over();
CREATE TRIGGER trg_institution_quotas_updated_at
BEFORE UPDATE ON institution_quotas
FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- 5) Applications dan kuota aktif
CREATE TABLE IF NOT EXISTS applications (
  id               bigserial PRIMARY KEY,
  student_id       bigint NOT NULL REFERENCES students(id) ON DELETE CASCADE,
  institution_id   bigint NOT NULL REFERENCES institutions(id) ON DELETE RESTRICT,
  period_id        bigint NOT NULL REFERENCES periods(id) ON DELETE RESTRICT,
  status           application_status_enum NOT NULL DEFAULT 'submitted',
  student_access   boolean NOT NULL DEFAULT FALSE,
  submitted_at     timestamptz NOT NULL DEFAULT now(),
  hold_expires_at  timestamptz NULL, -- TTL opsional untuk status aktif awal (misal draft)
  notes            text NULL,
  created_at       timestamptz NOT NULL DEFAULT now(),
  updated_at       timestamptz NOT NULL DEFAULT now(),
  CONSTRAINT uq_application_unique_per_period UNIQUE (student_id, institution_id, period_id)
);
CREATE INDEX IF NOT EXISTS idx_applications_student     ON applications (student_id, period_id);
CREATE INDEX IF NOT EXISTS idx_applications_institution ON applications (institution_id, period_id);
CREATE INDEX IF NOT EXISTS idx_applications_status      ON applications (status);
CREATE UNIQUE INDEX IF NOT EXISTS uq_app_id_period ON applications (id, period_id);
CREATE TRIGGER trg_applications_updated_at
BEFORE UPDATE ON applications
FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- Riwayat status
CREATE TABLE IF NOT EXISTS application_status_history (
  id             bigserial PRIMARY KEY,
  application_id bigint NOT NULL REFERENCES applications(id) ON DELETE CASCADE,
  from_status    application_status_enum NULL,
  to_status      application_status_enum NOT NULL,
  changed_at     timestamptz NOT NULL DEFAULT now(),
  changed_by     bigint NULL REFERENCES users(id)
);

-- Log status changes
CREATE OR REPLACE FUNCTION log_app_status_change() RETURNS trigger AS $$
BEGIN
  IF TG_OP='UPDATE' AND NEW.status IS DISTINCT FROM OLD.status THEN
    INSERT INTO application_status_history(application_id, from_status, to_status)
    VALUES (NEW.id, OLD.status, NEW.status);
  END IF;
  RETURN NEW;
END $$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_app_log_status ON applications;
CREATE TRIGGER trg_app_log_status
AFTER UPDATE OF status ON applications
FOR EACH ROW EXECUTE FUNCTION log_app_status_change();

-- Pastikan kuota ada saat menjadi aktif (INSERT atau transisi non-aktif -> aktif)
CREATE OR REPLACE FUNCTION ensure_quota_exists_on_active() RETURNS trigger AS $$
DECLARE v_exists int;
BEGIN
  IF (TG_OP='INSERT' AND is_active_status(NEW.status))
     OR (TG_OP='UPDATE' AND is_active_status(NEW.status) AND NOT is_active_status(OLD.status)) THEN
    SELECT 1 INTO v_exists FROM institution_quotas
     WHERE institution_id = NEW.institution_id AND period_id = NEW.period_id
     FOR UPDATE;
    IF v_exists IS NULL THEN
      RAISE EXCEPTION 'Quota not set for institution_id=% period_id=%', NEW.institution_id, NEW.period_id;
    END IF;
  END IF;
  RETURN NEW;
END $$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_app_check_quota_exists ON applications;
CREATE TRIGGER trg_app_check_quota_exists
BEFORE INSERT OR UPDATE OF status ON applications
FOR EACH ROW EXECUTE FUNCTION ensure_quota_exists_on_active();

-- Limit maksimum aplikasi aktif per siswa per period (default 3)
-- Ubah angka 3 sesuai kebijakan
CREATE OR REPLACE FUNCTION enforce_max_active_per_student()
RETURNS trigger LANGUAGE plpgsql AS $$
DECLARE cnt int;
BEGIN
  IF (TG_OP='INSERT' AND is_active_status(NEW.status))
     OR (TG_OP='UPDATE' AND is_active_status(NEW.status) AND NOT is_active_status(OLD.status)) THEN
    SELECT COUNT(*) INTO cnt
      FROM applications
      WHERE student_id = NEW.student_id
        AND period_id  = NEW.period_id
        AND is_active_status(status);
    IF cnt >= 3 THEN
      RAISE EXCEPTION 'Active application limit reached (3) for student_id=% period_id=%', NEW.student_id, NEW.period_id;
    END IF;
  END IF;
  RETURN NEW;
END $$;

DROP TRIGGER IF EXISTS trg_app_enforce_max_active ON applications;
CREATE TRIGGER trg_app_enforce_max_active
BEFORE INSERT OR UPDATE OF status ON applications
FOR EACH ROW EXECUTE FUNCTION enforce_max_active_per_student();

-- Set TTL hold_expires_at default saat INSERT aktif atau transisi non-aktif -> aktif (opsional)
CREATE OR REPLACE FUNCTION set_default_hold_ttl()
RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
  IF TG_OP='INSERT' THEN
    IF is_active_status(NEW.status) AND NEW.hold_expires_at IS NULL THEN
      NEW.hold_expires_at := now() + interval '24 hours';
    END IF;
  ELSIF TG_OP='UPDATE' THEN
    IF NOT is_active_status(OLD.status) AND is_active_status(NEW.status) AND NEW.hold_expires_at IS NULL THEN
      NEW.hold_expires_at := now() + interval '24 hours';
    END IF;
  END IF;
  RETURN NEW;
END $$;

DROP TRIGGER IF EXISTS trg_app_set_hold_ttl ON applications;
CREATE TRIGGER trg_app_set_hold_ttl
BEFORE INSERT OR UPDATE OF status ON applications
FOR EACH ROW EXECUTE FUNCTION set_default_hold_ttl();

-- Bump kuota pada INSERT/UPDATE/DELETE dan perpindahan institution/period
CREATE OR REPLACE FUNCTION bump_quota_used_active()
RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
  IF TG_OP='INSERT' THEN
    IF is_active_status(NEW.status) THEN
      UPDATE institution_quotas
        SET used = used + 1
      WHERE institution_id = NEW.institution_id
        AND period_id      = NEW.period_id;
    END IF;

  ELSIF TG_OP='UPDATE' THEN
    -- Pindah institusi/period ketika status tetap aktif
    IF is_active_status(OLD.status) AND is_active_status(NEW.status)
       AND (OLD.institution_id <> NEW.institution_id OR OLD.period_id <> NEW.period_id) THEN

      UPDATE institution_quotas
        SET used = GREATEST(0, used - 1)
      WHERE institution_id = OLD.institution_id
        AND period_id      = OLD.period_id;

      UPDATE institution_quotas
        SET used = used + 1
      WHERE institution_id = NEW.institution_id
        AND period_id      = NEW.period_id;

    -- Transisi non-aktif -> aktif
    ELSIF NOT is_active_status(OLD.status) AND is_active_status(NEW.status) THEN
      UPDATE institution_quotas
        SET used = used + 1
      WHERE institution_id = NEW.institution_id
        AND period_id      = NEW.period_id;

    -- Transisi aktif -> non-aktif
    ELSIF is_active_status(OLD.status) AND NOT is_active_status(NEW.status) THEN
      UPDATE institution_quotas
        SET used = GREATEST(0, used - 1)
      WHERE institution_id = OLD.institution_id
        AND period_id      = OLD.period_id;
    END IF;

  ELSIF TG_OP='DELETE' THEN
    IF is_active_status(OLD.status) THEN
      UPDATE institution_quotas
        SET used = GREATEST(0, used - 1)
      WHERE institution_id = OLD.institution_id
        AND period_id      = OLD.period_id;
    END IF;
  END IF;

  RETURN NEW;
END $$;

-- Pasang trigger kuota
DROP TRIGGER IF EXISTS trg_app_bump_quota_ins ON applications;
DROP TRIGGER IF EXISTS trg_app_bump_quota_upd ON applications;
DROP TRIGGER IF EXISTS trg_app_bump_quota_del ON applications;

CREATE TRIGGER trg_app_bump_quota_ins
AFTER INSERT ON applications
FOR EACH ROW EXECUTE FUNCTION bump_quota_used_active();

CREATE TRIGGER trg_app_bump_quota_upd
AFTER UPDATE OF status, institution_id, period_id ON applications
FOR EACH ROW EXECUTE FUNCTION bump_quota_used_active();

CREATE TRIGGER trg_app_bump_quota_del
AFTER DELETE ON applications
FOR EACH ROW EXECUTE FUNCTION bump_quota_used_active();

-- Opsional: fungsi batch untuk auto-cancel hold yang kadaluarsa (dipanggil via scheduler)
CREATE OR REPLACE FUNCTION cancel_expired_holds()
RETURNS integer LANGUAGE plpgsql AS $$
DECLARE v_count int;
BEGIN
  UPDATE applications a
     SET status = 'cancelled',
         updated_at = now()
   WHERE is_active_status(a.status)
     AND a.hold_expires_at IS NOT NULL
     AND a.hold_expires_at < now();
  GET DIAGNOSTICS v_count = ROW_COUNT;
  RETURN v_count;
END $$;

-- 6) Internships
CREATE TABLE IF NOT EXISTS internships (
  id              bigserial PRIMARY KEY,
  application_id  bigint NOT NULL,
  student_id      bigint NOT NULL REFERENCES students(id) ON DELETE RESTRICT,
  institution_id  bigint NOT NULL REFERENCES institutions(id) ON DELETE RESTRICT,
  period_id       bigint NOT NULL REFERENCES periods(id) ON DELETE RESTRICT,
  start_date      date NULL,
  end_date        date NULL,
  status          internship_status_enum NOT NULL DEFAULT 'planned',
  created_at      timestamptz NOT NULL DEFAULT now(),
  updated_at      timestamptz NOT NULL DEFAULT now(),
  CONSTRAINT uq_internships_application UNIQUE (application_id),
  CONSTRAINT chk_dates_valid CHECK (end_date IS NULL OR start_date IS NULL OR end_date >= start_date),
  CONSTRAINT fk_internships_app_period
    FOREIGN KEY (application_id, period_id)
    REFERENCES applications (id, period_id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_internships_student     ON internships (student_id, period_id);
CREATE INDEX IF NOT EXISTS idx_internships_institution ON internships (institution_id, period_id);
CREATE UNIQUE INDEX IF NOT EXISTS uq_internships_student_period ON internships (student_id, period_id);
CREATE TRIGGER trg_internships_updated_at
BEFORE UPDATE ON internships
FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- Internship harus dari application accepted (dan sinkronkan FK turunannya)
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

DROP TRIGGER IF EXISTS trg_internship_enforce_app ON internships;
CREATE TRIGGER trg_internship_enforce_app
BEFORE INSERT OR UPDATE OF application_id ON internships
FOR EACH ROW EXECUTE FUNCTION enforce_internship_from_accepted_application();

-- 7) Monitoring
CREATE TABLE IF NOT EXISTS internship_supervisors (
  internship_id bigint NOT NULL REFERENCES internships(id) ON DELETE CASCADE,
  supervisor_id bigint NOT NULL REFERENCES supervisors(id) ON DELETE CASCADE,
  is_primary    boolean NOT NULL DEFAULT FALSE,
  PRIMARY KEY (internship_id, supervisor_id)
);
CREATE INDEX IF NOT EXISTS idx_internship_supervisors_primary
ON internship_supervisors (internship_id) WHERE is_primary;

CREATE TABLE IF NOT EXISTS monitoring_logs (
  id             bigserial PRIMARY KEY,
  internship_id  bigint NOT NULL REFERENCES internships(id) ON DELETE CASCADE,
  supervisor_id  bigint NULL REFERENCES supervisors(id) ON DELETE SET NULL,
  log_date       date NOT NULL DEFAULT CURRENT_DATE,
  score          smallint NULL,
  title          varchar(150) NULL,
  content        text NOT NULL,
  type           monitor_type_enum NOT NULL DEFAULT 'weekly',
  created_at     timestamptz NOT NULL DEFAULT now(),
  updated_at     timestamptz NOT NULL DEFAULT now(),
  CONSTRAINT chk_score_range CHECK (score IS NULL OR score BETWEEN 0 AND 100)
);
CREATE INDEX IF NOT EXISTS idx_monitoring_logs_internship ON monitoring_logs (internship_id, log_date);
CREATE INDEX IF NOT EXISTS idx_monitoring_logs_supervisor ON monitoring_logs (supervisor_id, log_date);
CREATE TRIGGER trg_monitoring_logs_updated_at
BEFORE UPDATE ON monitoring_logs
FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- 8) Laravel infra tables
CREATE TABLE IF NOT EXISTS cache (
  key         varchar(255) PRIMARY KEY,
  value       text NOT NULL,
  expiration  integer NOT NULL
);

CREATE TABLE IF NOT EXISTS cache_locks (
  key         varchar(255) PRIMARY KEY,
  owner       varchar(255) NOT NULL,
  expiration  integer NOT NULL
);

CREATE TABLE IF NOT EXISTS jobs (
  id            bigserial PRIMARY KEY,
  queue         varchar(255) NOT NULL,
  payload       text NOT NULL,
  attempts      smallint NOT NULL,
  reserved_at   integer NULL,
  available_at  integer NOT NULL,
  created_at    integer NOT NULL,
  CONSTRAINT chk_jobs_attempts_range CHECK (attempts >= 0 AND attempts <= 255)
);
CREATE INDEX IF NOT EXISTS jobs_queue_index ON jobs (queue);

CREATE TABLE IF NOT EXISTS job_batches (
  id             varchar(255) PRIMARY KEY,
  name           varchar(255) NOT NULL,
  total_jobs     integer NOT NULL,
  pending_jobs   integer NOT NULL,
  failed_jobs    integer NOT NULL,
  failed_job_ids text NOT NULL,
  options        text NULL,
  cancelled_at   integer NULL,
  created_at     integer NOT NULL,
  finished_at    integer NULL
);

CREATE TABLE IF NOT EXISTS failed_jobs (
  id         bigserial PRIMARY KEY,
  uuid       varchar(255) NOT NULL UNIQUE,
  connection text NOT NULL,
  queue      text NOT NULL,
  payload    text NOT NULL,
  exception  text NOT NULL,
  failed_at  timestamp NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS sessions (
  id            varchar(255) PRIMARY KEY,
  user_id       bigint NULL REFERENCES users(id),
  ip_address    varchar(45) NULL,
  user_agent    text NULL,
  payload       text NOT NULL,
  last_activity integer NOT NULL
);
CREATE INDEX IF NOT EXISTS sessions_user_id_index ON sessions (user_id);
CREATE INDEX IF NOT EXISTS sessions_last_activity_index ON sessions (last_activity);

-- 9) Role enforcement
CREATE OR REPLACE FUNCTION enforce_role_student() RETURNS trigger AS $$
DECLARE r user_role_enum;
BEGIN
  SELECT role INTO r FROM users WHERE id = NEW.user_id FOR UPDATE;
  IF r <> 'student' THEN RAISE EXCEPTION 'User % is not role=student', NEW.user_id; END IF;
  RETURN NEW;
END $$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_students_role ON students;
CREATE TRIGGER trg_students_role
BEFORE INSERT OR UPDATE OF user_id ON students
FOR EACH ROW EXECUTE FUNCTION enforce_role_student();

CREATE OR REPLACE FUNCTION enforce_role_supervisor() RETURNS trigger AS $$
DECLARE r user_role_enum;
BEGIN
  SELECT role INTO r FROM users WHERE id = NEW.user_id FOR UPDATE;
  IF r <> 'supervisor' THEN RAISE EXCEPTION 'User % is not role=supervisor', NEW.user_id; END IF;
  RETURN NEW;
END $$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_supervisors_role ON supervisors;
CREATE TRIGGER trg_supervisors_role
BEFORE INSERT OR UPDATE OF user_id ON supervisors
FOR EACH ROW EXECUTE FUNCTION enforce_role_supervisor();

COMMIT;
