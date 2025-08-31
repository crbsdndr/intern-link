-- Internish Database (clean snake_case) – FIXED for PostgreSQL
-- Main fix: PostgreSQL TIDAK mendukung DEFERRABLE pada CHECK constraint.
-- Saya ganti aturan `used <= quota` dengan TRIGGER validator.
-- Urutan sudah aman untuk dieksekusi sekaligus.

BEGIN;

-- ===============
-- 0) Extensions
-- ===============
CREATE EXTENSION IF NOT EXISTS citext;
-- CREATE EXTENSION IF NOT EXISTS pg_trgm; -- optional

-- ===============
-- 1) ENUM Types
-- ===============
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

-- ===============
-- 2) Utility triggers
-- ===============
CREATE OR REPLACE FUNCTION set_updated_at()
RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
  NEW.updated_at := now();
RETURN NEW;
END $$;

-- used <= quota validator (karena CHECK tidak bisa DEFERRABLE)
CREATE OR REPLACE FUNCTION validate_quota_not_over()
RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
  IF NEW.used > NEW.quota THEN
    RAISE EXCEPTION 'Quota exceeded: used=% > quota=% for institution_quotas.%', NEW.used, NEW.quota, NEW.id;
END IF;
RETURN NEW;
END $$;

-- ===============
-- 3) Reference: periods
-- ===============
CREATE TABLE IF NOT EXISTS periods (
    id    INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    year  INTEGER NOT NULL CHECK (year BETWEEN 2000 AND 2100),
    term  SMALLINT NOT NULL CHECK (term BETWEEN 1 AND 4),
    UNIQUE (year, term)
    );

-- ===============
-- 4) Core tables
-- ===============
CREATE TABLE IF NOT EXISTS users (
    id             INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    full_name      VARCHAR(150) NOT NULL,
    email          CITEXT NOT NULL UNIQUE,
    phone          VARCHAR(30),
    password_hash  TEXT NOT NULL,
    role           user_role_enum NOT NULL,
    created_at     TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at     TIMESTAMPTZ NOT NULL DEFAULT now()
    );
CREATE TRIGGER trg_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TABLE IF NOT EXISTS students (
    id             INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id        INTEGER NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    student_number VARCHAR(50) NOT NULL UNIQUE,
    national_sn    VARCHAR(50) NOT NULL UNIQUE,
    major          VARCHAR(100) NOT NULL,
    batch          VARCHAR(9) NOT NULL,
    notes          TEXT,
    photo          TEXT,
    created_at     TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at     TIMESTAMPTZ NOT NULL DEFAULT now()
    );
CREATE TRIGGER trg_students_updated_at BEFORE UPDATE ON students
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TABLE IF NOT EXISTS supervisors (
    id                 INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id            INTEGER NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    supervisor_number  VARCHAR(50) NOT NULL UNIQUE,
    department         VARCHAR(100) NOT NULL,
    notes              TEXT,
    photo              TEXT,
    created_at         TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at         TIMESTAMPTZ NOT NULL DEFAULT now()
    );
CREATE TRIGGER trg_supervisors_updated_at BEFORE UPDATE ON supervisors
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TABLE IF NOT EXISTS institutions (
    id           INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name         VARCHAR(150) NOT NULL UNIQUE,
    address      TEXT,
    city         VARCHAR(100),
    province     VARCHAR(100),
    website      TEXT,
    notes        TEXT,
    created_at   TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at   TIMESTAMPTZ NOT NULL DEFAULT now()
    );
CREATE TRIGGER trg_institutions_updated_at BEFORE UPDATE ON institutions
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TABLE IF NOT EXISTS institution_contacts (
    id              INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    institution_id  INTEGER NOT NULL REFERENCES institutions(id) ON DELETE CASCADE,
    name            VARCHAR(150) NOT NULL,
    email           VARCHAR(255),
    phone           VARCHAR(50),
    position        VARCHAR(100),
    is_primary      BOOLEAN NOT NULL DEFAULT FALSE,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT uq_inst_contact_email UNIQUE (institution_id, email)
    );
CREATE TRIGGER trg_institution_contacts_updated_at BEFORE UPDATE ON institution_contacts
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TABLE IF NOT EXISTS institution_quotas (
    id              INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    institution_id  INTEGER NOT NULL REFERENCES institutions(id) ON DELETE CASCADE,
    period_id       INTEGER NOT NULL REFERENCES periods(id) ON DELETE RESTRICT,
    quota           INTEGER NOT NULL CHECK (quota >= 0),
    used            INTEGER NOT NULL DEFAULT 0,
    notes           TEXT,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT uq_inst_quota_per_period UNIQUE (institution_id, period_id)
    );
