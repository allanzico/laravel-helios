# Laravel Helios Strategic Review

Date: 2026-06-25

## Verdict

**Continue, but narrow the scope aggressively.**

Laravel Helios is useful if it becomes a **small self-hosted personal ops console for Laravel apps**. It is not useful if it tries to become a full competitor to Horizon, Pulse, Telescope, or Nightwatch.

The project should not be stopped. It also should not keep expanding. The current direction has value because it combines several small operational needs in one private endpoint:

- Is the app healthy?
- Are scheduled tasks running?
- Can I run a safe scheduled command manually?
- Are there failed jobs?
- What slow requests or queries happened recently?
- What errors or logs should I inspect?

That is a real use case for solo/small-team Laravel projects. The value is not “better observability than Nightwatch.” The value is **a simple, private, zero-SaaS operational cockpit you can install into your own projects**.

The recommended direction is:

> Build Helios as a lightweight personal ops console and Horizon companion. Keep the simple UI. Remove or narrow anything that turns it into a broad APM/debugging system.

## Product Positioning

### Exact User

The ideal user is a Laravel developer maintaining several personal, client, or small business apps who wants one private dashboard to check operational health without setting up a full monitoring stack.

This user likely:

- already knows Laravel
- may use database queues instead of Redis/Horizon on small apps
- wants a `/helios` endpoint similar in spirit to `/horizon` or `/pulse`
- prefers self-hosted data
- wants useful operational actions, not perfect observability
- values simplicity over dashboards full of charts

### Exact Use Case

Helios should answer:

- “Is anything obviously broken?”
- “Did my scheduled commands run?”
- “Can I safely rerun a known scheduled command?”
- “Are queues failing?”
- “Are requests or queries getting slow?”
- “What recent logs/errors should I look at?”
- “Is this app production-ready enough?”

It should not try to answer:

- “Give me a full distributed trace.”
- “Replace Horizon’s worker management.”
- “Replace Telescope’s local debugging detail.”
- “Replace Nightwatch’s managed monitoring and alerting.”
- “Become a full APM.”

## Usefulness Assessment

Yes, this can be useful for your own projects, but only if it stays small and practical.

The danger is real: we could burn a lot of tokens recreating existing tools. The current broad feature list overlaps heavily with first-party Laravel products. The project becomes worth keeping only if it optimizes for a narrower personal workflow:

> install package, migrate, open `/helios`, see operational state, run safe actions, prune old data, move on.

If Helios needs hours of configuration, complex UI work, or deep tracing logic to become valuable, it loses. Nightwatch, Pulse, Telescope, and Horizon already exist for those deeper jobs.

The most honest answer:

- **Useful:** yes, as a private ops console for small Laravel apps.
- **Not useful:** as another all-purpose monitoring/debugging/APM platform.
- **Worth continuing:** yes, for one more focused iteration.
- **Stop condition:** if after the next iteration it still does not become something you would install in at least two real projects.

## Recommended Direction

Take the **Personal Ops Console + Horizon Companion** route.

The product should be organized around five primary surfaces:

1. **Overview**
   Health, recent failures, scheduler freshness, failed jobs, slowest request/query.

2. **Scheduler**
   Show tasks, last run, next run, runtime, status, output, and safe manual run for allowlisted commands.

3. **Queues**
   Show failed job count and recent job history. Avoid rebuilding Horizon. Provide retry/forget only where driver-safe and explicitly enabled.

4. **Performance**
   Slow requests and slow queries only. No full tracing unless a future version clearly needs it.

5. **Logs / Errors**
   Keep this basic. Logs should be tail/search/read-only by default. Errors should be grouped, redacted, and optional.

Keep the current simple UI direction. Enhance clarity, empty states, filters, and action safety. Do not redesign it into a heavier analytics product.

## External Tool Comparison

