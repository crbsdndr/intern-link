-- Internish Database schema generated from Laravel migrations
BEGIN;

-- ============================
-- 0) Extensions
-- ============================
CREATE EXTENSION IF NOT EXISTS citext;

-- ============================
-- 1) ENUM Types
-- ============================
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
END$$;

-- ============================
-- 2) Utility functions & validators
-- ============================
CREATE OR REPLACE FUNCTION set_updated_at() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
  NEW.updated_at := now();
  RETURN NEW;
END$$;

CREATE OR REPLACE FUNCTION validate_quota_not_over() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
  IF NEW.used > NEW.quota THEN
    RAISE EXCEPTION 'Quota exceeded: used=% > quota=% for institution_quotas.%', NEW.used, NEW.quota, NEW.id;
  END IF;
  RETURN NEW;
END$$;

CREATE OR REPLACE FUNCTION enforce_role_student() RETURNS trigger LANGUAGE plpgsql AS $$
DECLARE r user_role_enum;
BEGIN
  SELECT role INTO r FROM users WHERE id = NEW.user_id FOR UPDATE;
  IF r <> 'student' THEN RAISE EXCEPTION 'User % is not role=student', NEW.user_id; END IF;
  RETURN NEW;
END$$;

CREATE OR REPLACE FUNCTION enforce_role_supervisor() RETURNS trigger LANGUAGE plpgsql AS $$
DECLARE r user_role_enum;
BEGIN
  SELECT role INTO r FROM users WHERE id = NEW.user_id FOR UPDATE;
  IF r <> 'supervisor' THEN RAISE EXCEPTION 'User % is not role=supervisor', NEW.user_id; END IF;
  RETURN NEW;
END$$;

CREATE OR REPLACE FUNCTION log_app_status_change() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
  IF TG_OP='UPDATE' AND NEW.status IS DISTINCT FROM OLD.status THEN
    INSERT INTO application_status_history(application_id, from_status, to_status)
    VALUES (NEW.id, OLD.status, NEW.status);
  END IF;
  RETURN NEW;
END$$;

CREATE OR REPLACE FUNCTION ensure_quota_exists_on_accept() RETURNS trigger LANGUAGE plpgsql AS $$
DECLARE v_exists INTEGER;
BEGIN
  IF (TG_OP='INSERT' AND NEW.status='accepted') OR
     (TG_OP='UPDATE' AND NEW.status='accepted' AND OLD.status IS DISTINCT FROM 'accepted') THEN
    SELECT 1 INTO v_exists FROM institution_quotas
      WHERE institution_id = NEW.institution_id AND period_id = NEW.period_id
      FOR UPDATE;
    IF v_exists IS NULL THEN
      RAISE EXCEPTION 'Quota not set for institution_id=% period_id=%', NEW.institution_id, NEW.period_id;
    END IF;
  END IF;
  RETURN NEW;
END$$;

CREATE OR REPLACE FUNCTION bump_quota_used() RETURNS trigger LANGUAGE plpgsql AS $$
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
END$$;

CREATE OR REPLACE FUNCTION enforce_internship_from_accepted_application() RETURNS trigger LANGUAGE plpgsql AS $$
DECLARE app RECORD;
BEGIN
  SELECT * INTO app FROM applications WHERE id = NEW.application_id FOR UPDATE;
  IF app IS NULL THEN
    RAISE EXCEPTION 'Application % not found', NEW.application_id;
  END IF;
  IF app.status <> 'accepted' THEN
    RAISE EXCEPTION 'Application % is not accepted', NEW.application_id;
  END IF;
  NEW.student_id := app.student_id;
  NEW.institution_id := app.institution_id;
  NEW.period_id := app.period_id;
  RETURN NEW;
END$$;

-- ============================
-- 3) Reference tables
-- ============================
CREATE TABLE periods (
    id BIGSERIAL PRIMARY KEY,
    year INTEGER NOT NULL CHECK (year BETWEEN 2000 AND 2100),
    term SMALLINT NOT NULL CHECK (term BETWEEN 1 AND 4),
    UNIQUE (year, term)
);

-- ============================
-- 4) Core tables
-- ============================
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email CITEXT NOT NULL UNIQUE,
    email_verified_at TIMESTAMPTZ,
    phone VARCHAR(30),
    password VARCHAR(255) NOT NULL,
    role user_role_enum NOT NULL DEFAULT 'student',
    remember_token VARCHAR(100),
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE TRIGGER trg_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TABLE students (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    student_number VARCHAR(50) NOT NULL UNIQUE,
    national_sn VARCHAR(50) NOT NULL UNIQUE,
    major VARCHAR(100) NOT NULL,
    batch VARCHAR(9) NOT NULL,
    notes TEXT,
    photo TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE (user_id)
);
CREATE TRIGGER trg_students_updated_at BEFORE UPDATE ON students
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_students_role BEFORE INSERT OR UPDATE OF user_id ON students
    FOR EACH ROW EXECUTE FUNCTION enforce_role_student();