CREATE INDEX IF NOT EXISTS idx_inst_quota_period ON institution_quotas (institution_id, period_id);
CREATE TRIGGER trg_inst_quota_validate BEFORE INSERT OR UPDATE ON institution_quotas
                                                            FOR EACH ROW EXECUTE FUNCTION validate_quota_not_over();
CREATE TRIGGER trg_institution_quotas_updated_at BEFORE UPDATE ON institution_quotas
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TABLE IF NOT EXISTS applications (
    id               INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    student_id       INTEGER NOT NULL REFERENCES students(id) ON DELETE CASCADE,
    institution_id   INTEGER NOT NULL REFERENCES institutions(id) ON DELETE RESTRICT,
    period_id        INTEGER NOT NULL REFERENCES periods(id) ON DELETE RESTRICT,
    status           application_status_enum NOT NULL DEFAULT 'submitted',
    submitted_at     TIMESTAMPTZ NOT NULL DEFAULT now(),
    decision_at      TIMESTAMPTZ,
    rejection_reason TEXT,
    notes            TEXT,
    created_at       TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at       TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT uq_application_unique_per_period UNIQUE (student_id, institution_id, period_id)
    );
CREATE INDEX IF NOT EXISTS idx_applications_student ON applications (student_id, period_id);
CREATE INDEX IF NOT EXISTS idx_applications_institution ON applications (institution_id, period_id);
CREATE INDEX IF NOT EXISTS idx_applications_status ON applications (status);
CREATE TRIGGER trg_applications_updated_at BEFORE UPDATE ON applications
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TABLE IF NOT EXISTS application_status_history (
    id             INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    application_id INTEGER NOT NULL REFERENCES applications(id) ON DELETE CASCADE,
    from_status    application_status_enum,
    to_status      application_status_enum NOT NULL,
    changed_at     TIMESTAMPTZ NOT NULL DEFAULT now(),
    changed_by     INTEGER REFERENCES users(id)
    );

CREATE TABLE IF NOT EXISTS internships (
    id              INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    application_id  INTEGER NOT NULL UNIQUE REFERENCES applications(id) ON DELETE CASCADE,
    student_id      INTEGER NOT NULL REFERENCES students(id) ON DELETE RESTRICT,
    institution_id  INTEGER NOT NULL REFERENCES institutions(id) ON DELETE RESTRICT,
    period_id       INTEGER NOT NULL REFERENCES periods(id) ON DELETE RESTRICT,
    start_date      DATE,
    end_date        DATE,
    status          internship_status_enum NOT NULL DEFAULT 'planned',
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    CHECK (end_date IS NULL OR start_date IS NULL OR end_date >= start_date)
    );
CREATE INDEX IF NOT EXISTS idx_internships_student ON internships (student_id, period_id);
CREATE INDEX IF NOT EXISTS idx_internships_institution ON internships (institution_id, period_id);
CREATE TRIGGER trg_internships_updated_at BEFORE UPDATE ON internships
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TABLE IF NOT EXISTS internship_supervisors (
    internship_id  INTEGER NOT NULL REFERENCES internships(id) ON DELETE CASCADE,
    supervisor_id  INTEGER NOT NULL REFERENCES supervisors(id) ON DELETE CASCADE,
    is_primary     BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (internship_id, supervisor_id)
    );
CREATE INDEX IF NOT EXISTS idx_internship_supervisors_primary ON internship_supervisors (internship_id) WHERE is_primary;

CREATE TABLE IF NOT EXISTS monitoring_logs (
    id             INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    internship_id  INTEGER NOT NULL REFERENCES internships(id) ON DELETE CASCADE,
    supervisor_id  INTEGER REFERENCES supervisors(id) ON DELETE SET NULL,
    log_date       DATE NOT NULL DEFAULT CURRENT_DATE,
    score SMALLINT CHECK (score BETWEEN 0 AND 100);
    title          VARCHAR(150),
    content        TEXT NOT NULL,
    type           monitor_type_enum NOT NULL DEFAULT 'weekly',
    created_at     TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at     TIMESTAMPTZ NOT NULL DEFAULT now()
    );