| Tool | Official positioning | Strength | Where Helios should not compete | Helios opportunity |
|---|---|---|---|---|
| Horizon | Dashboard and code-driven configuration for Redis queues, including throughput, runtime, and failures. Requires Redis queues. | Queue worker management and queue metrics. | Do not rebuild worker balancing, Redis queue supervision, tags, throughput metrics, or Horizon’s queue dashboard. | Be a companion for non-Horizon apps and show simple queue health/status. |
| Pulse | At-a-glance performance and usage insights; tracks bottlenecks like slow jobs and endpoints. Uses recorders, sampling, trimming, and storage strategies. | Lightweight app performance summaries. | Do not clone Pulse charts, recorders, user activity cards, or performance analytics. | Borrow the recorder concept and focus on operational actions Pulse does not own. |
| Telescope | Local development companion for requests, exceptions, logs, queries, jobs, mail, notifications, cache, scheduled tasks, dumps, and more. | Deep local/staging debugging. | Do not become an event microscope or debug all Laravel internals. | Stay production-conscious and operational, not local-debug-first. |
| Nightwatch | Managed first-party Laravel monitoring with connected timelines across requests, queries, jobs, commands, cache, mail, scheduled tasks, exceptions, and performance issues. Fully managed, not self-hosted. | Full observability, timelines, alerting, managed retention. | Do not chase full APM, alerts, timelines, collaboration, or SaaS-grade monitoring. | Self-hosted, simple, private, no external service, useful on small projects. |
| Small internal admin panel | App-specific controls and checks. | Fully tailored to one app. | Do not become a generic CRUD/admin builder. | Provide reusable ops primitives that would otherwise be rebuilt in every project. |

References:

- Laravel Horizon docs: <https://laravel.com/docs/12.x/horizon>
- Laravel Pulse docs: <https://laravel.com/docs/12.x/pulse>
- Laravel Telescope docs: <https://laravel.com/docs/12.x/telescope>
- Laravel Nightwatch site: <https://nightwatch.laravel.com/>

## Direction Options

| Direction | Recommendation | Why |
|---|---:|---|
| Personal Ops Console | **Yes** | Best fit. Clear use case, self-hosted, simple, actionable. |
| Horizon Companion | **Yes** | Avoids direct queue competition and gives Helios a sane boundary. |
| Pulse/Nightwatch Alternative | **No** | Too broad. Pulse and Nightwatch are already strong first-party options. |
| Developer Debug Console | **No** | Telescope already owns this. Helios should not be local-debug-first. |
| Package Starter Kit | **Secondary** | The playground and package structure are useful, but not the product. |

## What To Keep

| Keep | Reason |
|---|---|
| Simple embedded `/helios` endpoint | This is the core install-and-open experience. |
| Current simple UI direction | The UI is restrained and workable. Improve it; do not replace it. |
| Playground app | It made real issues visible immediately and should remain. |
| Runtime path config | Needed for package flexibility. |
| Authorization middleware | Correct direction; needs stronger production guardrails. |
| Slow request/query thresholds | Useful and safer than collecting everything. |
| Scheduler monitoring | One of the strongest unique features. |
| Safe manual scheduled command runs | Useful if allowlisted and disabled by default in production. |
| Health checks | Useful if minimal and not too leaky. |
| Prune command | Necessary for self-hosted storage. |

## What To Remove Or Narrow

| Remove / narrow | Recommendation |
|---|---|
| Full queue management ambitions | Do not compete with Horizon. Keep simple status and recent history. |
| Retry/forget for all drivers | Narrow to driver-safe support or explicitly document database failed jobs only. |
| Log clearing from UI | Disable by default or remove. Reading logs is useful; clearing logs from a dashboard is risky. |
| Error tracking as a central feature | Keep optional and redacted. Do not try to become Sentry/Nightwatch. |
| Storing full query bindings | Redact or disable by default. |
| DB-backed health check settings | Move to config unless runtime toggles are truly valuable. |
| `helios_task_definitions` as a durable source of truth | Prefer live schedule discovery plus optional cache/sync fallback. |
| Future-dated migration names before release | Squash/rename before v1. |
| Too many top-level nav items | Group Performance, Events, and Operations to reduce dashboard sprawl. |

## Security Risks

