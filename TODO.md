# AvaX Muscular System Lockdown Plan — V1 / V2 / V3

Status: canonical AI TODO plan  
Purpose: restore and preserve AvaX “muscle” from `avax-backup.txt`, `Framework.txt`, `components/components.txt`, and
local Git history, but implement it under the new architecture, governance, and V1/V2/V3 roadmap.  
Execution rule: V1 must be proven before V2 implementation. V2 platform baseline must be proven before V3 production implementation.
Execution control: `Code-Review-And-ToDo/EXECUTION.md` is the active stage lock. If this file and `EXECUTION.md`
disagree, `EXECUTION.md` wins.

Current active result:

```text
Stage 00: Current Truth Lock — COMPLETE
Stage 01: Final Project Tree Freeze — COMPLETE
Stage 02: Taxonomy Integrity Green — COMPLETE
Stage V1-01: Backup Muscle Inventory — COMPLETE
Stage V1-02: Current Component Muscle Audit — COMPLETE
Stage V1-03: Static Integrity Closure — ACTIVE
Current repository readiness: RED
Next allowed action: Fix PHPStan baseline or component errors
V2 Implementation: LOCKED
V3 Implementation: LOCKED
```

---

# 0. Core Decision

AvaX should not be a clean skeleton.

AvaX should become a muscular, production-grade, architecture-native PHP framework.

That means:

```text
old muscle is valuable
old chaos is not valuable
new architecture is valuable
new skeleton without behavior is not valuable
```

Therefore:

```text
Use avax-backup.txt as source material.
Do not rewrite working behavior from scratch unless the backup has no usable implementation.
Do not copy old monoliths blindly.
Move, slice, adapt, test, and prove behavior under the new plan.
```

---

# 1. Current Truth

Current state:

```text
V1 Kernel Green: NOT PROVEN
V2 Implementation: LOCKED
V3 Implementation: LOCKED

Composer validate: GREEN
Autoload integrity: RED
Production PSR-4 skips: RED
Runtime doctor: GREEN

Broken refs: RED / not fully classified
PHPStan: RED
Tests: RED
Component suite structure: GREEN
Superglobal audit: GREEN
Component completion: not proven
Muscle restoration: inventory and current component audit complete / implementation locked
```

Important correction:

```text
Broken refs must not be called GREEN while critical refs remain unless every critical ref is classified as:
- false-positive
- test-only
- docs-only
- external-vendor
- non-production
- requires-human-decision
```

Current next priority:

```text
1. Stage V1-03: repair or classify static integrity blockers.
2. Repair autoload, test configuration, broken refs, and PHPStan in locked stage order.
3. Restore V1 muscles under canonical architecture only after audit and integrity gates allow production changes.
4. Prove V1 with tests/static analysis.
5. Only then unlock V2.
6. Only after V2 baseline, start V3.
```

---

# 2. Non-Negotiable Rules

## 2.1 Execution locks

```text
[ ] Do not implement V2 until V1 Kernel Green is proven.
[ ] Do not implement V3 until V1 Kernel Green and V2 platform baseline are proven.
[ ] Planning V2/V3 is allowed.
[ ] Production code implementation for V2/V3 is locked.
```

## 2.2 Refactor rules

```text
[ ] Use existing code from avax-backup.txt first.
[ ] Do not rewrite behavior from scratch if backup has working code.
[ ] Do not copy old monoliths as-is.
[ ] Do not keep old god-class design if behavior can be sliced into capabilities.
[ ] Do not create placeholder classes.
[ ] Do not create dummy classes to silence PHPStan or broken refs.
[ ] Do not weaken types to make static analysis green.
[ ] Do not introduce forbidden directories: Services, Helpers, Utils, Common, Shared, Managers, Core, Support.
```

## 2.3 One active stage rule

```text
Only one stage is active at a time.
Every stage must produce:
- report
- changed files list
- validation commands
- validation result
- remaining blockers
- next allowed action
```

---

# 3. Target Version Model

```text
V1 = Production Kernel + V1 Muscles
V2 = Enterprise Platform Engines
V3 = Executable System Design Framework
```

Sharper:

```text
V1 proves AvaX is real.
V2 proves AvaX is enterprise-useful.
V3 proves AvaX can validate large-system architecture.
```

---

# 4. V1 Goal

