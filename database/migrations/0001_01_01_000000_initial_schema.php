<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
        CREATE SCHEMA IF NOT EXISTS core;
        CREATE SCHEMA IF NOT EXISTS app;

        CREATE EXTENSION IF NOT EXISTS citext;

        SET search_path = app, core, public;
        DO $$
        BEGIN
          IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'user_role_enum') THEN
            CREATE TYPE public.user_role_enum AS ENUM ('student','supervisor','admin','developer');
          END IF;
          IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'application_status_enum') THEN
            CREATE TYPE public.application_status_enum AS ENUM ('draft','submitted','under_review','accepted','rejected','cancelled');
          END IF;
          IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'internship_status_enum') THEN
            CREATE TYPE public.internship_status_enum AS ENUM ('planned','ongoing','completed','terminated');
          END IF;
          IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'monitor_type_enum') THEN
            CREATE TYPE public.monitor_type_enum AS ENUM ('weekly','issue','final','other');
          END IF;
        END $$;

        CREATE OR REPLACE FUNCTION app.set_updated_at()
        RETURNS trigger LANGUAGE plpgsql AS $$
        BEGIN
          NEW.updated_at := now();
          RETURN NEW;
        END $$;

        CREATE OR REPLACE FUNCTION app.is_active_status(app_status public.application_status_enum)
        RETURNS boolean LANGUAGE sql IMMUTABLE AS $$
          SELECT app_status IN ('draft','submitted','under_review','accepted')
        $$;

        CREATE OR REPLACE FUNCTION app.app_current_actor() RETURNS bigint
        LANGUAGE plpgsql AS $$
        DECLARE v bigint;
        BEGIN
          SELECT NULLIF(current_setting('app.user_id', true), '')::bigint INTO v;
          RETURN v;
        END $$;

        CREATE OR REPLACE FUNCTION app.assert_not_student(p_user_id bigint) RETURNS void
        LANGUAGE plpgsql AS $$
        DECLARE r public.user_role_enum;
        BEGIN
          IF p_user_id IS NULL THEN
            RETURN;
          END IF;

          SELECT role INTO r FROM core.users WHERE id = p_user_id;
          IF r IS NULL THEN
            RAISE EXCEPTION 'changed_by user % not found', p_user_id;
          ELSIF r = 'student' THEN
            RAISE EXCEPTION 'changed_by user % has forbidden role=student', p_user_id;
          END IF;
        END $$;

        CREATE OR REPLACE FUNCTION app.validate_quota_not_over()
        RETURNS trigger LANGUAGE plpgsql AS $$
        BEGIN
          IF NEW.used > NEW.quota THEN
            RAISE EXCEPTION 'Quota exceeded: used=% > quota=% for institution_quotas.%', NEW.used, NEW.quota, NEW.id;
          END IF;
          RETURN NEW;
        END $$;

        CREATE TABLE IF NOT EXISTS core.users (
          id                 bigserial PRIMARY KEY,
          name               varchar(255) NOT NULL,
          email              citext NOT NULL UNIQUE,
          email_verified_at  timestamptz NULL,
          phone              varchar(15) NULL,
          password           varchar(255) NOT NULL,
          role               public.user_role_enum NOT NULL DEFAULT 'student',
          remember_token     varchar(100) NULL,
          created_at         timestamptz NOT NULL DEFAULT now(),
          updated_at         timestamptz NOT NULL DEFAULT now()
        );

        DO $$
        BEGIN
          IF NOT EXISTS (
            SELECT 1 FROM pg_trigger
            WHERE tgname = 'trg_core_users_updated_at'
          ) THEN
            CREATE TRIGGER trg_core_users_updated_at
            BEFORE UPDATE ON core.users
            FOR EACH ROW EXECUTE FUNCTION app.set_updated_at();
          END IF;
        END $$;

        CREATE TABLE IF NOT EXISTS core.cache (
          key         varchar(255) PRIMARY KEY,
          value       text NOT NULL,
          expiration  integer NOT NULL
        );

        CREATE TABLE IF NOT EXISTS core.cache_locks (
          key         varchar(255) PRIMARY KEY,
          owner       varchar(255) NOT NULL,
          expiration  integer NOT NULL
        );

        CREATE TABLE IF NOT EXISTS core.jobs (
          id            bigserial PRIMARY KEY,
          queue         varchar(255) NOT NULL,
          payload       text NOT NULL,
          attempts      smallint NOT NULL,
          reserved_at   integer NULL,
          available_at  integer NOT NULL,
          created_at    integer NOT NULL,
          CONSTRAINT chk_jobs_attempts_range CHECK (attempts >= 0 AND attempts <= 255)
        );
        CREATE INDEX IF NOT EXISTS jobs_queue_index ON core.jobs (queue);

        CREATE TABLE IF NOT EXISTS core.job_batches (
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

        CREATE TABLE IF NOT EXISTS core.failed_jobs (
          id         bigserial PRIMARY KEY,
          uuid       varchar(255) NOT NULL UNIQUE,
          connection text NOT NULL,
          queue      text NOT NULL,
          payload    text NOT NULL,
          exception  text NOT NULL,
          failed_at  timestamp NOT NULL DEFAULT now()
        );

        CREATE TABLE IF NOT EXISTS core.sessions (
          id            varchar(255) PRIMARY KEY,
          user_id       bigint NULL REFERENCES core.users(id),
          ip_address    varchar(45) NULL,
          user_agent    text NULL,
          payload       text NOT NULL,
          last_activity integer NOT NULL
        );
        CREATE INDEX IF NOT EXISTS sessions_user_id_index ON core.sessions (user_id);
        CREATE INDEX IF NOT EXISTS sessions_last_activity_index ON core.sessions (last_activity);
        SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
          DO $$
          BEGIN
            IF EXISTS (
              SELECT 1 FROM pg_trigger WHERE tgname = 'trg_core_users_updated_at'
            ) THEN
              DROP TRIGGER trg_core_users_updated_at ON core.users;
            END IF;
          END $$;

          DROP TABLE IF EXISTS core.sessions;
          DROP INDEX IF EXISTS sessions_last_activity_index;
          DROP INDEX IF EXISTS sessions_user_id_index;

          DROP TABLE IF EXISTS core.failed_jobs;
          DROP TABLE IF EXISTS core.job_batches;
          DROP INDEX IF EXISTS jobs_queue_index;
          DROP TABLE IF EXISTS core.jobs;

          DROP TABLE IF EXISTS core.cache_locks;
          DROP TABLE IF EXISTS core.cache;

          DROP TABLE IF EXISTS core.users;

          DROP FUNCTION IF EXISTS app.validate_quota_not_over();
          DROP FUNCTION IF EXISTS app.assert_not_student(bigint);
          DROP FUNCTION IF EXISTS app.app_current_actor();
          DROP FUNCTION IF EXISTS app.is_active_status(public.application_status_enum);
          DROP FUNCTION IF EXISTS app.set_updated_at();

          DO $$
          BEGIN
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname = 'monitor_type_enum') THEN
              DROP TYPE public.monitor_type_enum;
            END IF;
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname = 'internship_status_enum') THEN
              DROP TYPE public.internship_status_enum;
            END IF;
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname = 'application_status_enum') THEN
              DROP TYPE public.application_status_enum;
            END IF;
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname = 'user_role_enum') THEN
              DROP TYPE public.user_role_enum;
            END IF;
          END $$;

          DROP SCHEMA IF EXISTS app CASCADE;
          DROP SCHEMA IF EXISTS core CASCADE;
          SQL);
    }
};