| Severity | Risk | Current state | Recommendation |
|---|---|---|---|
| Critical | Manual scheduled command execution | Enabled by default with `HELIOS_ALLOW_MANUAL_SCHEDULE_RUNS=true`. Any scheduled command can be run if dashboard access is compromised. | Disable by default outside local. Add explicit allowlist: `manual_commands => []`. Require confirmation and audit log. |
| Critical | Sensitive data exposure through dashboard | Logs, query bindings, errors, health metadata, request paths, user IDs, IPs, and headers may expose secrets. | Redact by default. Add config for hidden headers, body fields, query bindings, SQL patterns, log lines. Make dangerous fields opt-in. |
| High | Queue retry/forget from HTTP | Controller calls Artisan commands. Driver behavior and authorization semantics are too broad. | Use Laravel failed job provider where possible. Gate actions separately. Disable actions by default in production. |
| High | Log clearing | UI/API can clear log files. | Make logs read-only by default. Add `HELIOS_ALLOW_LOG_CLEAR=false`. Consider removing clear entirely. |
| High | Production authorization depends on user-defined gate | Default blocks production without gate, which is good, but docs must be stronger. | Add boot-time warning or exception in production if no gate/custom middleware configured. |
| Medium | Query listener stores bindings | Bindings may contain emails, tokens, IDs, payload fragments. | Store bindings only if `HELIOS_STORE_QUERY_BINDINGS=true`. Default false. |
| Medium | Error tracker stores request body | Current redaction list is incomplete. | Use configurable recursive redaction with wildcard matching. Default to storing metadata only. |
| Medium | Health checks leak infra | DB name, Redis connection, disk stats, app version/debug flags can leak operational detail. | Show coarse status by default; reveal metadata only with `HELIOS_SHOW_HEALTH_META=true`. |
| Medium | Mutating API action visibility | Buttons exist without enough contextual warnings. | Add confirmation modals with command/job/log details and risk labels. |
| Low | Empty catch blocks hide package problems | Watchers silently fail. | Keep fail-quiet behavior, but add optional internal diagnostic logging under debug. |

## Concrete Security Patches

1. Change defaults:

```php
'watchers' => [
    'schedule' => [
        'allow_manual_runs' => env('HELIOS_ALLOW_MANUAL_SCHEDULE_RUNS', app()->environment('local')),
        'manual_allowlist' => [],
    ],
],

'actions' => [
    'retry_jobs' => env('HELIOS_ALLOW_JOB_RETRY', app()->environment('local')),
    'forget_jobs' => env('HELIOS_ALLOW_JOB_FORGET', false),
    'clear_logs' => env('HELIOS_ALLOW_LOG_CLEAR', false),
],

'security' => [
    'store_query_bindings' => env('HELIOS_STORE_QUERY_BINDINGS', false),
    'store_request_body' => env('HELIOS_STORE_REQUEST_BODY', false),
    'show_health_meta' => env('HELIOS_SHOW_HEALTH_META', false),
],
```

2. Add separate authorization abilities:

- `viewHelios`
- `runHeliosTask`
- `retryHeliosJob`
- `forgetHeliosJob`
- `clearHeliosLog`

3. In production, refuse to serve if authorization is not explicitly configured.

4. Add recursive redaction service used by errors, requests, logs, and query bindings.

5. Make all destructive actions audit into a `helios_actions` table or Laravel log.

## Architecture Risks

| Risk | Why it matters | Recommendation |
|---|---|---|
| Too many tables too early | Seven package tables before v1 creates migration burden. | Collapse where possible or stabilize a smaller schema. |
| Synchronous watcher writes | Monitoring can add latency and failure modes. | Keep only interesting events; consider buffered writes later. |
| Query listener overhead | Runs for every query and checks schema. | Cache table existence and keep slow-only defaults. |
| Error tracking overlaps logs/Nightwatch/Sentry | Could become a deep rabbit hole. | Keep optional, simple, redacted. |
| Scheduler discovery is split | Live discovery plus DB definitions plus sync command can diverge. | Pick live discovery as primary; DB only for historical runs. |
| Queue module duplicates Horizon | Full queue support is complex and driver-dependent. | Narrow queue actions and document boundaries. |
| UI bundle size | React bundle is heavy for a package dashboard, but current UI is acceptable. | Keep for now; reduce dependencies and lazy load pages before considering replacement. |
| Health settings in DB | Adds table and UI complexity. | Prefer config-first health checks. |