V1 is not just “make tests pass”.

V1 means:

```text
AvaX boots.
AvaX handles HTTP.
AvaX handles CLI.
AvaX resets runtime state.
AvaX has real component behavior.
AvaX has essential framework muscles.
AvaX passes integrity gates.
AvaX has enough tests to prove the kernel and public API.
```

V1 does not need every future feature.

V1 does need the framework to feel like a real usable framework.

---

# 5. V1 Lockdown Stages

## Stage V1-00 — Truth Correction and Gate Lock

Goal:

```text
Make CURRENT_TRUTH.md, EXECUTION.md, TODO.md, and reports agree.
```

Tasks:

```text
[ ] Read CURRENT_TRUTH.md.
[ ] Read Code-Review-And-ToDo/EXECUTION.md.
[ ] Read TODO.md.
[ ] Read latest v1-integrity reports.
[ ] Correct any false GREEN state.
[ ] Make broken refs status honest.
[ ] Keep V2 and V3 locked.
```

Output:

```text
Code-Review-And-ToDo/v1-lockdown/v1-current-truth-correction-report.md
```

Acceptance:

```text
[ ] CURRENT_TRUTH.md says V1 Kernel Green NOT PROVEN unless all gates pass.
[ ] V2 Implementation says LOCKED.
[ ] V3 Implementation says LOCKED.
[ ] Broken refs state is not falsely GREEN.
```

---

## Stage V1-01 — Backup Muscle Inventory

Goal:

```text
Extract every useful feature/muscle from avax-backup.txt and classify it.
```

Do not implement code in this stage.

Tasks:

```text
[x] Parse avax-backup.txt file headers.
[x] Extract old components.
[x] Extract public APIs.
[x] Extract tests.
[x] Extract known feature reports.
[x] Extract legacy monoliths.
[x] Extract old facades.
[x] Extract old helpers.
[x] Extract old runtime flows.
[x] Extract old database/query/migration behavior.
[x] Extract old auth/session/cache/mail/queue/view behavior.
```

Output:

```text
Code-Review-And-ToDo/muscle-recovery/backup-muscle-inventory.md
Code-Review-And-ToDo/muscle-recovery/backup-muscle-inventory.json
```

Required table:

```md
| Old path | Old namespace | Feature | Behavior summary | Tests found | Target version | Target component | Action |
|---|---|---|---|---|---|---|---|
```

Target version values:

```text
V1
V2
V3
drop
external
human-decision
```

Action values:

```text
move
slice
restore
wrap
bridge
replace-with-new
drop
postpone
```

Acceptance:

```text
[x] Every meaningful backup feature is listed.
[x] Every old monolith is listed.
[x] Every old test family is listed.
[x] Nothing is implemented yet.
```

---

## Stage V1-02 — Current Component Muscle Audit

Goal:

```text
Compare current repo against backup inventory and identify lost muscles.
```

Tasks:

```text
[x] Read current components tree.
[x] Read component-completion matrix if present.
[x] Compare current files against backup inventory.
[x] Mark current component as muscular, partial, skeleton, missing, or stale.
[x] Identify code that exists but is disconnected.
[x] Identify tests that exist but are stale namespace/test-only.
```

Output:

```text
Code-Review-And-ToDo/muscle-recovery/component-muscle-audit.md
```

Required table:

```md
| Component | Current state | Backup muscle | Missing behavior | Target version | Target path | Priority | Risk |
|---|---|---|---|---|---|---|---|
```

Component states:

```text
muscular
partial
skeleton
missing
stale
unknown
```

Acceptance:

```text
[x] Every component has a state.
[x] Every missing V1 muscle has a target path.
[x] Every V2/V3 muscle remains locked.
[x] No production code changed.
```

---

## Stage V1-03 — Static Integrity Closure

Goal:

```text
Remove static-analysis noise before restoring more muscle.
```

Tasks:

```text
[ ] Close or classify remaining critical broken refs.
[ ] Split remaining multi-class production files.
[ ] Clean framework/System PHPStan.
[ ] Clean Application/Cache PHPStan.
[ ] Clean HTTP Request/Response PHPStan.
[ ] Clean DataStack/Database PHPStan.
[ ] Clean Operations core PHPStan.
```

Output:

```text
Code-Review-And-ToDo/v1-integrity/static-integrity-closure-report.md
```