CREATE TABLE supervisors (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    supervisor_number VARCHAR(50) NOT NULL UNIQUE,
    department VARCHAR(100) NOT NULL,
    notes TEXT,
    photo TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE (user_id)
);
CREATE TRIGGER trg_supervisors_updated_at BEFORE UPDATE ON supervisors
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_supervisors_role BEFORE INSERT OR UPDATE OF user_id ON supervisors
    FOR EACH ROW EXECUTE FUNCTION enforce_role_supervisor();

CREATE TABLE institutions (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(100),
    website TEXT,
    notes TEXT,
    photo VARCHAR(255),
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE TRIGGER trg_institutions_updated_at BEFORE UPDATE ON institutions
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TABLE institution_contacts (
    id BIGSERIAL PRIMARY KEY,
    institution_id BIGINT NOT NULL REFERENCES institutions(id) ON DELETE CASCADE,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    position VARCHAR(100),
    is_primary BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT uq_inst_contact_email UNIQUE (institution_id, email)
);
CREATE TRIGGER trg_institution_contacts_updated_at BEFORE UPDATE ON institution_contacts
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TABLE institution_quotas (
    id BIGSERIAL PRIMARY KEY,
    institution_id BIGINT NOT NULL REFERENCES institutions(id) ON DELETE CASCADE,
    period_id BIGINT NOT NULL REFERENCES periods(id) ON DELETE RESTRICT,
    quota INTEGER NOT NULL CHECK (quota >= 0),
    used INTEGER NOT NULL DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT uq_inst_quota_per_period UNIQUE (institution_id, period_id)
);
CREATE INDEX idx_inst_quota_period ON institution_quotas (institution_id, period_id);
CREATE TRIGGER trg_inst_quota_validate BEFORE INSERT OR UPDATE ON institution_quotas
    FOR EACH ROW EXECUTE FUNCTION validate_quota_not_over();
CREATE TRIGGER trg_institution_quotas_updated_at BEFORE UPDATE ON institution_quotas
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TABLE applications (
    id BIGSERIAL PRIMARY KEY,
    student_id BIGINT NOT NULL REFERENCES students(id) ON DELETE CASCADE,
    institution_id BIGINT NOT NULL REFERENCES institutions(id) ON DELETE RESTRICT,
    period_id BIGINT NOT NULL REFERENCES periods(id) ON DELETE RESTRICT,
    status application_status_enum NOT NULL DEFAULT 'submitted',
    submitted_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    decision_at TIMESTAMPTZ,
    rejection_reason TEXT,
    notes TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT uq_application_unique_per_period UNIQUE (student_id, institution_id, period_id)
);
CREATE INDEX idx_applications_student ON applications (student_id, period_id);
CREATE INDEX idx_applications_institution ON applications (institution_id, period_id);
CREATE INDEX idx_applications_status ON applications (status);
CREATE TRIGGER trg_applications_updated_at BEFORE UPDATE ON applications
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_app_log_status AFTER UPDATE OF status ON applications
    FOR EACH ROW EXECUTE FUNCTION log_app_status_change();
CREATE TRIGGER trg_app_check_quota_exists BEFORE INSERT OR UPDATE OF status ON applications
    FOR EACH ROW EXECUTE FUNCTION ensure_quota_exists_on_accept();
CREATE TRIGGER trg_app_bump_quota AFTER UPDATE OF status ON applications
    FOR EACH ROW EXECUTE FUNCTION bump_quota_used();

CREATE TABLE application_status_history (
    id BIGSERIAL PRIMARY KEY,
    application_id BIGINT NOT NULL REFERENCES applications(id) ON DELETE CASCADE,
    from_status application_status_enum,
    to_status application_status_enum NOT NULL,
    changed_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    changed_by BIGINT REFERENCES users(id)
);

CREATE TABLE internships (
    id BIGSERIAL PRIMARY KEY,
    application_id BIGINT NOT NULL UNIQUE REFERENCES applications(id) ON DELETE CASCADE,
    student_id BIGINT NOT NULL REFERENCES students(id) ON DELETE RESTRICT,
    institution_id BIGINT NOT NULL REFERENCES institutions(id) ON DELETE RESTRICT,
    period_id BIGINT NOT NULL REFERENCES periods(id) ON DELETE RESTRICT,
    start_date DATE,
    end_date DATE,
    status internship_status_enum NOT NULL DEFAULT 'planned',
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    CHECK (end_date IS NULL OR start_date IS NULL OR end_date >= start_date)
);
CREATE INDEX idx_internships_student ON internships (student_id, period_id);
CREATE INDEX idx_internships_institution ON internships (institution_id, period_id);
CREATE TRIGGER trg_internships_updated_at BEFORE UPDATE ON internships
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_internship_enforce_app BEFORE INSERT OR UPDATE OF application_id ON internships
    FOR EACH ROW EXECUTE FUNCTION enforce_internship_from_accepted_application();
CREATE UNIQUE INDEX uq_internships_student_period ON internships (student_id, period_id);

CREATE TABLE internship_supervisors (
    internship_id BIGINT NOT NULL REFERENCES internships(id) ON DELETE CASCADE,
    supervisor_id BIGINT NOT NULL REFERENCES supervisors(id) ON DELETE CASCADE,
    is_primary BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (internship_id, supervisor_id)
);
CREATE INDEX idx_internship_supervisors_primary ON internship_supervisors (internship_id) WHERE is_primary;

CREATE TABLE monitoring_logs (
    id BIGSERIAL PRIMARY KEY,
    internship_id BIGINT NOT NULL REFERENCES internships(id) ON DELETE CASCADE,
    supervisor_id BIGINT REFERENCES supervisors(id) ON DELETE SET NULL,
    log_date DATE NOT NULL DEFAULT CURRENT_DATE,
    score SMALLINT CHECK (score BETWEEN 0 AND 100),
    title VARCHAR(150),
    content TEXT NOT NULL,
    type monitor_type_enum NOT NULL DEFAULT 'weekly',
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE INDEX idx_monitoring_logs_internship ON monitoring_logs (internship_id, log_date);
CREATE INDEX idx_monitoring_logs_supervisor ON monitoring_logs (supervisor_id, log_date);
CREATE TRIGGER trg_monitoring_logs_updated_at BEFORE UPDATE ON monitoring_logs
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- ============================
-- 5) Default Laravel tables
-- ============================
CREATE TABLE cache (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT NOT NULL,
    expiration INTEGER NOT NULL
);
CREATE TABLE cache_locks (
    key VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INTEGER NOT NULL
);

CREATE TABLE jobs (
    id BIGSERIAL PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    attempts SMALLINT NOT NULL,
    reserved_at INTEGER,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
);
CREATE INDEX jobs_queue_index ON jobs (queue);

CREATE TABLE job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INTEGER NOT NULL,
    pending_jobs INTEGER NOT NULL,
    failed_jobs INTEGER NOT NULL,
    failed_job_ids TEXT NOT NULL,
    options TEXT,
    cancelled_at INTEGER,
    created_at INTEGER NOT NULL,
    finished_at INTEGER
);