## Recommended Architecture

Keep the package shape but reduce conceptual scope.

### Target Modules

| Module | Status | Notes |
|---|---|---|
| Core dashboard/auth/config | Keep | Must be secure and boring. |
| Health | Keep | Config-first, on-demand, optional metadata. |
| Scheduler | Keep | Strong unique value; add allowlist. |
| Performance | Keep | Slow requests and slow queries only. |
| Queues | Narrow | Status/history, optional safe retry for supported failed-job providers. |
| Logs | Narrow | Tail/search/read-only by default. |
| Errors | Optional | Redacted grouping only; not a Sentry clone. |

### Target Data Model

Preferred v1 schema:

| Table | Purpose | Keep? |
|---|---|---|
| `helios_requests` | Slow/error request samples | Keep |
| `helios_queries` | Slow query samples | Keep, but no bindings by default |
| `helios_jobs` | Job execution history only | Keep if queue module remains |
| `helios_scheduled_task_runs` | Schedule run history | Rename from `helios_scheduled_tasks` |
| `helios_errors` | Optional grouped errors | Keep optional |
| `helios_actions` | Audit manual actions | Add |
| `helios_task_definitions` | Stored schedule definitions | Remove or cache-only |
| `helios_health_check_settings` | Runtime health settings | Remove; move to config |

Do not use one append-only events table yet. It sounds simpler, but the UI needs different query patterns for requests, queries, jobs, errors, and task runs. Purpose-built tables are fine if reduced and stable.

## Runtime Defaults

| Environment | Requests | Queries | Jobs | Scheduler | Errors | Logs | Health |
|---|---|---|---|---|---|---|---|
| Local | sample 100%, slow threshold low | bindings allowed | actions allowed | manual runs allowed | enabled | read/clear allowed | full metadata |
| Staging | slow/errors only | slow only, no bindings | retry optional | allowlisted manual runs | enabled redacted | read-only | limited metadata |
| Production | slow/errors only, low sample | slow only, no bindings | view only by default | manual runs disabled unless allowlisted | optional redacted | read-only | coarse status |

Default production should collect only interesting events:

- request status >= 400
- request duration above threshold
- query duration above threshold
- job failed/processed summary
- scheduled task run events
- grouped errors if enabled

Never store by default:

- passwords
- tokens
- cookies
- authorization headers
- full request bodies
- full query bindings
- full environment values
- raw log lines containing configured sensitive patterns

## Queue Monitoring Recommendation

Do not try to become Horizon.

Queue support should be:

- failed job count
- recent job executions
- recent failures
- failed job retry only when driver/provider support is known
- failed job forget only if explicitly enabled

The current use of `Artisan::call('queue:retry')` and `Artisan::call('queue:forget')` is acceptable as a playground proof, but not as a production v1 design. It should be replaced or wrapped by a dedicated queue action service that:

- detects failed job provider
- validates job exists
- authorizes action
- logs action
- reports unsupported drivers clearly

If Horizon is installed, Helios should show a link to Horizon and avoid duplicate queue action UI.

## Scheduled Tasks Recommendation

Scheduler should become a flagship feature.

This is a strong Helios angle because scheduled tasks sit between Horizon, Pulse, and Telescope:

- Horizon is queue-focused.
- Pulse gives performance summaries.
- Telescope is local debugging.
- Nightwatch monitors scheduled tasks but is managed SaaS.

Helios can be useful by making scheduler state and safe manual runs simple.

Required changes:

1. Rename `helios_scheduled_tasks` to `helios_scheduled_task_runs` before v1.
2. Remove durable task definitions if live discovery is reliable.
3. Add config:

```php
'schedule' => [
    'manual_runs' => false,
    'manual_allowlist' => [
        // 'reports:daily',
        // 'cache:warm',
    ],
],
```

4. Show non-allowlisted tasks as view-only.
5. Store manual run action audit.
6. Support arguments/options only through explicit configured command templates, not arbitrary input.