Commands:

```bash
composer validate --no-check-publish
composer dump-autoload -o

php tooling/audit_broken_refs.php
php tooling/refactor/categorize-broken-refs.php
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php

vendor/bin/phpstan analyse framework/System --memory-limit=1G --error-format=raw --no-progress
vendor/bin/phpstan analyse components/Application/Cache --memory-limit=1G --error-format=raw --no-progress
vendor/bin/phpstan analyse components/HTTP/Request components/HTTP/Response --memory-limit=1G --error-format=raw --no-progress
vendor/bin/phpstan analyse components/DataStack/Database --memory-limit=1G --error-format=raw --no-progress
```

Acceptance:

```text
[ ] Composer remains GREEN.
[ ] Autoload remains GREEN.
[ ] Production PSR-4 skips remain zero.
[ ] Unresolved production-critical broken refs are zero.
[ ] framework/System PHPStan is green or remaining errors are fully classified.
[ ] No V2/V3 implementation happened.
```

---

# 6. V1 Muscle Restoration Plan

Restore V1 muscles in waves.

## Wave V1-A — Small Self-Contained Muscles

These should come first because they are low-risk and improve DX immediately.

### Application/Text

Old muscle:

```text
Str-like behavior, string operations, text helpers.
```

New target:

```text
components/Application/Text/System/
  PublicSurface/
  Capabilities/
    Case/
    Search/
    Replace/
    Slug/
    Normalize/
  Foundation/
```

Rules:

```text
[ ] Do not restore Str as god-class.
[ ] Restore behavior as small named capabilities.
[ ] Optional facade can delegate to capabilities.
```

### DataStack/Data

Old muscle:

```text
Arr, Collection, LazyCollection, Enumerable, structures, transformation/search/aggregate operations.
```

New target:

```text
components/DataStack/Data/System/
  PublicSurface/
    Data.php
    Collection.php
    Arrhae.php
  Capabilities/
    Collections/
    Arrays/
    Objects/
    Paths/
    Structures/
    Operators/
```

Rules:

```text
[ ] Do not restore Arr as god-class.
[ ] Keep public API small.
[ ] Put operators in capability folders.
[ ] Preserve useful behavior from backup.
```

### Application/DateTime

Old muscle:

```text
Carbon-like date helper behavior.
```

New target:

```text
components/Application/DateTime/System/
  PublicSurface/
    DateTime.php
    Clock.php
  Capabilities/
    ParseTime/
    FormatTime/
    CompareTime/
    TravelTime/
    FreezeTime/
    HumanizeTime/
  Foundation/
    Values/
    Failure/
```

Rules:

```text
[ ] Do not clone Carbon as one huge class.
[ ] Implement Carbon-like developer experience through small capabilities.
[ ] Keep Carbon compatibility optional or facade-level only.
```

### Application/Config

Old muscle:

```text
Environment config, config repository, typed access.
```

New target:

```text
components/Application/Config/System/
  PublicSurface/
    Config.php
  Capabilities/
    LoadConfiguration/
    ReadConfiguration/
    ValidateConfiguration/
    CacheConfiguration/
```

### Application/Facade

Old muscle:

```text
Global ergonomic entrypoints.
```

New target:

```text
components/Application/Facade/System/
  PublicSurface/
    Facade.php
  Capabilities/
    ResolveFacadeTarget/
    BindFacadeRoot/
    ResetFacadeState/
```

Rules:

```text
[ ] Facade must not contain behavior.
[ ] Facade delegates.
[ ] Long-lived runtime reset must be explicit.
```

Output for Wave V1-A:

```text
Code-Review-And-ToDo/muscle-recovery/v1-wave-a-small-muscles-report.md
```

Acceptance:

```text
[ ] Text, Data, DateTime, Config, Facade are restored or explicitly classified.
[ ] Component PHPStan passes for touched components.
[ ] Public API tests exist for restored public surfaces.
```

---

## Wave V1-B — Interdependent Framework Muscles

### Application/Container

Old muscle:

```text
DI, provider lifecycle, autowiring, resolution, scopes, metrics.
```

New target:

```text
components/Application/Container/System/
  PublicSurface/
    Container.php
    ContainerInterface.php
  Flows/
    BuildContainer/
    ResolveDependency/
    CallFunction/
    OpenScope/
    CloseScope/
  Capabilities/
    Registrations/
    Resolution/
    Calls/
    Injection/
    Scopes/
    Providers/
    Observability/
```

