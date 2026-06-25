# Laravel Helios Strategic Review Brief

## Mission

Review Laravel Helios as both a Laravel package and a product. Decide whether it should continue in its current direction, narrow into a smaller product, pivot to a different architecture, or stop.

This review should be blunt, practical, and grounded in the repository. Do not assume the current direction is correct. The most valuable outcome is a clear recommendation with tradeoffs, followed by a prioritized implementation plan.

## Project Context

Laravel Helios is a self-hosted Laravel monitoring package. It currently provides, or is intended to provide, a Horizon/Pulse-like dashboard at `/helios` for:

- request performance
- slow database queries
- queued jobs and failed job retry/forget actions
- scheduled task monitoring and manual task runs
- application logs
- health checks
- grouped error tracking

Recent work added:

- configurable watcher toggles
- route authorization
- quieter request/query collection
- queue retry/forget actions
- a prune command
- runtime-configurable frontend paths
- Laravel 13 compatibility
- a real `playground/` Laravel app for local testing
- helper scripts for running and seeding the playground

The central question is:

> Is Laravel Helios useful enough as a standalone package, or is it recreating too many existing Laravel tools without a strong enough reason to exist?

Important preference from the project owner:

> The current simple UI direction is liked. Do not recommend replacing the UI stack or redesigning the product into something visually heavier unless there is a very strong reason. Prefer enhancing and simplifying the existing UI over changing it.

Important concern:

> Are we burning time and tokens building something we will never actually use? Be honest about whether this is a useful product, a personal curiosity, a duplicate of better tools, or a project that should be stopped.

## Required Final Output

Produce a review with these sections:

1. **Verdict**
   State one recommendation: continue, narrow, pivot, rebuild, pause, or stop.

2. **Product Positioning**
   Define the exact user, exact use case, and why this package should exist.

3. **Usefulness Assessment**
   Decide whether this project is useful enough to keep investing in. Include a direct answer to: are we building something we will actually use?

4. **Recommended Direction**
   Choose the direction this project should take next and explain why.

5. **What To Keep**
   List the features and technical choices that are worth preserving.

6. **What To Remove**
   List features, tables, endpoints, UI pages, or concepts that dilute the product.

7. **Security Risks**
   Rank risks by severity and include concrete patches.

8. **Architecture Risks**
   Explain where the current design will become painful.

9. **Recommended Architecture**
   Propose a simpler target architecture.

10. **Testing Plan**
   Define the smallest useful automated test suite.

11. **Release Readiness**
   List what blocks real-world use.

12. **Prioritized Implementation Plan**
    Give ordered work items, starting with the highest-leverage changes.

Do not begin coding until this review is complete.

## Product Direction Review

Evaluate these possible product routes:

1. **Personal Ops Console**
   A private, self-hosted, single-app dashboard for queues, scheduler, logs, slow queries, slow requests, and health.

2. **Horizon Companion**
   Avoid competing with Horizon. Focus on things Horizon does not cover well: scheduled commands, app health, logs, slow requests, deploy/runtime checks.

3. **Pulse/Nightwatch Alternative**
   A local-first performance monitor with fewer moving parts and no SaaS assumptions.

4. **Developer Debug Console**
   More like Telescope, focused on local/staging debugging rather than production monitoring.

5. **Package Starter Kit**
   Treat Helios as a reusable internal package template: auth, embedded dashboard, path-repo playground, health checks, and package development workflow.

Answer these directly:

- Who is the exact user?
- What problem does Helios solve better than Horizon, Pulse, Telescope, Nightwatch, or a small internal admin panel?
- Is this product actually useful for our own projects?
- Would we install and use this in multiple real Laravel apps, or only enjoy building it?
- What direction should we take next?
- Should we continue, narrow the scope, pivot, pause, or stop?
- What is the smallest lovable version?
- Which existing Laravel tools should Helios avoid competing with?
- What is the strongest unique angle?
- Should logs stay?
- Should error tracking stay?
- Should manual command execution stay?
- Should this be production-safe by default or local/staging-first?
- What would make this package obviously useful in the first 10 minutes after install?
- What would make us delete it and use Horizon/Pulse/Telescope/Nightwatch instead?

## Security Review

Treat `/helios` as a sensitive admin surface. The package can read logs, inspect request/query data, expose health information, retry jobs, clear logs, and run scheduled commands.

Inspect:

- `src/Http/Middleware/Authorize.php`
- route middleware and production defaults
- CSRF coverage for all mutating endpoints
- scheduled command manual execution
- queue retry/forget actions
- log file reading and clearing
- path traversal protections
- error/request redaction
- query binding exposure
- health check information leakage
- dashboard API data leakage
- destructive actions and confirmation flows
- whether `viewHelios` is sufficient

Answer:

