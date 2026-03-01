# AGENTS.md — Laravel

Laravel-specific rules for this codebase. Overrides global `~/AGENTS.md`.

> This file is **living documentation** — rules here can and should evolve over time. When a user's request conflicts with an existing rule, discuss the conflict, and if needed, update this file to reflect the new convention. When refactoring or improving the codebase reveals a bad pattern, **proactively add or update the relevant rule** here so it doesn't happen again.

---

## PHP RULES

- Always use `declare(strict_types=1);` in PHP files where applicable.
- Never use `mixed` unless absolutely necessary and documented.
- Always type function parameters, return values, and class properties explicitly.
- Prefer constructor property promotion for dependencies.
- Use value objects or DTOs for structured payloads — avoid untyped arrays for domain data.

## FRAMEWORK RULES

- Follow Laravel conventions first (`app/Http`, `app/Models`, `app/Policies`, `database/`, `routes/`).
- Keep controllers thin — orchestration only. Business logic belongs in services/actions.
- Use dependency injection via the service container — avoid manual instantiation inside controllers.
- Prefer named routes and route model binding over manual ID lookups.
- Never call `env()` outside config files — use `config()` in runtime code.

## NAMING CONVENTION

- **Classes** → PascalCase (`UserService`, `CreateInvoiceAction`)
- **Methods & variables** → camelCase (`createInvoice`, `isApproved`)
- **Database tables** → snake_case plural (`invoice_items`)
- **Columns** → snake_case (`created_at`, `email_verified_at`)
- **Controllers** → suffix with `Controller` (`InvoiceController`)
- **Form requests** → suffix with `Request` (`StoreInvoiceRequest`)
- **Jobs** → imperative verbs (`SendInvoiceEmail`, `SyncCustomerData`)

## ROUTING & CONTROLLER RULES

- Put web routes in `routes/web.php`, API routes in `routes/api.php`.
- Use one controller method per endpoint concern — avoid large multi-purpose methods.
- Always authorize access explicitly (`authorize`, policies, or gates) before mutating data.
- Return consistent response types per controller (view, redirect, or JSON).
- Avoid query building in controllers — delegate to model scopes or query services.

## VALIDATION RULES

- Always validate input with Form Request classes for non-trivial endpoints.
- Keep validation rules centralized in `FormRequest` — never duplicate inline rules across controllers.
- Use custom rule objects for complex validation logic.
- Always validate and normalize external payloads before using them.

## ELOQUENT RULES

- Prevent N+1 queries — always eager load needed relations.
- Use query scopes for reusable filters.
- Never expose mass assignment risk — define `$fillable` or `$guarded` explicitly.
- Keep model events lightweight — move heavy side effects to queued jobs.
- Prefer Eloquent relationships over manual joins when domain clarity is better.

## DATABASE RULES

- Every schema change must go through migrations — never manual DB edits in production.
- Keep migrations reversible (`up`/`down`) and deterministic.
- Use foreign keys and indexes for relational integrity and performance.
- Wrap multi-step writes in transactions when atomicity is required.
- Use seeders/factories for reproducible local and test data.

## SECURITY RULES

- Never trust request data — always validate and authorize.
- Use policies/gates for access control, not ad-hoc role checks in controllers.
- Never log secrets, tokens, or sensitive personal data.
- Avoid raw SQL; if needed, use parameter binding only.
- Escape output in Blade by default (`{{ }}`), use raw output only for trusted sanitized content.

## ERROR HANDLING RULES

- Never swallow exceptions silently — log with actionable context.
- Use domain-specific exceptions for predictable business rule failures.
- Return structured API error responses with consistent shape.
- Never expose stack traces or internal exception messages to end users.

## QUEUE & ASYNC RULES

- Offload heavy or slow operations to queued jobs.
- Jobs must be idempotent when possible.
- Set retries, backoff, and timeout intentionally for each job.
- Track failed jobs and provide a retry strategy.

## TESTING RULES

- Cover business logic with unit tests and HTTP flows with feature tests.
- Use factories and database refresh strategy for isolated tests.
- Test happy paths and edge cases (validation, authorization, failure paths).
- Mock external integrations (email, payment, third-party APIs).

## PERFORMANCE RULES

- Cache expensive reads with clear invalidation strategy.
- Use pagination/chunking for large datasets.
- Select only required columns for heavy queries.
- Use queues and events to keep request latency low.

## VERIFICATION RULES

- After changes, always verify using the project's available scripts:
  - Lint/format — check code style and static analysis
  - Test — verify unit and feature tests pass
  - Build/runtime checks — ensure app boots and critical flows work
  - Migrate checks — verify migrations run and rollback correctly in local/test environment