Rules:

```text
[ ] Public surface must not expose internal dependency-injection vocabulary unless intentionally public.
[ ] Provider contracts depend on public/narrow contracts.
[ ] Request scope must integrate with framework/System.
```

### framework/System Runtime

Old muscle:

```text
Kernel/runtime/bootstrap behavior.
```

New target:

```text
framework/System/
  PublicSurface/
    Avax.php
    RuntimeKernel.php
    HttpKernel.php
    ConsoleKernel.php
    WorkerKernel.php
  Flows/
    BootApplication/
    RunHttpRequest/
    RunConsoleCommand/
    RunWorkerJob/
    ResetApplicationState/
    RunDoctor/
  Capabilities/
    Runtime/
    RequestScope/
    StateReset/
    RuntimeSafety/
    RuntimeManifest/
    ControlPlane/
```

Acceptance:

```text
[ ] Kernel boots.
[ ] HTTP request can run.
[ ] Console command can run.
[ ] Request scope opens/closes.
[ ] State reset runs.
[ ] Runtime doctor passes.
```

Output for Wave V1-B:

```text
Code-Review-And-ToDo/muscle-recovery/v1-wave-b-framework-muscles-report.md
```

---

## Wave V1-C — Complex Runtime Muscles

### Application/Cache

Old muscle:

```text
Runtime cache, compiled cache, stores, remember semantics, null-value behavior, named stores.
```

New target:

```text
components/Application/Cache/System/
  PublicSurface/
    Cache.php
    CacheStore.php
    CacheReadTarget.php
  Flows/
    ReadCache/
    WriteCache/
    RememberCachedValue/
    ClearCache/
    CompileCache/
  Capabilities/
    StoreCachedValues/
    ReadCachedValues/
    ManageCompiledCache/
    Expiration/
    Serialization/
```

V1 scope:

```text
[ ] runtime store
[ ] file store
[ ] in-memory/fake store
[ ] named stores
[ ] null caching
[ ] compiled cache basic
```

V2 scope:

```text
[ ] distributed cache
[ ] stampede protection
[ ] stale-while-revalidate
[ ] replacement policies
[ ] multi-tier cache
```

### HTTP/Session

Old muscle:

```text
Full session system.
```

New target:

```text
components/HTTP/Session/System/
  PublicSurface/
    Session.php
  Flows/
    StartSession/
    ReadSession/
    WriteSession/
    RegenerateSession/
    DestroySession/
  Capabilities/
    Stores/
    Cookies/
    Flash/
    Csrf/
    Encryption/
```

### Operations/Queue

Old muscle:

```text
Queue, jobs, worker management.
```

V1 scope:

```text
[ ] in-memory/fake queue
[ ] sync queue
[ ] job contract
[ ] worker basic
[ ] failed job model
```

V2 scope:

```text
[ ] message broker integration
[ ] dead letter
[ ] retries/backoff
[ ] supervised workers
[ ] distributed queue adapters
```

### Operations/Mail

Old muscle:

```text
Mail system.
```

V1 scope:

```text
[ ] mail message object
[ ] fake mailer
[ ] SMTP boundary
[ ] template integration
```

V2 scope:

```text
[ ] provider adapters
[ ] queue integration
[ ] retry/failure diagnostics
```

Output for Wave V1-C:

```text
Code-Review-And-ToDo/muscle-recovery/v1-wave-c-complex-runtime-muscles-report.md
```

---

## Wave V1-D — Massive Framework Muscles

### HTTP/Router

Old muscle:

```text
Full routing system, route registration, matching, middleware handoff.
```

New target:

```text
components/HTTP/Router/System/
  PublicSurface/
    Router.php
    Route.php
  Flows/
    RegisterRoute/
    MatchRoute/
    DispatchRoute/
    BuildRouteUrl/
  Capabilities/
    Routes/
    Matching/
    Parameters/
    Groups/
    Middleware/
```

### HTTP/Request + HTTP/Response + Middleware

Old muscle:

```text
Request handling, response building, emitters, middleware pipeline.
```

New target:

```text
components/HTTP/Request/System/
components/HTTP/Response/System/
components/HTTP/Middleware/System/
```