- If an attacker can access `/helios`, what is the worst they can do?
- Which risks are critical, high, medium, and low?
- Should Helios refuse to boot in production without explicit authorization?
- Should manual actions be disabled by default?
- Should scheduled command execution require an allowlist?
- Should logs, queries, request bodies, headers, IPs, user IDs, and stack traces be redacted differently?
- Should there be IP allowlists, signed access, basic auth, or custom middleware examples?

Produce concrete security patches.

## Architecture Review

Current shape:

- Laravel package service provider
- package migrations
- Eloquent models under `src/Models`
- event listeners for jobs, queries, and scheduled tasks
- terminating middleware for requests
- error reportable callback
- React/Vite UI embedded as inline assets
- API routes under `/helios/api`
- local Laravel playground app

Evaluate:

- whether multiple feature-specific tables are justified
- whether one append-only events table would be simpler
- whether watcher writes should be synchronous, queued, sampled, buffered, or file-based
- whether Helios should write to the host app database at all
- whether SQLite/file storage would be better for small projects
- whether pruning should be automatically scheduled
- whether watchers should be modular recorder classes similar to Pulse
- whether React is worth the bundle and complexity
- whether Blade/Livewire/Alpine would be simpler
- whether inline assets are still the right packaging strategy
- whether the API surface is too broad
- whether health checks should be config-only, DB-backed, or mixed
- whether scheduled task definitions should be stored or discovered live
- whether Laravel 11/12/13 support is realistic

Deliver a simpler target architecture that preserves product value while reducing tables, endpoints, frontend complexity, and runtime overhead.

## Runtime Overhead and Data Volume

Analyze the overhead of:

- request middleware on every web request
- query listener on every database query
- schema checks inside listeners
- storing query bindings
- job event writes during queue execution
- scheduled task event writes
- error tracking writes during exception reporting
- dashboard queries over growing tables
- manual pruning

Answer:

- What should the default watcher settings be for local, staging, and production?
- Should production disable some watchers by default?
- Should queries and requests use sampling, thresholds, or both?
- Should Helios collect all events or only interesting events?
- What indexes are missing?
- What data should never be stored?

## Data Model Review

Inspect all migrations and models.

Check:

- cross-database compatibility: SQLite, MySQL, PostgreSQL
- UUID primary key behavior
- JSON columns and casts
- timestamp precision
- indexes for dashboard endpoints
- inconsistent timestamps
- table naming
- future-dated migration names
- whether package migrations should be squashed before v1
- whether `helios_jobs` duplicates Laravel `jobs` and `failed_jobs`
- whether `helios_task_definitions` should exist
- whether `helios_health_check_settings` belongs in DB or config

Redesign the v1 data model. Prefer fewer tables unless separate tables are clearly justified.

## Queue Monitoring and Retry

Current behavior:

- listens to queue processing/processed/failed events
- stores execution history in `helios_jobs`
- shows pending and failed counts from Laravel tables
- retry/forget delegates to `queue:retry` and `queue:forget`

Resolve:

- whether Helios should manage queues at all
- whether queue management belongs to Horizon instead
- whether retry/forget should call Artisan from HTTP controllers
- whether Laravel failed job provider APIs should be used directly
- whether retry works across database, Redis, SQS, Beanstalkd, and other drivers
- whether job UUID assumptions are correct
- how pending jobs should be inspected without rebuilding Horizon

If queue support stays, design a driver-safe queue module. If it should be removed or narrowed, explain how Helios should position itself next to Horizon.

## Scheduled Tasks

Current behavior:

- scheduler events are recorded
- definitions can be synced by `helios:sync-tasks`
- dashboard tries to discover definitions from Laravel's schedule
- manual run uses `POST /helios/api/scheduled-tasks/run`

Resolve:

- whether manual command execution is too dangerous
- whether only explicitly allowlisted commands should be runnable
- whether commands with arguments/options should be supported
- how output streaming should work safely
- whether task definitions should be stored or discovered live
- whether schedule discovery works across Laravel 11/12/13
- whether `helios:sync-tasks` should be removed, automated, or kept

Design a safe scheduled-command module where the dashboard can show all tasks but only run explicitly allowed tasks.

## Error Tracking

Current behavior:

- package registers a reportable callback in the service provider
- groups errors by hash
- stores trace, request info, user ID, IP, and user agent
- supports resolve, ignore, unresolve, and delete actions

Evaluate:

- whether automatic error tracking is too invasive
- whether the grouping hash is correct
- whether traces should be full, truncated, or redacted
- whether request payloads should be stored at all
- whether error tracking should be separate from request monitoring
- whether it should integrate with Laravel's exception handling differently
- whether this feature should be removed in favor of logs

Identify privacy risks, storage risks, and grouping flaws.

## Query and Request Monitoring

Review:

- slow request capture
- slow query capture
- sampling behavior
- ignored paths
- query binding sanitization
- request data sanitization
- N+1 detection possibility
- relation to Laravel Pulse
- whether sampled dashboard charts are meaningful