CREATE INDEX IF NOT EXISTS idx_monitoring_logs_internship ON monitoring_logs (internship_id, log_date);
CREATE INDEX IF NOT EXISTS idx_monitoring_logs_supervisor ON monitoring_logs (supervisor_id, log_date);
CREATE TRIGGER trg_monitoring_logs_updated_at BEFORE UPDATE ON monitoring_logs
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- ===============
-- 5) Business rules via triggers
-- ===============
-- Role enforcement
CREATE OR REPLACE FUNCTION enforce_role_student() RETURNS trigger AS $$
DECLARE r user_role_enum;
BEGIN
SELECT role INTO r FROM users WHERE id = NEW.user_id FOR UPDATE;
IF r <> 'student' THEN RAISE EXCEPTION 'User % is not role=student', NEW.user_id; END IF;
RETURN NEW;
END $$ LANGUAGE plpgsql;
CREATE TRIGGER trg_students_role BEFORE INSERT OR UPDATE OF user_id ON students
    FOR EACH ROW EXECUTE FUNCTION enforce_role_student();

CREATE OR REPLACE FUNCTION enforce_role_supervisor() RETURNS trigger AS $$
DECLARE r user_role_enum;
BEGIN
SELECT role INTO r FROM users WHERE id = NEW.user_id FOR UPDATE;
IF r <> 'supervisor' THEN RAISE EXCEPTION 'User % is not role=supervisor', NEW.user_id; END IF;
RETURN NEW;
END $$ LANGUAGE plpgsql;
CREATE TRIGGER trg_supervisors_role BEFORE INSERT OR UPDATE OF user_id ON supervisors
    FOR EACH ROW EXECUTE FUNCTION enforce_role_supervisor();

-- Application status history
CREATE OR REPLACE FUNCTION log_app_status_change() RETURNS trigger AS $$
BEGIN
  IF TG_OP='UPDATE' AND NEW.status IS DISTINCT FROM OLD.status THEN
    INSERT INTO application_status_history(application_id, from_status, to_status)
    VALUES (NEW.id, OLD.status, NEW.status);
END IF;
RETURN NEW;
END $$ LANGUAGE plpgsql;
CREATE TRIGGER trg_app_log_status AFTER UPDATE OF status ON applications
    FOR EACH ROW EXECUTE FUNCTION log_app_status_change();

-- Quota existence check on accept
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
CREATE TRIGGER trg_app_check_quota_exists BEFORE INSERT OR UPDATE OF status ON applications
    FOR EACH ROW EXECUTE FUNCTION ensure_quota_exists_on_accept();

-- Bump used counter when toggling accepted
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
CREATE TRIGGER trg_app_bump_quota AFTER UPDATE OF status ON applications
    FOR EACH ROW EXECUTE FUNCTION bump_quota_used();

-- Internship must derive from accepted application
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
CREATE TRIGGER trg_internship_enforce_app BEFORE INSERT OR UPDATE OF application_id ON internships
    FOR EACH ROW EXECUTE FUNCTION enforce_internship_from_accepted_application();

-- Forbid multiple internships per student per period
CREATE UNIQUE INDEX IF NOT EXISTS uq_internships_student_period ON internships(student_id, period_id);

-- ===============
-- 6) Views
-- ===============
CREATE OR REPLACE VIEW v_application_summary AS
SELECT a.id AS application_id,
       s.id AS student_id,
       u.full_name AS student_name,
       i.name AS institution_name,
       p.year AS period_year,
       p.term AS period_term,
       a.status,
       a.submitted_at,
       a.decision_at
FROM applications a
         JOIN students s   ON s.id = a.student_id
         JOIN users u      ON u.id = s.user_id
         JOIN institutions i ON i.id = a.institution_id
         JOIN periods p    ON p.id = a.period_id;

CREATE OR REPLACE VIEW v_internship_detail AS
SELECT it.id AS internship_id,
       it.status AS internship_status,
       it.start_date,
       it.end_date,
       u.full_name AS student_name,
       i.name AS institution_name,
       p.year AS period_year,
       p.term AS period_term,
       usv.full_name AS primary_supervisor_name
FROM internships it
         JOIN students s       ON s.id = it.student_id
         JOIN users u          ON u.id = s.user_id
         JOIN institutions i   ON i.id = it.institution_id
         JOIN periods p        ON p.id = it.period_id
         LEFT JOIN internship_supervisors its ON its.internship_id = it.id AND its.is_primary = TRUE
         LEFT JOIN supervisors sv     ON sv.id = its.supervisor_id
         LEFT JOIN users usv          ON usv.id = sv.user_id;

COMMIT;