CREATE TABLE failed_jobs (
    id BIGSERIAL PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload TEXT NOT NULL,
    exception TEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);
CREATE INDEX sessions_user_id_index ON sessions (user_id);
CREATE INDEX sessions_last_activity_index ON sessions (last_activity);

-- ============================
-- 6) Views
-- ============================
CREATE OR REPLACE VIEW student_details_view AS
SELECT s.id,
       u.id AS user_id,
       u.name,
       u.email,
       u.phone,
       u.role,
       s.student_number,
       s.national_sn,
       s.major,
       s.batch,
       s.notes,
       s.photo,
       s.created_at,
       s.updated_at
FROM students s
JOIN users u ON s.user_id = u.id;

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
FROM supervisors s
JOIN users u ON s.user_id = u.id;

CREATE OR REPLACE VIEW institution_details_view AS
WITH primary_contact AS (
    SELECT DISTINCT ON (institution_id) id, institution_id, name, email, phone, position, is_primary
    FROM institution_contacts
    ORDER BY institution_id, is_primary DESC, id
),
latest_quota AS (
    SELECT DISTINCT ON (institution_id) iq.id, iq.institution_id, iq.quota, iq.used, iq.notes, p.year, p.term
    FROM institution_quotas iq
    JOIN periods p ON p.id = iq.period_id
    ORDER BY iq.institution_id, p.year DESC, p.term DESC, iq.id DESC
)
SELECT i.id,
       i.name,
       i.address,
       i.city,
       i.province,
       i.website,
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
       lq.used,
       lq.notes AS quota_notes
FROM institutions i
LEFT JOIN primary_contact pc ON pc.institution_id = i.id
LEFT JOIN latest_quota lq ON lq.institution_id = i.id;

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

CREATE OR REPLACE VIEW v_monitoring_log_summary AS
SELECT
  ml.id                    AS monitoring_log_id,
  ml.log_date,
  ml.type                  AS log_type,
  ml.score,
  COALESCE(ml.title, NULL) AS title,
  ml.content,
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

CREATE OR REPLACE VIEW v_monitoring_log_detail AS
SELECT
  ml.id            AS monitoring_log_id,
  ml.log_date,
  ml.type          AS log_type,
  ml.score,
  ml.title,
  ml.content,
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

CREATE OR REPLACE VIEW v_application_summary AS
SELECT a.id AS application_id,
       s.id AS student_id,
       u.name AS student_name,
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
       u.name AS student_name,
       i.name AS institution_name,
       p.year AS period_year,
       p.term AS period_term,
       usv.name AS primary_supervisor_name
FROM internships it
JOIN students s       ON s.id = it.student_id
JOIN users u          ON u.id = s.user_id
JOIN institutions i   ON i.id = it.institution_id
JOIN periods p        ON p.id = it.period_id
LEFT JOIN internship_supervisors its ON its.internship_id = it.id AND its.is_primary = TRUE
LEFT JOIN supervisors sv     ON sv.id = its.supervisor_id
LEFT JOIN users usv          ON usv.id = sv.user_id;

COMMIT;