Decide whether Helios is useful if it only shows slow requests and slow queries, or whether it needs deeper request traces to matter.

## Health Checks and Infra Monitoring

Current health checks include application, database, Redis, queue, disk, and environment.

Resolve:

- whether health checks should run on demand or periodically
- whether results should be stored historically
- whether thresholds should be config-only
- which checks are genuinely useful for small Laravel apps
- which checks are misleading or risky
- whether health should include HTTP endpoints, scheduler freshness, queue worker freshness, cache, mail, storage writability, and failed job thresholds
- how much infrastructure information can safely be shown

Design a minimal but useful health-check system for a solo developer running multiple Laravel apps.

## Frontend Review

Current frontend:

- React 18
- TanStack Router and Query
- Tailwind/shadcn-like components
- inline built assets
- route runtime config through `window.Helios`

Constraint:

- Preserve the simple UI direction unless there is a decisive reason not to.
- Prefer incremental improvements: clearer information hierarchy, better empty states, safer action flows, better filtering, smaller bundle, and more polished operational screens.
- Do not recommend a major visual redesign for its own sake.

Evaluate:

- whether React is worth it
- whether the bundle is too large
- whether Blade, Livewire, or Alpine would reduce complexity enough to justify abandoning the current UI direction
- whether pages should be reduced
- whether filters, empty states, loading states, and actions are good enough
- whether the nav structure matches the product
- whether the UI feels like an operational tool or a generic dashboard
- whether destructive actions are clear enough

Recommend how to enhance the existing simple UI. Only recommend replacing the frontend stack if the current stack creates a clear product or maintenance problem that cannot be solved incrementally.

## Package Developer Experience

Recent work added:

- `playground/`
- `bash scripts/playground.sh`
- `bash scripts/playground-demo.sh`

Evaluate:

- whether the playground should be committed
- whether it should be generated on demand instead
- whether Orchestra Testbench Workbench is a better fit
- whether Composer path repository is the best approach
- whether playground runtime files are ignored correctly
- whether package development should include Pest/PHPUnit integration tests
- whether CI should run package tests and playground smoke tests

Design the ideal local development loop from clone to seeing `/helios` with sample data.

## Testing Strategy

Create the smallest useful automated test suite.

Must cover:

- service provider registers routes conditionally
- authorization allows local/testing
- authorization blocks production without a gate
- request watcher ignores Helios paths
- request watcher stores slow/error/sampled requests correctly
- query watcher ignores Helios tables
- query watcher respects threshold/sampling
- job listener records processing/processed/failed
- retry/forget endpoints use the correct mechanism
- mutating endpoints enforce auth and CSRF expectations
- scheduled task sync discovers tasks
- manual scheduled task run requires allow flag and `POST`
- error handler redacts sensitive data
- prune command deletes old records
- dashboard endpoints avoid database-specific SQL

Prioritize tests that protect security and package boot behavior.

## Release Readiness

Create a v1 readiness checklist covering:

- Composer constraints
- Laravel version support policy
- migration stability
- semantic versioning
- README accuracy
- screenshots or demo GIF
- production security warning
- config publishing
- upgrade path
- changelog
- package discovery
- CI matrix
- frontend asset build process
- whether built assets should be committed

Identify what blocks this package from being safely used in real projects today.

## Files To Inspect First

Start with:

- `src/HeliosServiceProvider.php`
- `src/Http/Middleware/Authorize.php`
- `src/Http/Middleware/TrackRequestPerformance.php`
- `src/Listeners/QueryListener.php`
- `src/Listeners/JobEventListener.php`
- `src/Listeners/ScheduledTaskEventListener.php`
- `src/Services/ErrorHandler.php`
- `src/Services/HealthCheckService.php`
- `src/Http/Controllers/Api/JobController.php`
- `src/Http/Controllers/Api/ScheduledTaskController.php`
- `src/Http/Controllers/Api/DashboardController.php`
- `database/migrations/*`
- `config/helios.php`
- `ui/src`
- `playground/`
- `scripts/playground.sh`
- `scripts/playground-demo.sh`

## Execution Instructions

1. Read the whole repo before recommending changes.
2. Use the playground app to validate assumptions.
3. Compare the package honestly against Horizon, Pulse, Telescope, Nightwatch, and a small internal admin panel.
4. Give the strategic review first.
5. Only after the review, implement the first high-leverage changes if asked to continue.
6. Keep the playground working.
7. Add tests for changed behavior.

## Follow-Up If Continuing

Implement the first high-leverage changes from the review. Prioritize:

1. security
2. production-safe defaults
3. a smaller architecture
4. tests around package boot and dangerous actions
5. playground smoke validation

## Follow-Up If Pivoting

Create a migration plan from the current repository to the recommended product direction.

Include:

- files to delete
- features to keep
- features to postpone
- data model changes
- UI changes
- tests needed before the pivot is safe
- the smallest first version to build