## Error Tracking Recommendation

Keep error tracking optional and modest.

Do not compete with Sentry, Flare, Nightwatch, or Bugsnag. The useful Helios version is:

- grouped exception class/message/location
- first seen / last seen / count
- environment
- route/method/status if available
- redacted request metadata
- top stack frame and short trace
- resolve/ignore

Change defaults:

- do not store request body unless opted in
- store short trace by default
- redact recursively
- allow disabling IP/user agent/user ID

The grouping hash should probably ignore line number by default or use a configurable grouping strategy. Including line number causes noisy regrouping after small code shifts.

## Query And Request Monitoring Recommendation

Keep slow request/query monitoring, but do not promise tracing.

Helios is useful with only slow requests and slow queries if it answers:

- which route is slow?
- how slow?
- how often recently?
- which query is slow?
- when did it happen?
- did it correlate with errors/jobs/tasks?

Near-term improvements:

- group slow requests by method + URI pattern/controller
- group slow queries by normalized SQL fingerprint
- show counts, max, average, p95
- keep raw recent samples secondary
- hide query bindings by default

Do not build full request timelines. Nightwatch already owns this space with connected events and timelines.

## Health Checks Recommendation

Keep health checks, but make them more operational.

Minimum useful checks:

| Check | Keep? | Notes |
|---|---:|---|
| Application boot/version | Yes | Show coarse status. |
| Database connectivity | Yes | Hide database name by default. |
| Queue backlog/failed jobs | Yes | Config thresholds. |
| Scheduler freshness | Yes | Critical missing piece. |
| Disk space | Yes | Useful on VPS/small servers. |
| Redis | Optional | Only if configured. |
| Environment/debug | Yes | Warn on debug in production. |
| Storage writability | Add | Common deployment problem. |
| Cache read/write | Add | Common app health signal. |
| Mail | Defer | Hard to test safely. |
| HTTP endpoints | Defer | Can become external monitoring. |

Health checks should be config-first. The DB-backed settings table should be removed unless runtime toggles become clearly valuable.

## Frontend Recommendation

Keep the simple UI direction.

Do not rewrite the frontend now. The current React/TanStack setup is heavier than ideal, but a rewrite would burn time without answering the product question.

Enhance instead:

- reduce nav sprawl by grouping pages
- add better empty states
- add “danger” labeling for actions
- add filters for status/time/range
- add a read-only production mode indicator
- add clear “what to do next” hints on Overview
- lazy load route chunks if bundle size remains high
- remove unnecessary dependencies over time

Suggested nav:

| Current | Recommended |
|---|---|
| Dashboard | Overview |
| Health | Health |
| Jobs | Queues |
| Tasks | Scheduler |
| Requests + Queries | Performance |
| Logs + Errors | Events |

## Developer Experience Recommendation

Keep the playground.

It already proved its worth by exposing a Blade issue and validating package installation in a real Laravel app. It should remain committed, with runtime files ignored.

Improve it:

- add `composer playground` or `make playground`
- add a smoke test that boots `/helios`
- add demo data reset command
- add screenshots/GIF later
- document that `playground/vendor` is ignored and installed locally

Ideal local loop:

```bash
bash scripts/playground.sh
bash scripts/playground-demo.sh
open http://127.0.0.1:8001/helios
```

## Testing Plan

Start with package tests, not browser tests.

| Priority | Test | Reason |
|---:|---|---|
| P0 | package boots and registers `/helios` routes | Prevents install failures. |
| P0 | production blocks without gate/custom middleware | Security baseline. |
| P0 | manual scheduled run disabled by default outside local | Critical action safety. |
| P0 | scheduled run requires allowlist | Critical action safety. |
| P0 | query bindings are not stored by default | Privacy baseline. |
| P0 | error handler redacts recursively | Privacy baseline. |
| P1 | request watcher ignores Helios paths | Prevents self-noise. |
| P1 | slow/error request capture works | Core feature. |
| P1 | query threshold/sampling works | Core feature. |
| P1 | job listener records lifecycle | Queue feature confidence. |
| P1 | prune command deletes old rows | Storage safety. |
| P1 | dashboard stats work on SQLite | Cross-DB guard. |
| P2 | playground smoke test | Developer loop confidence. |