Required V1 behavior:

```text
[ ] immutable request
[ ] server request creation
[ ] headers
[ ] query/body/files
[ ] JSON/form parsing
[ ] response factory
[ ] body/stream
[ ] emitter
[ ] middleware pipeline
```

### DataStack/Database

Old muscle:

```text
QueryBuilder, schema builder, migrations, connections, transactions, observability.
```

New target:

```text
components/DataStack/Database/System/
  PublicSurface/
    Database.php
    QueryBuilder.php
    Schema.php
    Migration.php
  Flows/
    RunQuery/
    BuildQuery/
    RunMigration/
    RollbackMigration/
  Capabilities/
    Connections/
    QueryBuilding/
    Grammar/
    SchemaBuilding/
    Migrations/
    Transactions/
    Observability/
```

### DataStack/Persistence

Old muscle:

```text
UnitOfWork, IdentityMap, Repository, Hydration, Entity persistence.
```

New target:

```text
components/DataStack/Persistence/System/
  PublicSurface/
    Persistence.php
    Repository.php
  Capabilities/
    UnitOfWork/
    IdentityMap/
    Repositories/
    Hydration/
    ChangeTracking/
```

### Identity/Auth

Old muscle:

```text
OAuth2, OIDC, SSO, SCIM, Passkeys, MFA.
```

V1 scope:

```text
[ ] basic authentication
[ ] authorization separation
[ ] tokens
[ ] credentials
[ ] session integration
[ ] AuthMiddleware
```

V2 scope:

```text
[ ] OAuth2/OIDC
[ ] SSO
[ ] SCIM
[ ] Passkeys
[ ] MFA
[ ] risk-based access
[ ] tenant-aware auth
```

Output for Wave V1-D:

```text
Code-Review-And-ToDo/muscle-recovery/v1-wave-d-massive-muscles-report.md
```

---

## Wave V1-E — New Missing V1 Features

These were identified as missing or partial.

### Localization

```text
components/Application/Localization/System/
  PublicSurface/
    Translator.php
    Lang.php
  Flows/
    TranslateMessage/
    LoadTranslations/
  Capabilities/
    Catalogs/
    Locale/
    Fallbacks/
```

### Notifications

```text
components/Operations/Notifications/System/
  PublicSurface/
    Notifications.php
    Notification.php
  Flows/
    SendNotification/
  Capabilities/
    Channels/
    Recipients/
    Templates/
```

V1 scope:

```text
[ ] generic notification contract
[ ] fake channel
[ ] mail channel if Mail exists
[ ] security notification integration
```

### AuthMiddleware

```text
components/HTTP/Security/System/Capabilities/Authentication/
  RequireAuthenticatedRequest.php
```

or:

```text
components/HTTP/Middleware/System/Capabilities/Auth/
  AuthenticateRequest.php
```

Decision needed:

```text
If middleware pipeline owns execution, place middleware there.
If security policy owns auth requirement, place policy under HTTP/Security and adapter middleware under HTTP/Middleware.
```

Output:

```text
Code-Review-And-ToDo/muscle-recovery/v1-wave-e-new-missing-features-report.md
```

---

# 7. V1 Definition of Done

V1 is locked only when:

```text
[ ] Composer validate passes.
[ ] Composer dump-autoload -o passes.
[ ] No production PSR-4 skips.
[ ] Component suite checker passes.
[ ] Duplicate owner checker passes.
[ ] Namespace drift checker passes.
[ ] Public surface checker passes.
[ ] Runtime leak checker passes.
[ ] Compatibility aliases are valid.
[ ] Broken refs have zero unresolved production-critical refs.
[ ] PHPStan passes for framework/components/tests or has honest non-masking baseline.
[ ] Psalm passes or is explicitly removed from required gates.
[ ] PHPUnit full V1 suite passes.
[ ] Kernel feature tests exist and pass.
[ ] Public API tests exist and pass.
[ ] Component muscle audit is complete.
[ ] V1 muscle restoration reports are complete.
[ ] Golden Path App passes using public API only.
[ ] Docs validate.
[ ] Docs mirror source.
[ ] Superglobals checker passes.
[ ] Runtime doctor passes.
```

V1 output:

```text
A muscular, production-ready kernel framework.
```

---

# 8. V2 Unlock Conditions