Use Orchestra Testbench for package behavior. Keep playground for manual and smoke validation.

## Release Readiness

Not ready for real-world use yet.

Blockers:

- manual scheduled command execution is too permissive
- query bindings and error/request data need stronger redaction defaults
- queue retry/forget design is not driver-safe enough
- no automated package tests
- README claims Laravel 11/12 but composer now includes 13
- health metadata leaks too much by default
- migration set should be stabilized before v1
- production security story needs stronger docs and enforcement
- no CI matrix

V1 readiness checklist:

- [ ] define final product scope
- [ ] production-safe config defaults
- [ ] command allowlist
- [ ] action audit log
- [ ] recursive redaction service
- [ ] no query bindings by default
- [ ] read-only logs by default
- [ ] driver-safe queue actions or queue actions disabled by default
- [ ] scheduler module stabilized
- [ ] migrations squashed/renamed
- [ ] Testbench tests
- [ ] playground smoke test
- [ ] README updated with accurate Laravel version support
- [ ] screenshots or short demo GIF
- [ ] CI for PHP 8.2/8.3/8.4 and Laravel 11/12/13 if that matrix is retained

## Prioritized Implementation Plan

### Phase 1: Stop The Dangerous Stuff

1. Disable manual scheduled runs by default outside local.
2. Add scheduled command allowlist.
3. Disable log clearing by default.
4. Disable query binding storage by default.
5. Add recursive redaction service.
6. Add production boot guard when no authorization is configured.

### Phase 2: Define The Product Boundary

1. Rename product language to “personal ops console.”
2. Narrow queue page to status/history and optional supported actions.
3. Make scheduler the most polished page.
4. Group requests/queries into Performance.
5. Make logs/errors secondary and optional.

### Phase 3: Stabilize Architecture

1. Remove DB-backed health settings or justify them.
2. Replace stored task definitions with live discovery plus sync fallback.
3. Add action audit table.
4. Squash/rename migrations for v1.
5. Add config presets for local/staging/production.

### Phase 4: Add Tests

1. Add Orchestra Testbench.
2. Test service provider and route auth.
3. Test redaction and watcher capture.
4. Test schedule allowlist.
5. Test prune command.
6. Add playground smoke command.

### Phase 5: UI Polish Without Redesign

1. Add overview “problems first” layout.
2. Add empty states.
3. Add safer destructive action modals.
4. Add filters.
5. Group nav.
6. Lazy load pages if bundle size is still uncomfortable.

## Direct Answers To The Brief

| Question | Answer |
|---|---|
| Is this useful? | Yes, if narrowed to a private personal ops console. |
| Are we burning tokens? | We will be if we keep expanding toward APM/debugging. One focused iteration is justified. |
| Would we install this in real apps? | Yes, after security defaults and scheduler/health polish. Not yet in production. |
| Should logs stay? | Yes, read-only by default. |
| Should errors stay? | Yes, optional and redacted. |
| Should manual command execution stay? | Yes, but disabled by default and allowlisted. |
| Should queue management stay? | Narrow it. Do not compete with Horizon. |
| Should the simple UI stay? | Yes. Enhance it; do not replace it. |
| Should this be production-safe or local-first? | Production-safe defaults, but useful in local/staging with more permissive toggles. |
| What would make us delete it? | If scheduler/health/actions are not clearly better than a small internal admin page, or if security hardening makes the package too cumbersome. |

## Final Recommendation

Continue for one focused cycle.

The target should be:

> Laravel Helios: a private, self-hosted Laravel ops console for small apps. It shows health, scheduler state, queue failures, slow requests/queries, logs, and grouped errors, with safe allowlisted actions.

Do not build:

- a full APM
- a Horizon replacement
- a Telescope clone
- a Nightwatch clone
- a broad analytics dashboard

The next work should be mostly subtraction, security, and product boundary-setting. If that focused version still does not feel install-worthy in your own projects, stop and keep the package as a useful internal package-development template.