V2 can start only when:

```text
[ ] V1 Kernel Green is PROVEN.
[ ] V1 muscles are restored or explicitly postponed.
[ ] Core components are type-clean or baselined honestly.
[ ] Critical public APIs have tests.
[ ] Runtime state safety is proven.
```

V2 implementation remains locked until then.

---

# 9. V2 Enterprise Platform Engines

V2 is not random features.

V2 is a set of platform engines.

## V2 Engine 1 — API Contract Engine

Target:

```text
components/API/
  Contracts/
  OpenAPI/
  GraphQL/
  Rest/
  JsonApi/
  Webhooks/
  Rpc/
```

Required behavior:

```text
[ ] OpenAPI generation.
[ ] API contract validation.
[ ] Breaking change detection.
[ ] REST resource model.
[ ] JSON:API document model.
[ ] Webhook signatures.
[ ] Webhook idempotency.
[ ] GraphQL schema.
[ ] GraphQL resolver registry.
[ ] GraphQL DataLoader/batching.
[ ] GraphQL query complexity/depth limits.
[ ] Resolver timing observability.
```

## V2 Engine 2 — Integration Engine

Target:

```text
components/Integration/
  ObjectStorage/
  SearchIndex/
  MessageBroker/
  StreamProcessor/
  Media/
  Recommendations/
  CDN/
```

Every integration must have:

```text
[ ] public contract
[ ] fake adapter
[ ] null/local adapter where useful
[ ] production adapter boundary
[ ] config schema
[ ] health check
[ ] failure model
[ ] timeout policy
[ ] retry policy
[ ] circuit-breaker integration where external I/O exists
[ ] observability events
[ ] contract tests
[ ] docs/example
```

## V2 Engine 3 — Reliability Engine

Target:

```text
components/Operations/Resilience/
```

Required primitives:

```text
Retry
Timeout
CircuitBreaker
Fallback
Bulkhead
DeadLetter
Compensation
Saga
Lease
Lock
Idempotency
Backpressure
LoadShedding
```

## V2 Engine 4 — Observability Engine

Target:

```text
components/Operations/Observability/
```

Required:

```text
[ ] structured logs
[ ] metrics
[ ] traces
[ ] request timeline
[ ] job timeline
[ ] command timeline
[ ] DB timing
[ ] cache hit/miss
[ ] external call timing
[ ] audit events
[ ] sensitive data redaction
[ ] OpenTelemetry exporter boundary
```

## V2 Engine 5 — Runtime Supervision + Background Processes

Target:

```text
framework/System/Capabilities/RuntimeSupervision/
components/Operations/BackgroundProcesses/
```

Required:

```text
[ ] process registry
[ ] start/stop/restart
[ ] restart policies
[ ] health policies
[ ] worker heartbeat
[ ] graceful shutdown
[ ] systemd/supervisord boundaries
```

## V2 Engine 6 — Memory Lifecycle / GC Policy

Target:

```text
framework/System/Capabilities/MemoryManagement/
```

Required:

```text
[ ] memory budgets
[ ] request cleanup
[ ] job cleanup
[ ] gc_collect_cycles policy
[ ] leak suspects report
[ ] long-lived worker memory checks
```

## V2 Engine 7 — Delivery Engine

Target:

```text
framework/System/Capabilities/Delivery/
framework/System/Capabilities/Compilation/
```

Required:

```text
[ ] compile app
[ ] compile config
[ ] compile routes
[ ] compile container
[ ] warm runtime
[ ] build manifest
[ ] verify release
[ ] smoke release
[ ] rollback plan
[ ] evidence report
```

V2 output:

```text
AvaX becomes an enterprise platform framework.
```

---

# 10. V3 Unlock Conditions

V3 production implementation can start only when:

```text
[ ] V1 Kernel Green is proven.
[ ] V2 platform baseline is at least YELLOW/GREEN.
[ ] labs/SystemDesignKit MVP is approved.
[ ] At least one V2 Integration Engine and one V2 Reliability Engine are usable.
```

V3 may be planned earlier, but not implemented in production `components/SystemDesign/`.

---

# 11. V3 Executable System Design Framework

Start in:

```text
labs/SystemDesignKit/
```

Promote later to:

```text
components/SystemDesign/
```

Target:

```text
components/SystemDesign/
  Capacity/
  LoadModel/
  LatencyBudget/
  Availability/
  Consistency/
  Partitioning/
  Replication/
  Sharding/
  Messaging/
  Projections/
  Caching/
  Failure/
  Simulation/
  ArchitectureTests/
  ReferenceArchitecture/
  ScenarioRunner/
  TradeoffReport/
  InfrastructureBoundary/
  ApiSurface/
```

V3 must validate:

```text
[ ] requests per second
[ ] read/write ratio
[ ] storage growth
[ ] cache hit ratio
[ ] fanout size
[ ] queue depth
[ ] latency budget
[ ] SLO/SLA
[ ] failure budget
[ ] command/query side split
[ ] read model
[ ] projection
[ ] materialized view
[ ] cache-aside
[ ] write-through cache
[ ] eventual consistency
[ ] outbox
[ ] consumer
[ ] subscriber
[ ] retry policy
[ ] dead letter queue
[ ] idempotency key
```

V3 CLI target:

```bash
php avax system-design:validate reference-architectures/url-shortener
php avax system-design:capacity reference-architectures/url-shortener/capacity.yaml
php avax system-design:simulate reference-architectures/url-shortener --scenario=redis-outage
php avax system-design:check examples/system-design/url-shortener
php avax system-design:tradeoffs reference-architectures/news-feed
```

V3 output:

```text
AvaX can test whether architecture makes sense.
```

---

# 12. Backup Feature Placement Matrix

Use this as initial classification.

```md
| Backup muscle | Target version | Target component | Notes |
|---|---|---|---|
| Text / Str behavior | V1 | Application/Text | Do not restore god-class. Slice into capabilities. |
| Arr behavior | V1 | DataStack/Data | Public Arrhae optional. Operators internal. |
| Collection / LazyCollection / Enumerable | V1 | DataStack/Data | Restore as real DX muscle. |
| Carbon-like DateTime | V1 | Application/DateTime | Custom DateTime with Carbon-like DX, not monolith. |
| Config | V1 | Application/Config | Required early. |
| Container / DI | V1 | Application/Container + framework/System | Must integrate with request scope. |
| Facade | V1 | Application/Facade | Thin public ergonomics only. |
| Cache | V1/V2 | Application/Cache | Runtime cache V1, distributed/stampede V2. |
| Session | V1 | HTTP/Session | Full session lifecycle. |
| Router | V1 | HTTP/Router | Full route registration/matching/dispatch. |
| Request/Response | V1 | HTTP/Request + HTTP/Response | Kernel path. |
| Middleware | V1 | HTTP/Middleware | Pipeline and built-ins. |
| HTTP Client | V1 or V2 | HTTP/Client or Integration/Http | If already complete, keep V1 utility. External policy V2. |
| View / BladeOne | V1 | Presentation/View | Keep vendor-like monolith isolated. |
| Mail | V1/V2 | Operations/Mail | Basic V1, providers/retry/queue V2. |
| Queue / Worker | V1/V2 | Operations/Queue | Sync/fake/basic worker V1, broker/distributed V2. |
| Notifications | V1/V2 | Operations/Notifications | General notification system missing. |
| Logging | V1/V2 | Operations/Observability | Minimal logs V1, full observability V2. |
| ErrorHandler / DumpDebugger | V1 | DeveloperTools/Diagnostics + DumpDebugger | Required for DX. |
| Filesystem | V1/V2 | Application/Filesystem | Local/filesystem V1, async/object storage V2. |
| Database QueryBuilder | V1 | DataStack/Database | Must be muscular. |
| Schema/Migrations | V1 | DataStack/Database | Must be muscular. |
| Transactions/Observability | V1/V2 | DataStack/Database + Operations/Observability | Basic V1, deep V2. |
| Persistence / ORM-like behavior | V1 | DataStack/Persistence | UnitOfWork/IdentityMap/Repository/Hydration. |
| Auth basic | V1 | Identity/Auth + HTTP/Security | Basic authentication/authorization. |
| OAuth2/OIDC/SSO/SCIM | V2 | Identity/ExternalIdentity + IdentitySync | Too large for V1. |
| MFA/Passkeys | V2 | Identity/Credentials | Platform security. |
| Tenancy | V1/V2 | Identity/Tenancy | Basic tenant context V1, resource budgets/policies V2. |
| i18n / Translator | V1 | Application/Localization | Build new. |
| API Contract/OpenAPI | V2 | API/OpenAPI + API/Contracts | Platform engine. |
| GraphQL | V2 | API/GraphQL | Experimental until proven. |
| ObjectStorage | V2 | Integration/ObjectStorage | Port/adapters. |
| SearchIndex | V2 | Integration/SearchIndex | Port/adapters. |
| MessageBroker | V2 | Integration/MessageBroker | Port/adapters. |
| StreamProcessor | V2 | Integration/StreamProcessor | Port boundary only. |
| TranscodingGateway | V2 | Integration/Media | Gateway, not transcoding engine. |
| RecommendationEngine | V2 | Integration/Recommendations | Gateway, not ML platform. |
| CdnInvalidator | V2 | Integration/CDN | Gateway. |
| Capacity/Consistency/Simulation | V3 | SystemDesign/* | Executable architecture validation. |
```

---

# 13. AI TODO: Full Lockdown Order

Use this order. Do not skip.

```text
[ ] V1-00 Truth Correction and Gate Lock
[ ] V1-01 Backup Muscle Inventory
[ ] V1-02 Current Component Muscle Audit
[ ] V1-03 Static Integrity Closure
[ ] V1-A Restore Small Self-Contained Muscles
[ ] V1-B Restore Framework/Container Runtime Muscles
[ ] V1-C Restore Complex Runtime Muscles
[ ] V1-D Restore Massive Framework Muscles
[ ] V1-E Build Missing V1 Features
[ ] V1-F Add V1 Proof Tests
[ ] V1-G Run V1 Kernel Green Proof
[ ] V2-00 Re-check unlock conditions
[ ] V2-01 Build API Contract Engine
[ ] V2-02 Build Integration Engine
[ ] V2-03 Build Reliability Engine
[ ] V2-04 Build Observability Engine
[ ] V2-05 Build Runtime Supervision/Memory/Delivery Engines
[ ] V2-06 Run V2 Platform Baseline Proof
[ ] V3-00 Start labs/SystemDesignKit
[ ] V3-01 Build capacity/consistency/messaging/failure model
[ ] V3-02 Build scenario runner and architecture tests
[ ] V3-03 Build reference architectures
[ ] V3-04 Promote to components/SystemDesign only after proof
```

---

# 14. One-Shot Prompt For The Next AI Run

```text
You are working on AvaX Muscular System Lockdown.

Read first:
1. CURRENT_TRUTH.md
2. Code-Review-And-ToDo/EXECUTION.md
3. TODO.md
4. avax-backup.txt
5. Code-Review-And-ToDo/v1-integrity/*
6. Code-Review-And-ToDo/master-plan/*

Current truth:
V1 Kernel Green is NOT PROVEN.
V2 Implementation is LOCKED.
V3 Implementation is LOCKED.
Composer/autoload are GREEN.
PHPStan is RED.
Tests are RED.
Broken refs are not fully green unless all critical refs are classified.

Task:
Run V1-01 and V1-02 only.

Do not modify production code.
Do not implement features.
Do not repair PHPStan.
Do not move files.
Do not add placeholders.
Do not unlock V2 or V3.

Goal:
Create a complete muscle inventory from avax-backup.txt and compare it with the current component tree.

Outputs:
- Code-Review-And-ToDo/muscle-recovery/backup-muscle-inventory.md
- Code-Review-And-ToDo/muscle-recovery/backup-muscle-inventory.json
- Code-Review-And-ToDo/muscle-recovery/component-muscle-audit.md

For each old feature, classify:
- old path
- old namespace
- behavior summary
- tests found
- target version: V1 / V2 / V3 / drop / external / human-decision
- target component
- action: move / slice / restore / wrap / bridge / replace-with-new / drop / postpone
- risk level
- required tests

Final response must include:
Stage:
Status:
Files changed:
Validation commands:
Muscles found:
V1 muscles missing:
V2 muscles postponed:
V3 muscles postponed:
Next allowed action:
```

---

# 15. Final Principle

Do not choose between skeleton and muscle.

AvaX needs both:

```text
new skeleton
old muscle
new discipline
proof gates
```

The target is not “lots of code”.

The target is:

```text
muscular behavior under brutally clean architecture
```
