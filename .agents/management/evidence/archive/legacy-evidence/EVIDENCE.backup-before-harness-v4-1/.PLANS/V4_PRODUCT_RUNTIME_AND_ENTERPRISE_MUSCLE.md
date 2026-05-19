# AvaX V4 — Product Runtime & Enterprise Muscle

Version: 1.0.0
Date: 2026-05-09
Status: **STAGE LOCKED — Planning complete, implementation not started**
Stage: **V4-00** (Integrity Lock & Stage Definition)
Predecessors: V1 Kernel Green (PROVEN), V2 Platform Baseline (CLOSED/GREEN), V3 SystemDesignKit (CLOSED/GREEN)

---

# 0. Glavna ideja V4

V4 nije "još komponenti". V4 je faza u kojoj AvaX iz framework arhitekture prelazi u **framework koji možeš realno da
pokreneš, koristiš, meriš, proširuješ i gradiš ozbiljne aplikacije na njemu**.

Dosadašnji tok:

```text
V1 = stabilizacija jezgra
V2 = platform baseline
V3 = system-design kit, capacity, consistency, messaging, data structure foundations
V4 = product runtime + enterprise muscle
```

Drugim rečima:

```text
V1/V2/V3 = mozak, skelet, discipline, modeli, komponente
V4       = telo, mišići, start dugme, runtime, developer DX, production proof
```

Trenutno stanje pre V4:

```text
PHPUnit:    1401 tests, 5252 assertions, 0 failures, 0 errors, 0 skipped
PHPStan:    clean
Governance: all 7 checks PASS
Test suites: Unit/Integration/Feature/Architecture/GoldenPath/Runtime/Security/Full defined
Deleted:    11 duplicate SystemDesignKit test files (227 duplicate tests removed)
```

AvaX ne sme da bude samo:

```text
7000 klasa + dobra arhitektura + gomila potencijala
```

Mora da postane:

```php
$app = Avax::create();

$app->get('/', fn () => 'Hello AvaX');

$app->run();
```

To je "start dugme".

---

# 1. V4 North Star

## Cilj

```text
Make AvaX usable as a real framework for building production-grade, system-design-grade applications.
```

To znači da AvaX mora imati:

```text
1. mali, jasan App API
2. runnable HTTP runtime
3. SecureRequest / DataTransfer DX
4. async/concurrent/parallel execution
5. storage/database/queue/outbox/reliability muscle
6. observability/security/doctor proof
7. reference apps
8. measurable performance
9. strict repo integrity
10. governance-backed development flow
```

---

# 2. Inspiration & Boundary Map

Ovo nije "copy list". Ovo je **inspiration and boundary list**. Svaki framework/biblioteka ovde inspiriše AvaX odluke,
ali ne diktira API.

## 2.1 Runtime / Async / High-Concurrency

| Source                  | Šta uzimamo kao ideju                                                        | AvaX odgovor                                                              | V4 status       |
|-------------------------|------------------------------------------------------------------------------|---------------------------------------------------------------------------|-----------------|
| **Framework X**         | simple async micro-framework, built-in server, direktan `App` API            | `V4 Runtime App Layer`, `Avax::create()`, `app->get()`, `app->run()`      | V4-01           |
| **ReactPHP**            | event loop, streams, async HTTP server/client, promises, process utilities   | prvi async runtime adapter, `ReactPhpRuntime`, Promise/Fiber bridge       | V4-02           |
| **Hyperf**              | Swoole coroutine server, high-load/microservice model, component composition | kasniji `SwooleRuntime`, coroutine-aware context, high-concurrency design | V4-17 (roadmap) |
| **Spiral / RoadRunner** | RoadRunner persistent worker model                                           | `RoadRunnerRuntime`, long-lived worker bridge                             | V4-17 (roadmap) |
| **Laravel Octane**      | warm app, boot once, serve many requests, Swoole/RoadRunner/FrankenPHP       | `WarmApplication`, request scope reset, leak detection                    | V4-03           |
| **Swoole / OpenSwoole** | coroutine/event-driven non-blocking PHP extension                            | Swoole/OpenSwoole runtime adapter                                         | V4-17 (roadmap) |
| **FrankenPHP**          | modern PHP app server/runtime                                                | future runtime adapter                                                    | V4-17 (roadmap) |
| **Workerman**           | long-lived event-driven PHP worker/server                                    | future runtime adapter option                                             | V4-17 (roadmap) |
| **Amp / Revolt**        | modern async event loop/promise ecosystem                                    | future runtime bridge                                                     | V4-17 (roadmap) |

**Hard rule:** ReactPHP is first. RoadRunner/Swoole/FrankenPHP come ONLY after V4-03 Warm Worker Safety is proven.

## 2.2 Lightweight Micro-Framework

| Source                 | Šta uzimamo                                      | AvaX odgovor                   |
|------------------------|--------------------------------------------------|--------------------------------|
| **Flight PHP**         | zero-dependency core, fast local DX              | `Avax\App` must stay tiny      |
| **Fat-Free Framework** | lightweight toolkit, simple route/API ergonomics | simple route DSL, low ceremony |
| **Framework X**        | async micro-framework + built-in server          | Runtime App Layer + ReactPHP   |

AvaX is not trying to become tiny in the same way, but V4 needs a **tiny public entrypoint** even if internals are
enterprise-grade.

## 2.3 Container / Serialization / Execution

| Source / concept                 | Šta uzimamo                              | AvaX odgovor                                         |
|----------------------------------|------------------------------------------|------------------------------------------------------|
| **Symfony Container**            | compiled container, lazy/service closure | Container generated code, not random closure persist |
| **Laravel Serializable Closure** | safe closure serialization               | `Foundation/CallableSerialization` (already exists)  |
| **Symfony Process**              | process-based parallelism                | `Parallelism` process pool (already exists)          |
| **Pokio**                        | test acceleration / process optimization | Labs-only investigation, not default                 |
| **ParaTest**                     | standard PHPUnit parallelization         | evaluated; incompatible with PHP 8.5                 |
| **Docker Compose**               | E2E environment orchestration            | V4 E2E test infra                                    |
| **PHPStan / PHPUnit**            | static + behavior validation             | hard gate                                            |
| **Rector custom rules**          | automated code hygiene/refactor          | AvaX governance fixer                                |
| **Composer scripts**             | test/dev command DX                      | `composer test`, `test:full`, `test:parallel`        |

## 2.4 Data / Database / Persistence

| Source / concept               | Šta uzimamo                             | AvaX odgovor                                          |
|--------------------------------|-----------------------------------------|-------------------------------------------------------|
| **Laravel Collections**        | fluent data manipulation                | `Collection` (already exists)                         |
| **Arrhae idea**                | array-native pipeline engine            | `Arrhae` (already exists)                             |
| **JSON document manipulation** | same vocabulary as Arrhae/Collection    | `Json` (already exists)                               |
| **PHP ext-ds**                 | real data structure vocabulary          | `Map`, `Set`, `Queue`, `Stack`, `PriorityQueue`, etc. |
| **Protocol Buffers**           | compact typed message schema            | future `DataContracts` / `BinaryCodec`                |
| **Doctrine**                   | Unit of Work, Identity Map, Data Mapper | Database muscle                                       |
| **Hibernate / EntityManager**  | persistence context patterns            | cautious inspiration                                  |
| **Entity Framework**           | LINQ-ish/query/data access ideas        | query/data mapper inspiration                         |
| **Redis**                      | cache, rate limit, queue, Bloom         | optional runtime dependency                           |
| **Debezium / Kafka**           | outbox/event relay inspiration          | Transactional Outbox + Event Relay                    |

## 2.5 Reliability / Distributed Systems

| Source / concept                                 | Šta uzimamo                      | AvaX odgovor             |
|--------------------------------------------------|----------------------------------|--------------------------|
| **Transactional Outbox**                         | atomic DB write + event publish  | `TransactionalOutbox`    |
| **Inbox pattern**                                | idempotent message consumption   | `Inbox`                  |
| **Dead Letter Queue**                            | failed message quarantine        | `DeadLetter`             |
| **Retry / timeout / circuit breaker / fallback** | reliability envelope             | `Reliability` component  |
| **Bulkhead**                                     | failure isolation                | `Bulkhead`               |
| **Backpressure**                                 | slow input when saturated        | `Backpressure`           |
| **Adaptive Rate Limiting**                       | dynamic throttling               | `AdaptiveRateLimit`      |
| **Resilience4j**                                 | JVM resilience pattern           | AvaX reliability engine  |
| **Akka Streams / Go channels**                   | stream/backpressure mental model | future stream processing |

## 2.6 Observability / Security / Governance

| Source / concept                                        | Šta uzimamo                  | AvaX odgovor                       |
|---------------------------------------------------------|------------------------------|------------------------------------|
| **OpenTelemetry**                                       | traces, metrics, context     | `Telemetry`                        |
| **Jaeger / Honeycomb**                                  | tracing UI ecosystem         | exporter-ready tracing             |
| **OWASP**                                               | secure defaults              | security governance                |
| **OPA / Rego**                                          | policy-as-code               | optional future adapter            |
| **LaunchDarkly / Flipt**                                | feature flags and rollout    | AvaX `FeatureFlags`                |
| **Filament / Admin panels**                             | admin/control-plane DX       | minimal AvaX control plane         |
| **BladeOne**                                            | lightweight template         | optional admin/dashboard rendering |
| **Cloudflare Workers / Shopify Functions / Istio WASM** | sandboxed plugin inspiration | V5/Labs WASM plugin boundary       |

---

# 3. V4 Core Architecture Shape

V4 should not be one monster folder. It must be a sequence of **stage slices**, each independently validated before the
next begins.

High-level V4 shape:

```text
V4-00  Integrity Lock & Stage Definition
V4-01  Runtime App Layer + Zero-Dependency Feeling + Error Handling + Health Check baseline
V4-02  Reactive HTTP Runtime (ReactPHP) + Serve Modes
V4-03  Warm Worker Safety + Warm State Contract + MemoryGuard
V4-04  Developer Experience + Route Cache + Configuration as Code
V4-05  Data Platform Productization
V4-06  Storage Platform
V4-07  Database Muscle + Connection Pooling
V4-08  Queue & Worker Runtime
V4-09  Reliability Engine
V4-10  Messaging & Consistency + Service-to-Service basics
V4-11  Observability & Telemetry
V4-12  Security & Policy Runtime + Service Discovery basics
V4-13  System Design Runtime Kit
V4-14  Runtime Doctor & Control Plane + Health Check endpoints
V4-15  Reference Applications
V4-16  Benchmarks & Production Proof + Cold/Warm measurements
V4-17  Optional Runtime Adapters (RoadRunner/Swoole/FrankenPHP)
```

**Note:** This shape is updated by Framework Maturity Gates (Section 27). See Section 27.10 for the full stage order
with acceptance gates.

---

# 4. V4-00 — Integrity Lock & Stage Definition

## Goal

Pre nego što V4 počne, repo mora biti čist i stage-lockovan.

Trenutni signal:

```text
PHPUnit:     1401 tests, 5252 assertions, 0 failures, 0 errors, 0 skipped
PHPStan:     clean
Governance:  all 7 checks PASS
Test suites: Unit/Integration/Feature/Architecture/GoldenPath/Runtime/Security/Full
Deleted:     11 duplicate SystemDesignKit test files
```

V4-00 zaključava pravila:

```text
No skipped tests
No flaky tests
No placeholder tests
No fake GREEN
No broken refs in production paths
No broad ignored PHPStan debt without explicit ticket
No V4 implementation without stage lock
V4-01 cannot start until V4-00 is GREEN
```

## Required validation

```bash
composer validate --no-check-publish
composer dump-autoload -o
composer test:full
vendor/bin/phpstan analyse framework components tests --memory-limit=1G
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
php tooling/refactor/check-component-canonical-shape.php
php tooling/refactor/check-advanced-pattern-folder-violations.php
```

## Acceptance

```text
GREEN only if:
- full test suite passes (1401+ tests, 0 skipped)
- PHPStan clean
- all 7 governance checks pass
- stage lock says V4 active
- V4 master plan written and validated
```

## Status

**COMPLETE** — This document is the V4 master plan. Stage lock is active.

---

# 5. V4-01 — Runtime App Layer

## Inspiration

Framework X, Flight PHP, Fat-Free.

## Goal

AvaX must have a direct, tiny public framework API.

**Design principle:** Zero-Dependency Feeling (Section 27.7). The user must not see Container, Router,
MiddlewarePipeline, or any internal class.

```php
$app = Avax::create();

$app->get('/', fn () => 'Hello AvaX');

$app->post('/register', RegisterUser::class);

$app->run();
```

## Public API

```php
Avax::create(): App

$app->get(string $path, callable|string|array $action): Route
$app->post(string $path, callable|string|array $action): Route
$app->put(string $path, callable|string|array $action): Route
$app->patch(string $path, callable|string|array $action): Route
$app->delete(string $path, callable|string|array $action): Route
$app->any(string $path, callable|string|array $action): Route

$app->use(string|callable $middleware): self
$app->group(string $prefix, callable $routes): self
$app->run(): void
```

## Error Handling (first-class)

```text
Global exception handler must:
- catch all unhandled exceptions
- map to appropriate HTTP status (500, 503, 404, 405, etc.)
- return JSON for API requests, HTML for browser requests
- log with correlation ID
- never expose stack traces in production
- include error ID for support tracking
- categorize: user error, system error, security error
```

## Health Check baseline

```text
/health endpoint must exist after V4-01:
- returns 200 with {"status": "ok"}
- includes framework version
- includes uptime
- full /health/live and /health/ready come in V4-14
```

## Internal shape

```text
framework/System/PublicSurface/
  Avax.php                          — static factory
  App.php                           — runnable app API

framework/System/Flows/CreateApplication/
  CreateApplication.php

framework/System/Flows/RegisterHttpRoute/
  RegisterHttpRoute.php

framework/System/Flows/RunApplication/
  RunApplication.php

framework/System/Capabilities/RouteRegistration/
  RegisterGetRoute.php
  RegisterPostRoute.php
  RegisterRouteMiddleware.php
  RegisterGlobalMiddleware.php

framework/System/Capabilities/ResponseNormalization/
  NormalizeControllerResult.php
  ConvertArrayToJsonResponse.php
  ConvertStringToTextResponse.php
  ConvertDataObjectToJsonResponse.php
  ConvertPromiseToResponse.php

framework/System/Capabilities/ErrorHandling/
  GlobalExceptionHandler.php
  MapExceptionToHttpStatus.php
  RenderErrorResponse.php
  CategorizeException.php

framework/System/Capabilities/HealthCheck/
  CheckApplicationHealth.php         — baseline only
```

## Must reuse existing

```text
Router
HTTP Request/Response
Container
SecureRequest
Middleware
Exception rendering
DataTransfer
CallableSerialization
Concurrency
```

## Anti-patterns (forbidden)

```text
$app->getRouter()->addRoute(...)          — leaks Router
$app->getContainer()->make(...)           — leaks Container
$app->getMiddlewarePipeline()->add(...)   — leaks pipeline
$app->setRequestFactory(...)              — unnecessary configuration
$app->bind(Something::class, ...)         — leaks Container API
```

## Tests

```text
- app registers GET route
- app registers POST route
- app invokes closure route
- app invokes controller class
- app uses Container for controller invocation
- SecureRequest autowires into controller
- string return becomes text response
- array return becomes JSON response
- DataObject return becomes JSON response
- exception maps to response
- global middleware runs
- route middleware runs
- App public surface is thin (architecture test)
- Zero-Dependency Feeling: no internal classes visible in App public methods
- Error Handling: global exception handler catches and maps
- Error Handling: production mode hides stack trace
- Health Check baseline: /health returns 200 with status ok
```

## Non-goals

```text
No ReactPHP yet
No Swoole yet
No RoadRunner yet
No async DB yet
No Scaffolding yet
No full health endpoints (/health/live, /health/ready) — V4-14
```

## Component location

```text
framework/          — Avax, App, Flows, Capabilities
NO new components/  — App layer is framework responsibility
```

---

# 6. V4-02 — Reactive HTTP Runtime

## Inspiration

Framework X + ReactPHP.

ReactPHP gives PHP an event loop and low-level async building blocks: streams, async DNS, network client/server, HTTP
client/server, and process interaction. This makes it the right first async runtime because it does not require
Swoole/OpenSwoole extensions.

## Goal

AvaX can run as a reactive HTTP server.

**Serve Modes:** V4-02 must support the serve mode matrix (Section 27.4). At minimum: dev, react, and smoke modes.

```bash
php avax serve
```

or:

```php
$app->run();  // auto-detects available runtime
```

## Runtime shape

```text
framework/System/Runtime/ReactPhp/
  RunReactHttpServer.php
  ConvertReactRequestToAvaxRequest.php
  ConvertAvaxResponseToReactResponse.php
  AwaitReactPromise.php
  StopReactRuntime.php

framework/System/Capabilities/ServeModes/
  DetectAvailableRuntime.php
  StartDevServer.php
  StartSmokeServer.php
  SelectServeMode.php
```

## Public command

```bash
php avax serve
php avax serve --host=127.0.0.1 --port=8080
php avax serve --runtime=reactphp
php avax serve --smoke
```

## Requirements

```text
- start server
- register routes
- handle request
- return response
- middleware works
- SecureRequest works
- exception rendering works
- graceful shutdown
- runtime-specific code does not leak into public App API
- serve mode matrix: dev, react, smoke modes work
- auto-detect available runtime
```

## Tests

```text
- React runtime can instantiate
- React runtime can serve one request in smoke mode
- React request converts to AvaX request
- AvaX response converts to React response
- string response works
- JSON response works
- SecureRequest controller works
- runtime-specific classes stay behind Runtime/ReactPhp
- graceful shutdown stops event loop
- serve starts in dev mode
- serve starts in smoke mode
- serve auto-detects runtime
- serve with invalid --runtime fails with useful message
```

## Dependency

```text
require-dev: react/http, react/event-loop, react/promise, psr/http-message
```

---

# 7. V4-03 — Warm Worker Safety

## Inspiration

Laravel Octane, RoadRunner, Swoole/OpenSwoole.

Laravel Octane's key idea: boot once, keep in memory, serve many requests through RoadRunner/Swoole/FrankenPHP. Powerful
but dangerous without reset safety.

## Goal

AvaX must be safe in long-lived workers.

**Warm State Contract** (Section 27.1) and **MemoryGuard** (Section 27.5) are first-class features of this stage, not
separate stages.

## Core problem

In PHP-FPM, every request starts fresh. In RoadRunner/Swoole/Octane-like runtimes, state can leak.

AvaX needs:

```text
request scope
scoped service reset
container scope lifecycle
static leak detection
runtime context reset
memory growth checks
correlation context reset
current user/session/request reset
```

## Warm State Contract

```text
Allowed Warm (staje toplo između requesta):
- Container compiled definitions / resolved stateless singletons
- Route table / compiled route cache
- Configuration (immutable after boot)
- Logger instances (without request context)
- Connection pool objects (not individual connections with state)
- Event listener registry (without attached request data)
- Middleware pipeline (without request-scoped data)
- Template engine compiled templates
- FeatureFlags loaded definitions
- Policy engine loaded rules

Must Reset (mora da se resetuje posle svakog requesta):
- Current request / response objects
- Current user / authentication context
- Correlation ID / trace context
- Session data
- Scoped container instances
- Query builder with bound parameters
- Database transaction state
- Cache tags that include request-scoped keys
- Any static state that was configured per-request
- Flash data
- Validation error bags
- Rate limiter request counters (per-request scope)
```

## MemoryGuard

```text
Configuration:
  memory_limit: 128M               — soft limit (trigger warning)
  memory_max: 256M                 — hard limit (force worker recycle)
  max_requests: 1000               — recycle after N requests regardless
  check_interval: 50               — check memory every N requests
  recycle_graceful: true           — finish current request before recycle

Behavior:
- track memory after each request
- warn when soft limit reached
- force recycle when hard limit reached
- force recycle when max_requests reached
- record memory snapshots for diagnostics
- calculate memory growth rate over time
- do NOT interrupt mid-request (graceful only)
- log recycle reason (memory vs request count)
```

## Shape

```text
framework/System/Runtime/WarmApplication/
  WarmStateContract.php           — explicit interface
  AllowedWarmState.php            — registry of what stays warm
  MustResetState.php              — registry of what must reset
  BootWarmApplication.php
  HandleWarmRequest.php
  ResetWarmRequestState.php       — executes reset between requests
  FlushScopedInstances.php
  DetectLeakedState.php
  DetectMemoryGrowth.php
  ReloadWorker.php

framework/System/Runtime/MemoryGuard/
  MonitorWorkerMemory.php
  CheckMemoryThreshold.php
  TriggerWorkerRecall.php
  RecordMemorySnapshot.php
  CalculateMemoryGrowthRate.php
```

## Tests

```text
- WarmStateContract lists all allowed warm items
- MustResetState lists all required reset items
- ResetWarmRequestState resets all must-reset items
- Current user from request 1 is NOT visible in request 2
- Correlation ID from request 1 is NOT visible in request 2
- Container scoped instance is different between requests
- MemoryGuard tracks memory per request
- MemoryGuard soft limit triggers warning log
- MemoryGuard hard limit triggers worker recycle
- MemoryGuard max_requests triggers worker recycle
- Memory growth threshold can be detected
- Static state leak detector catches configured leak
- Container scoped instances are flushed between requests
- Worker reload triggers correctly
- Memory snapshot recorded
- Growth rate calculated over N requests
- Graceful recycle finishes current request
- Recycle reason logged correctly
```

## Acceptance

**No runtime adapter (ReactPHP, RoadRunner, Swoole, FrankenPHP) may serve a second request without passing the Warm
State Contract tests. This is a hard gate.**

**No runtime adapter may run without MemoryGuard active. This is a hard gate.**

---

# 8. V4-04 — Developer Experience

## Goal

Framework feels usable. Developer can scaffold, inspect, and diagnose without reading docs.

## Commands

```bash
php avax make:secure-request RegisterRequest
php avax make:data-object CreateUserInput
php avax make:controller RegisterUser
php avax make:flow RegisterUser
php avax make:migration create_users_table
php avax make:test RegisterUserTest

php avax doctor
php avax inspect
php avax validate
php avax architecture:check
php avax runtime:check
```

## Scaffolding shape

```text
framework/System/Capabilities/Scaffolding/
  CreateSecureRequest/
  CreateDataObject/
  CreateController/
  CreateFlow/
  CreateMigration/
  CreateTest/
```

## Doctor shape

```text
framework/System/Capabilities/Doctor/
  CheckAutoload
  CheckPhpStan
  CheckTests
  CheckNamespaceDrift
  CheckPublicSurface
  CheckRuntimeLeaks
  CheckBrokenRefs
  CheckComponentShape
  CheckForbiddenNames
  RunDoctor/
```

## Acceptance

```text
- command generates correct file
- generated code follows how-to rules
- generated tests pass
- doctor reports useful output
- doctor exits non-zero on integrity failure
```

---

# 9. V4-05 — Data Platform Productization

## Already present / foundation

```text
components/DataStack/Data/
  Forms: Arrhae, Collection, Json
  Structures: Map, Set, Sequence, Queue, Stack, Graph, Matrix...
  Operators, Codecs, Lenses
  Foundation values

components/DataStack/DataTransfer/
  DataObject, attributes, hydration, casting, validation, serialization

components/HTTP/SecureRequest/
  request-as-DTO, Container autowiring, validation, authorization
```

## V4 goal

Turn this into user-facing product muscle.

## Features

```text
GenerateSchemaFromDataObject
GenerateRequestSchema
GenerateResponseSchema
GenerateOpenApiFromRoutes
GenerateJsonSchema
ValidatePayloadAgainstSchema
```

## Shape

```text
components/API/SchemaGeneration/
  System/
    Capabilities/SchemaFromDataObject/
    Capabilities/OpenApiFromRoutes/
    Capabilities/JsonSchemaGenerator/
    Flows/GenerateSchemaFromDataObject/
    Flows/GenerateOpenApiFromRoutes/
    Flows/ValidatePayloadAgainstSchema/
    PublicSurface/
      SchemaGeneration.php
```

## Tests

```text
- DataObject attributes generate schema
- Required maps to required field
- StringType maps to string
- Min/Max map to minLength/maxLength or numeric min/max
- RegexPattern maps to pattern
- SecureRequest route generates requestBody schema
- response DataObject generates response schema
- route list generates OpenAPI paths
```

## Non-goals

```text
No full Swagger UI yet
No full API Platform clone
No overbuilt schema registry
```

---

# 10. V4-06 — Storage Platform

## Decision

Split:

```text
Filesystem = local path/file/folder/permissions
Storage    = named disks / stored objects / visibility / URLs
```

## Filesystem shape

```text
components/Application/Filesystem/
  System/
    PublicSurface/Filesystem.php
    Flows/ReadFile/
    Flows/WriteFile/
    Flows/AppendToFile/
    Flows/DeleteFile/
    Flows/ListDirectory/
    Capabilities/LocalPaths/
    Capabilities/LocalPermissions/
```

## Storage shape

```text
components/Application/Storage/
  System/
    PublicSurface/Storage.php
    Capabilities/Disks/Disk.php
    Capabilities/Disks/LocalDisk/
    Flows/ReadStoredObject/
    Flows/WriteStoredObject/
    Flows/GenerateTemporaryStoredObjectUrl/
```

## Initial scope

```text
LocalDisk real and tested
S3 only if real dependency/config exists
No placeholder S3
```

## Tests

```text
- local disk put/get/exists/delete
- local disk uses Filesystem through composition
- unknown disk throws DiskNotFound
- temporary URL on local disk throws TemporaryUrlNotSupported
- Filesystem has zero dependency on Storage
```

---

# 11. V4-07 — Database Muscle

## Inspiration

Doctrine, Hibernate, Entity Framework, Laravel database layer.

## Goal

AvaX must support serious apps, not only HTTP routing.

**Connection Pooling** (Section 27.2) is a first-class feature of this stage, required for long-lived runtimes.

## Layers

```text
components/DataStack/Database/
  Query/              — QueryBuilder (already partial)
  Schema/             — SchemaBuilder (already partial)
  Migrations/         — Migration runner (already partial)
  Transactions/       — TransactionManager (already partial)
  DataMapping/        — NEW: DataMapper, Entity mapping
  UnitOfWork/         — NEW: Unit of Work pattern
  IdentityMap/        — NEW: Identity Map
  Projections/        — NEW: Read model projections
  ConnectionPool/     — NEW: Connection pooling for long-lived runtimes
```

## Feature list

```text
QueryBuilder        — existing, needs completion
SchemaBuilder       — existing, needs completion
Migrations          — existing, needs runner
TransactionManager  — existing, needs completion
ConnectionPool      — NEW (Section 27.2)
UnitOfWork          — NEW
IdentityMap         — NEW
Repository/DataMapper — NEW
ReadModel           — NEW
ProjectionStore     — NEW
Outbox table support— NEW (links to V4-10)
```

## Connection Pool (first-class)

```text
Pool config:
  min_connections: 2
  max_connections: 20
  idle_timeout: 30s
  max_lifetime: 3600s
  health_check_interval: 10s
  acquire_timeout: 5s
  reset_on_release: true           — must clear transaction state
  health_check_on_acquire: true    — must verify connection is alive

Component shape:
components/DataStack/Database/
  System/
    Capabilities/ConnectionPool/
      CreateConnectionPool.php
      AcquireConnectionFromPool.php
      ReleaseConnectionToPool.php
      CloseConnectionPool.php
      HealthCheckConnectionPool.php
      ResizeConnectionPool.php
    Foundation/
      ConnectionPool.php
      ConnectionPoolConfig.php
      ConnectionLease.php
      PoolExhaustedException.php
      StaleConnectionException.php

Required behavior:
- acquire returns healthy connection or waits until timeout
- release resets connection state (transaction, variables, locks)
- pool creates min_connections at boot
- pool creates up to max_connections under load
- pool closes idle connections after idle_timeout
- pool closes connections after max_lifetime
- health check replaces dead connections
- PoolExhaustedException when all connections busy and max reached
- Connection state must never leak between requests
```

## Notes

```text
SchemaBuilder vs Schema must be clarified.
EntityManager name should be reviewed.
DTOs and Enums must be first-class.
Superglobal scan should replace raw strings with typed enums where useful.
```

## Acceptance

```text
- migration can create table
- transaction wraps work
- query builder creates safe parameterized query
- schema model can inspect/build schema
- outbox can write event inside transaction
- DataMapper can persist and retrieve entity
- UnitOfWork tracks changes and flushes
- IdentityMap prevents duplicate object loading
- Connection Pool: creates min connections at boot
- Connection Pool: acquire/release works
- Connection Pool: release resets transaction state
- Connection Pool: PoolExhaustedException on max
- Connection Pool: health check replaces dead connection
```

---

# 12. V4-08 — Queue & Worker Runtime

## Goal

AvaX can dispatch and run background work.

## Features

```text
DispatchJob
RunWorker
RetryJob
FailJob
DeadLetterJob
StopWorker
WorkerHeartbeat
WorkerMemoryLimit
WorkerMaxJobs
WorkerGracefulShutdown
```

## Queue drivers

```text
Queue can use:
- Database (first)
- Redis (later)
- in-memory fake (testing)
```

## Shape

```text
components/Operations/Queue/
  System/
    PublicSurface/Queue.php
    Capabilities/Drivers/DatabaseQueueDriver/
    Capabilities/Drivers/MemoryQueueDriver/
    Capabilities/Workers/RunQueueWorker/
    Capabilities/Workers/WorkerHeartbeat/
    Flows/DispatchJob/
    Flows/RetryFailedJob/
    Flows/MoveJobToDeadLetter/
    Flows/StopWorker/
    Foundation/
      Job.php
      JobResult.php
      WorkerStatus.php
      QueueException.php
```

## Tests

```text
- dispatch job
- worker runs job
- failed job retries
- max retries moves to dead letter
- worker heartbeat written
- graceful stop works
- memory limit triggers stop
- max jobs limit triggers stop
- memory queue driver works for testing
```

---

# 13. V4-09 — Reliability Engine

## Inspiration

Resilience4j, distributed systems reliability patterns.

## Core features

```text
RetryPolicy
TimeoutPolicy
CircuitBreaker     — partially exists in GoldenPathRuntime
Bulkhead
Fallback           — partially exists in GoldenPathRuntime
Backpressure
AdaptiveRateLimit
IdempotencyKey
DeadLetter
Compensation
Lease/Lock
```

## Shape

```text
components/Operations/Reliability/
  System/
    PublicSurface/Reliability.php
    Capabilities/Retry/
    Capabilities/Timeout/
    Capabilities/CircuitBreaker/
    Capabilities/Bulkhead/
    Capabilities/Fallback/
    Capabilities/Backpressure/
    Capabilities/RateLimit/
    Capabilities/Idempotency/
    Flows/ApplyRetryPolicy/
    Flows/ApplyTimeout/
    Flows/ApplyCircuitBreaker/
    Flows/ApplyBulkhead/
    Flows/ApplyFallback/
    Flows/ApplyBackpressure/
    Flows/CheckIdempotency/
    Foundation/
      RetryPolicy.php
      TimeoutPolicy.php
      CircuitBreakerPolicy.php
      BulkheadPolicy.php
      FallbackPolicy.php
      BackpressurePolicy.php
      RateLimitPolicy.php
      IdempotencyKey.php
      ReliabilityException.php
```

## Tests

```text
- retry retries only configured exceptions
- retry respects max attempts
- timeout stops slow operation
- circuit opens after threshold
- circuit half-opens after cooldown
- circuit closes on success in half-open
- bulkhead limits concurrent operations
- fallback returns fallback value on failure
- idempotency prevents duplicate operation
- rate limiter rejects over limit
- adaptive limiter reacts to latency if implemented
```

---

# 14. V4-10 — Messaging & Consistency

## V3 link

V3 created models:

```text
CapacityModel
ConsistencyModel
MessagingModel
```

V4 must connect models to runtime behavior.

## Features

```text
TransactionalOutbox
OutboxRelay
Inbox
Projection
ReadModel
EventEnvelope
MessageEnvelope
Consumer
Subscriber
RetryPolicy (links to V4-09)
DeadLetterQueue (links to V4-08)
```

## Transactional Outbox

```text
Database transaction
+ state change
+ outbox record
+ relay worker (links to V4-08)
+ retry (links to V4-09)
+ dead letter (links to V4-08)
+ idempotency (links to V4-09)
```

## Shape

```text
components/Operations/Messaging/
  System/
    PublicSurface/Messaging.php
    Capabilities/Outbox/TransactionalOutbox/
    Capabilities/Outbox/OutboxRelay/
    Capabilities/Inbox/InboxProcessor/
    Capabilities/Envelopes/EventEnvelope/
    Capabilities/Envelopes/MessageEnvelope/
    Capabilities/Consumers/MessageConsumer/
    Capabilities/Projections/ProjectionHandler/
    Flows/PublishEvent/
    Flows/ConsumeMessage/
    Flows/RelayOutbox/
    Flows/UpdateProjection/
    Foundation/
      Event.php
      Message.php
      Envelope.php
      MessagingException.php
```

## Tests

```text
- outbox write happens in same transaction
- relay publishes pending message
- failed publish retries
- max retries moves to DLQ
- consumer inbox prevents duplicate processing
- projection updates read model
- event envelope records metadata
- message envelope serializes/deserializes
```

---

# 15. V4-11 — Observability & Telemetry

## Inspiration

OpenTelemetry, Jaeger, Honeycomb.

## Features

```text
CorrelationId
RequestId
TraceId
Span
Metrics
Logs
Events
OpenTelemetry exporter
Redaction            — already partial in Security/Redaction
Audit trail
```

## Shape

```text
components/Operations/Observability/
  System/
    PublicSurface/Observability.php
    Capabilities/Correlation/
    Capabilities/Tracing/
    Capabilities/Metrics/
    Capabilities/Logging/
    Capabilities/Redaction/        — may reuse Security/Redaction
    Capabilities/Audit/
    Flows/CreateCorrelationId/
    Flows/StartSpan/
    Flows/EndSpan/
    Flows/RecordMetric/
    Flows/WriteAuditEvent/
    Foundation/
      CorrelationContext.php
      TraceSpan.php
      MetricPoint.php
      AuditEvent.php
      ObservabilityException.php
```

## Requirements

```text
- every request has correlation ID
- every job has correlation ID
- trace context propagates through queue/outbox if possible
- sensitive data redacted
- audit event records actor/action/resource/time
```

## Tests

```text
- correlation ID created per request
- correlation ID propagated through middleware
- redaction removes secrets from logs
- audit event recorded with actor/action/resource/time
- trace span starts and stops
- metric point recorded
- OpenTelemetry exporter format correct if implemented
```

---

# 16. V4-12 — Security & Policy Runtime

## Features

```text
RequestSigning
DataProtection       — already exists in Security/DataProtection
Secrets              — already exists in Security/Secrets
Redaction            — already exists in Security/Redaction
Audit                — links to V4-11
PrivacyExport        — already exists in Security/Privacy
RetentionPolicy      — already exists in Security/Privacy
PolicyEngine
FeatureFlags         — already exists in Application/FeatureFlags
```

## Policy-as-code

Do not start with heavy OPA/Rego core.

Start with:

```text
components/Security/Policy/
  System/
    Capabilities/EvaluatePolicy/
    Capabilities/PolicyDecision/
    Capabilities/PolicyContext/
    Flows/Allow/
    Flows/Deny/
    Foundation/
      PolicyRule.php
      PolicyEffect.php
      PolicyException.php
```

Later:

```text
components/Security/Policy/Opa/
  EvaluateRegoPolicy/
```

## Feature Flags (enhance existing)

```text
Application/FeatureFlags/ — already has basic support
Enhance with:
  IsFeatureEnabled
  EnableFeatureForUser
  EnableFeatureForPercentage
  EnableFeatureForEnvironment
  ResolveFeatureDecision
```

## Tests

```text
- signed request verifies
- bad signature rejected
- secret value redacted
- policy allow/deny works
- feature enabled by env
- feature enabled by user
- percentage rollout deterministic
- policy context carries request metadata
```

---

# 17. V4-13 — System Design Runtime Kit

## Goal

V3 SystemDesignKit must stop being only an analysis kit.

V4 should map:

```text
CapacityModel     -> runtime configuration recommendations
ConsistencyModel  -> outbox/projection/read model decisions
MessagingModel    -> queue/broker/retry/DLQ decisions
```

## Features

```text
RecommendRateLimitFromCapacity
RecommendQueueWorkersFromCapacity
RecommendCacheStrategy
RecommendStorageStrategy
RecommendConsistencyPattern
RecommendMessagingPattern
GenerateArchitectureReport
```

## Shape

```text
components/SystemDesign/  — already exists from V3
Enhance with runtime recommendations:
  Capabilities/RuntimeRecommendations/
    RecommendRateLimit/
    RecommendQueueWorkers/
    RecommendCacheStrategy/
    RecommendStorageStrategy/
    RecommendConsistencyPattern/
    RecommendMessagingPattern/
  Flows/GenerateArchitectureReport/
```

## Tests

```text
- capacity model recommends queue workers for high write load
- high read ratio recommends cache/read model
- eventual consistency model recommends outbox/projection
- high fanout recommends async messaging
- architecture report contains actionable recommendations
```

---

# 18. V4-14 — Runtime Doctor & Control Plane

## Doctor CLI

```bash
php avax doctor
php avax doctor --runtime=reactphp
php avax doctor --runtime=roadrunner
php avax doctor --runtime=swoole
```

## Checks

```text
autoload
PHP version
extensions
Composer deps
PHPStan baseline
test suite
namespace drift
public surface
runtime leaks
request scope
static leaks
memory growth
Redis optional extension
Swoole extension
RoadRunner binary
ReactPHP deps
```

## Shape

```text
framework/System/Capabilities/Doctor/  — see V4-04
Extended with runtime-specific checks.
```

## Control Plane minimal

```text
components/Operations/ControlPlane/
  System/
    PublicSurface/ControlPlane.php
    Capabilities/Health/
    Capabilities/RuntimeStatus/
    Capabilities/QueueStatus/
    Capabilities/CacheStatus/
    Capabilities/StorageStatus/
    Capabilities/FeatureFlags/
    Capabilities/FailedJobs/
    Flows/CheckHealth/
    Flows/GetRuntimeStatus/
    Flows/GetQueueStatus/
```

No full UI initially. CLI first.

## Tests

```text
- doctor runs and reports
- doctor exits non-zero on integrity failure
- health endpoint returns status
- queue status shows pending/failed counts
- cache status shows memory usage
- storage status shows disk usage
- feature flags shows current state
- failed jobs shows recent failures
```

---

# 19. V4-15 — Reference Applications

These are not demos for decoration. They are proof that the framework works.

## Required reference apps

| #  | App                        | Proves                                   |
|----|----------------------------|------------------------------------------|
| 1  | Hello HTTP                 | V4-01 App API, basic route               |
| 2  | JSON API                   | V4-01 response normalization             |
| 3  | SecureRequest registration | V4-01 SecureRequest autowiring           |
| 4  | URL Shortener              | V4-01 + V4-07 Database + V4-05 Schema    |
| 5  | Parking Lot                | V4-07 Database + V4-13 Capacity modeling |
| 6  | Webhook Receiver           | V4-08 Queue + V4-10 Outbox               |
| 7  | File Upload / Storage      | V4-06 Storage platform                   |
| 8  | Queue Worker Demo          | V4-08 Queue & Worker                     |
| 9  | Transactional Outbox Demo  | V4-10 Messaging & Consistency            |
| 10 | Notification System        | V4-09 Reliability + V4-11 Observability  |
| 11 | Chat / Messaging           | V4-02 ReactPHP runtime + realtime        |
| 12 | Mini Spotify-like Catalog  | V4-07 Database + V4-05 Schema + V4-06    |
| 13 | Mini YouTube-like Pipeline | V4-08 Queue + V4-06 Storage + V4-09      |

## Each reference app must prove

```text
routes
controllers
SecureRequest
DataTransfer
Storage
Database
Queue if needed
Observability
tests
docs
```

## Location

```text
examples/
  hello-http/
  json-api/
  secure-request-registration/
  url-shortener/
  parking-lot/
  webhook-receiver/
  file-upload/
  queue-worker-demo/
  outbox-demo/
  notification-system/
  chat-app/
  catalog-app/
  upload-pipeline/
```

---

# 20. V4-16 — Benchmarks & Performance Proof

## Needed

```text
benchmarks/http-kernel
benchmarks/router
benchmarks/container
benchmarks/data-transfer
benchmarks/secure-request
benchmarks/react-runtime
benchmarks/parallelism
benchmarks/storage
benchmarks/database
```

## Rules

```text
benchmarks are evidence, not marketing
do not compare unfairly
measure cold and warm
measure memory
measure request throughput
measure latency percentiles if possible
```

## Outputs

```text
EVIDENCE/benchmarks/http-kernel.md
EVIDENCE/benchmarks/data-transfer.md
EVIDENCE/benchmarks/runtime-reactphp.md
EVIDENCE/benchmarks/database.md
EVIDENCE/benchmarks/storage.md
```

---

# 21. V4-17 — Runtime Adapters Roadmap

## ReactPHP (FIRST)

```text
Status: first real async runtime
Reason: userland, no extension, proven PHP ecosystem
Dependency: react/http, react/event-loop, react/promise
Gate: V4-01 must be complete first
```

## RoadRunner (SECOND)

```text
Status: after V4-03 Warm Worker Safety
Reason: persistent workers need reset proof
Gate: V4-03 must be GREEN before this starts
```

## Swoole/OpenSwoole (THIRD)

```text
Status: after RoadRunner or parallel with it only if safety layer is ready
Reason: coroutine context and static state risk
Gate: V4-03 must be GREEN, V4-17 RoadRunner complete
```

## FrankenPHP

```text
Status: later runtime adapter
Reason: good Octane-style inspiration, but not first
Gate: V4-03 must be GREEN
```

## Workerman / Amp / Revolt

```text
Status: optional adapters
Reason: useful but not V4 blocker
Gate: none, but must not block V4-01 or V4-02
```

---

# 22. V4 Labs / Not Core Yet

These are useful, but not V4 blockers. They may exist in `labs/` but must not be promoted to `components/` during V4.

```text
CRDT
WASM plugins
custom distributed database
custom storage engine
lock-free concurrent structures
advanced consensus
media transcoding engine
HyperLogLog production usage
CountMinSketch production usage
BloomFilter public core promotion
Protocol Buffers production codec
OPA/Rego adapter
Workerman runtime
Amp/Revolt runtime
FrankenPHP runtime (before V4-03)
Swoole runtime (before V4-03)
RoadRunner runtime (before V4-03)
Admin UI
Filament/BladeOne integration
Plugin Capability Security (Section 29.13 — stretch goal)
Template Compiler / View Doctor (Section 29.14 — stretch goal)
labs/IntelligenceKit (experimental — Section 29 concepts are design, not code yet)
```

**From old plan anti-goals (Section 29.2):**

```text
V4 must not:
- Auto-restore code without review
- Generate production code without tests
- Treat old code as automatically valid
- Treat new skeleton code as progress
- Unlock V2/V3/V4 while previous gates are red
- Hide validation failures
- Mark UNKNOWN as GREEN
- Replace real infrastructure
- Become a generic AI agent framework
- Become a dashboard-only observability toy
- Become custom Kafka/Redis/CDN/DB/FFmpeg
- Promote labs code without proof
```

---

# 23. V4 Stage Order & Dependencies

**Note:** This section is superseded by Section 27.10 (Updated Stage Order with Acceptance Gates). The full stage order
with acceptance gates is defined there.

```text
V4-00  Integrity Lock & Stage Definition     — no deps
       |
V4-01  Runtime App Layer                     — V4-00
       |   (+ Zero-Dependency Feeling, Error Handling, Health Check baseline)
       |
V4-02  Reactive HTTP Runtime (ReactPHP)      — V4-01
       |   (+ Serve Modes: dev, react, smoke)
       |
V4-03  Warm Worker Safety                    — V4-01 (parallel ok with V4-02)
       |   (+ Warm State Contract, MemoryGuard — first-class features)
       |
       +---- V4-04  Developer Experience      — V4-01 (parallel ok)
       |          (+ Route Cache, Configuration as Code)
       +---- V4-05  Data Platform Product     — V4-01 (parallel ok)
       +---- V4-06  Storage Platform          — V4-01 (parallel ok)
       +---- V4-07  Database Muscle           — V4-01 (parallel ok)
       |          (+ Connection Pooling — first-class feature)
       |
V4-08  Queue & Worker Runtime                — V4-07 (needs DB driver)
       |
V4-09  Reliability Engine                    — V4-01 (parallel ok)
       |
V4-10  Messaging & Consistency               — V4-08 + V4-09 + V4-07
       |   (+ Service-to-Service basics)
       |
V4-11  Observability & Telemetry             — V4-01 (parallel ok)
       |
V4-12  Security & Policy Runtime             — V4-01 (parallel ok)
       |   (+ Service Discovery basics)
       |
V4-13  System Design Runtime Kit            — V4-10 + V4-09 + V4-08
       |
V4-14  Runtime Doctor & Control Plane       — V4-04 + V4-08 + V4-11
       |   (+ Health Check endpoints: /health, /health/live, /health/ready)
       |
V4-15  Reference Applications               — V4-01 through V4-14 (incremental)
       |
V4-16  Benchmarks & Performance Proof       — V4-15
       |   (+ Cold/Warm measurements required)
       |
V4-17  Optional Runtime Adapters            — V4-03 (hard gate) + V4-14
```

Critical path:

```text
V4-00 -> V4-01 -> V4-02 -> V4-03 -> V4-17
                |              |
                +-> V4-07 -> V4-08 -> V4-10 -> V4-13
                |                    |
                +-> V4-09 -----------+
                |
                +-> V4-11 -> V4-14 -> V4-17
                |          |
                +-> V4-12 -+
                |
                +-> V4-04 -> V4-14
                |
                +-> V4-05
                |
                +-> V4-06
                           |
                           +-> V4-15 -> V4-16
```

---

# 24. Global Acceptance Criteria

V4 cannot be called complete until ALL of these are true:

```text
 1. AvaX can run minimal HTTP app.
 2. App API is small and stable (Zero-Dependency Feeling — no internals leaked).
 3. ReactPHP runtime works.
 4. Serve Modes matrix: dev, react, smoke all work.
 5. SecureRequest works through controller invocation.
 6. Response normalization works.
 7. Middleware works.
 8. Error Handling: global exception handler works, production hides stack traces.
 9. Health Check baseline: /health exists.
10. Health Check full: /health, /health/live, /health/ready serve correctly.
11. DataTransfer schema/OpenAPI works.
12. Queue worker works.
13. Transactional Outbox works.
14. Storage LocalDisk works.
15. Database migrations/query/transaction basic flow works.
16. Connection Pooling works (acquire/release/reset/exhaustion).
17. Reliability policies work.
18. Observability trace/correlation/redaction works.
19. Runtime Doctor works.
20. At least 3 reference apps pass.
21. Full PHPUnit passes (0 skipped, 0 failures).
22. PHPStan clean.
23. All governance checks pass.
24. Runtime-specific APIs do not leak into core public API.
25. V4-03 Warm Worker Safety is GREEN before any RoadRunner/Swoole claim.
26. Warm State Contract tests pass (no state leak between requests).
27. MemoryGuard is active and triggers worker recycle on threshold.
28. Cold AND warm benchmarks measured (no performance claim without both).
29. Route cache compile/write/load/invalidate works.
30. Configuration as Code: config/ directory structure exists.
```

---

# 25. What V4 Is NOT

```text
V4 is NOT a rewrite.
V4 is NOT a Laravel clone.
V4 is NOT a Symfony replacement.
V4 is NOT "add everything".
V4 is NOT a micro-framework.
V4 is NOT a platform-as-a-service.
V4 is NOT an admin panel framework.
V4 is NOT a CMS.
```

V4 IS:

```text
V4 IS a runnable framework with a tiny public API.
V4 IS a framework that can build production-grade applications.
V4 IS runtime-agnostic at core, runtime-specific at adapters.
V4 IS evidence-driven — every claim must have proof.
V4 IS governed by stage lock — no skipping.
V4 IS inspired by great frameworks but owns its own architecture.
```

---

# 26. File Update Plan

This V4 stage lock must update:

| File                    | Action                                   |
|-------------------------|------------------------------------------|
| `CURRENT_TRUTH.md`      | Add V4 stage status, validation baseline |
| `TODO.md`               | Add V4 roadmap sections                  |
| `EVIDENCE/EXECUTION.md` | Add V4 stage lock, V4-00 as active       |
| `AGENTS.md`             | Add V4 to stage lock section             |
| This file               | Created — V4 master plan                 |

---

# 27. Framework Maturity Gates

Ovo nisu kozmetičke dopune. Ovo su **framework maturity gates** — svaki mora biti eksplicitno zadovoljen pre nego što V4
može biti nazvan production-ready.

## 27.1 Warm Application Model / Warm State Contract

Inspiracija: Laravel Octane, RoadRunner persistent workers, Swoole coroutine server.

**Problem:** U PHP-FPM svaki request je čist. U warm worker runtime-u (RoadRunner, Swoole, FrankenPHP, ReactPHP
long-lived), state između requesta može da leakuje.

**Warm State Contract** definiše eksplicitno šta sme da ostane toplo, a šta mora da se resetuje:

### Allowed Warm (staje toplo između requesta)

```text
Container compiled definitions / resolved singletons that are stateless
Route table / compiled route cache
Configuration (immutable after boot)
Logger instances (without request context)
Connection pool objects (not individual connections with state)
Event listener registry (without attached request data)
Middleware pipeline (without request-scoped data)
Template engine compiled templates
FeatureFlags loaded definitions
Policy engine loaded rules
```

### Must Reset (mora da se resetuje posle svakog requesta)

```text
Current request / response objects
Current user / authentication context
Correlation ID / trace context
Session data
Scoped container instances
Query builder with bound parameters
Database transaction state
Cache tags that include request-scoped keys
Any static state that was configured per-request
Flash data
Validation error bags
Rate limiter request counters (per-request scope)
```

### Required shape

```text
framework/System/Runtime/WarmApplication/
  WarmStateContract.php           — explicit interface
  AllowedWarmState.php            — registry of what stays warm
  MustResetState.php              — registry of what must reset
  BootWarmApplication.php
  HandleWarmRequest.php
  ResetWarmRequestState.php       — executes reset between requests
  FlushScopedInstances.php
  DetectLeakedState.php
  DetectMemoryGrowth.php
  ReloadWorker.php
```

### Tests

```text
- WarmStateContract lists all allowed warm items
- MustResetState lists all required reset items
- ResetWarmRequestState resets all must-reset items
- DetectLeakedState catches a configured static leak
- Current user from request 1 is not visible in request 2
- Correlation ID from request 1 is not visible in request 2
- Container scoped instance is different between requests
- Memory growth triggers worker reload when threshold exceeded
```

### Gate

**No runtime adapter (ReactPHP, RoadRunner, Swoole, FrankenPHP) may serve a second request without passing the Warm
State Contract tests.**

## 27.2 Connection Pooling

Inspiracija: Hyperf connection pool, Swoole coroutine pool, Go database/sql pool.

**Problem:** U long-lived runtime, kreiranje nove DB/Redis/cache konekcije za svaki request je preskupo. Ali deljenje
konekcija bez pool management-a je opasno (transaction state leak, stale connection, wrong user data).

### Component shape

```text
components/DataStack/Database/
  System/
    Capabilities/ConnectionPool/
      CreateConnectionPool.php
      AcquireConnectionFromPool.php
      ReleaseConnectionToPool.php
      CloseConnectionPool.php
      HealthCheckConnectionPool.php
      ResizeConnectionPool.php
    Foundation/
      ConnectionPool.php
      ConnectionPoolConfig.php
      ConnectionLease.php
      PoolExhaustedException.php
      StaleConnectionException.php
```

### Pool config

```text
min_connections: 2
max_connections: 20
idle_timeout: 30s
max_lifetime: 3600s
health_check_interval: 10s
acquire_timeout: 5s
reset_on_release: true           — must clear transaction state
health_check_on_acquire: true    — must verify connection is alive
```

### Required behavior

```text
- acquire returns healthy connection or waits until timeout
- release resets connection state (transaction, variables, locks)
- pool creates min_connections at boot
- pool creates up to max_connections under load
- pool closes idle connections after idle_timeout
- pool closes connections after max_lifetime
- health check replaces dead connections
- PoolExhaustedException when all connections busy and max reached
- Connection state must never leak between requests
```

### Tests

```text
- pool creates min connections at boot
- acquire returns connection from pool
- release returns connection to pool
- release resets transaction state
- pool grows under load up to max
- PoolExhaustedException thrown when max reached
- health check replaces dead connection
- idle connections closed after timeout
- max lifetime connection closed
- concurrent acquire/release is safe
```

## 27.3 Compiled Route Cache / Route Precomputation

Inspiracija: Symfony compiled container, Laravel route cache, Framework X route compilation.

**Problem:** Registracija ruta na svakom boot-u je spora. U cold start to je vidljivo. U warm state to je manje vidljivo
ali svejedno bespotreban rad.

### Strategy

```text
V4-01: Route registration is in-memory on boot (simple, correct)
V4-02: Route table is built once and kept warm (no recompilation)
V4-04: Route cache can be written to disk for faster cold start
V4-16: Route compilation benchmark proves cold/warm difference
```

### Route cache shape

```text
framework/System/Capabilities/RouteCompilation/
  CompileRouteTable.php
  WriteCompiledRoutes.php
  LoadCompiledRoutes.php
  InvalidateCompiledRoutes.php
  VerifyCompiledRouteIntegrity.php
```

### Compiled route data

```text
- HTTP method + path -> handler mapping
- middleware chain per route
- parameter extraction rules
- route group prefix resolution
- route name -> path reverse mapping
- compilation timestamp
- framework version hash (invalidate on upgrade)
```

### Tests

```text
- compiled routes match runtime registered routes
- loading compiled routes skips registration step
- invalidate clears stale compiled routes
- integrity check detects version mismatch
- route compilation produces correct handler mapping
- reverse routing (path generation) works from compiled data
```

## 27.4 Built-in Web Server / Serve Modes Matrix

Inspiracija: Framework X built-in server, Laravel `php artisan serve`, Symfony local server, Flight PHP.

AvaX mora imati eksplicitan **serve mode** matrix:

| Mode           | Command                               | Runtime             | Use Case                                        |
|----------------|---------------------------------------|---------------------|-------------------------------------------------|
| **dev**        | `php avax serve`                      | PHP built-in server | local development, auto-reload if possible      |
| **react**      | `php avax serve --runtime=reactphp`   | ReactPHP            | async HTTP, production-ready without extensions |
| **fpm**        | `php avax serve --runtime=fpm`        | PHP-FPM             | traditional deployment (nginx/Apache + FPM)     |
| **smoke**      | `php avax serve --smoke`              | minimal sync        | quick validation, no dependencies               |
| **roadrunner** | `php avax serve --runtime=roadrunner` | RoadRunner          | persistent workers (after V4-03)                |
| **swoole**     | `php avax serve --runtime=swoole`     | Swoole              | coroutine server (after V4-03)                  |
| **frankenphp** | `php avax serve --runtime=frankenphp` | FrankenPHP          | modern app server (after V4-03)                 |

### Serve mode requirements

```text
- auto-detect available runtime if no --runtime flag
- --host and --port work for all modes
- --workers for worker-based runtimes (RoadRunner, Swoole)
- --max-requests for worker recycling
- --memory-limit for worker memory guard
- graceful shutdown on SIGTERM/SIGINT for all modes
- health check endpoint available in all production modes
```

### Tests

```text
- serve starts in dev mode
- serve starts in smoke mode
- serve starts in react mode
- serve auto-detects runtime
- serve with invalid --runtime fails with useful message
- graceful shutdown stops server
- health endpoint responds in production mode
```

## 27.5 MemoryGuard — Memory-Aware Request Handling

Inspiracija: Laravel Octane memory limit, RoadRunner max-memory, Swoole worker recycling.

**Problem:** U long-lived worker-u, memory leak (čak i benigni) akumulira dok worker ne crashuje. MemoryGuard mora da
detektuje i reaguje pre crash-a.

### Shape

```text
framework/System/Runtime/MemoryGuard/
  MonitorWorkerMemory.php
  CheckMemoryThreshold.php
  TriggerWorkerRecall.php
  RecordMemorySnapshot.php
  CalculateMemoryGrowthRate.php
```

### Configuration

```text
memory_limit: 128M               — soft limit (trigger warning)
memory_max: 256M                 — hard limit (force worker recycle)
max_requests: 1000               — recycle after N requests regardless of memory
check_interval: 50               — check memory every N requests
recycle_graceful: true           — finish current request before recycle
```

### Required behavior

```text
- track memory after each request
- warn when soft limit reached
- force recycle when hard limit reached
- force recycle when max_requests reached
- record memory snapshots for diagnostics
- calculate memory growth rate over time
- do NOT interrupt mid-request (graceful only)
- log recycle reason (memory vs request count)
```

### Tests

```text
- MemoryGuard tracks memory per request
- soft limit triggers warning log
- hard limit triggers worker recycle
- max_requests triggers worker recycle
- memory snapshot recorded
- growth rate calculated over N requests
- graceful recycle finishes current request
- recycle reason logged correctly
```

### Integration with V4-03

MemoryGuard is a **first-class feature of V4-03 Warm Worker Safety**, not a separate stage. It must be implemented and
tested as part of V4-03.

## 27.6 Cold/Warm Performance Measurement

Inspiracija: Laravel Octane benchmarks, RoadRunner performance claims, Framework X throughput.

Svaki performance claim u V4 mora meriti **i cold i warm**:

### Benchmark requirements

```text
Cold start: time from `php avax serve` to first request response
Warm start: time from request N to request N+1 (after boot)
Cold throughput: requests/second starting from cold
Warm throughput: requests/second in steady warm state
Cold memory: memory after boot, before first request
Warm memory: memory after N requests (steady state)
Memory growth: delta between request 1 and request N
```

### Required outputs

```text
EVIDENCE/benchmarks/cold-vs-warm.md
EVIDENCE/benchmarks/http-kernel-cold.md
EVIDENCE/benchmarks/http-kernel-warm.md
EVIDENCE/benchmarks/router-cold.md
EVIDENCE/benchmarks/router-warm.md
EVIDENCE/benchmarks/runtime-reactphp-throughput.md
EVIDENCE/benchmarks/memory-growth.md
```

### Measurement rules

```text
- measure on CI or controlled environment
- report median, p50, p95, p99 latency
- report min/max memory
- report request count for steady state
- do not cherry-pick best run
- report at least 3 runs, use median
```

### Gate

**No performance claim is valid without both cold and warm measurements.** A claim of "X requests/second" without
specifying cold or warm is incomplete.

## 27.7 Zero-Dependency Feeling — V4-01 Design Principle

Inspiracija: Flight PHP zero-dependency core, Framework X minimal entry point, Go simplicity.

**Princip:** `Avax::create()` i `App` API moraju izgledati kao da nemaju dependency-je, čak i ako internals koriste
Router, Container, SecureRequest, Middleware, itd.

### What this means

```text
User sees:
  $app = Avax::create();
  $app->get('/', fn () => 'Hello');
  $app->run();

User does NOT see:
  new Container()
  new Router()
  new MiddlewarePipeline()
  new RequestFactory()
  new ResponseFactory()
  new ExceptionHandler()
```

### Design rules for V4-01

```text
1. Avax::create() wires all internal dependencies automatically
2. App API methods return fluent interfaces, not internal objects
3. Route registration does not expose Router internals
4. Middleware registration does not expose pipeline internals
5. Response normalization happens automatically, not by user request
6. SecureRequest autowiring is transparent to the user
7. Container is used internally, not exposed to the user
8. All internal classes stay behind framework/System boundaries
```

### Anti-patterns (must NOT happen)

```text
$app->getRouter()->addRoute(...)          — leaks Router
$app->getContainer()->make(...)           — leaks Container
$app->getMiddlewarePipeline()->add(...)   — leaks pipeline
$app->setRequestFactory(...)              — unnecessary configuration
$app->bind(Something::class, ...)         — leaks Container API
```

### Tests

```text
- Avax::create() returns App with all defaults
- App->get() registers route without exposing Router
- App->use() registers middleware without exposing pipeline
- closure route executes and returns response
- class route executes with Container autowiring
- SecureRequest parameter is autowired transparently
- no internal classes visible in App public methods
- architecture test: App depends only on framework/System
```

## 27.8 Microservices-Ready Runtime Toolkit

Inspiracija: Hyperf microservice model, Spring Cloud, Go kit, Istio sidecar patterns.

V4 mora imati **runtime toolkit za service-to-service communication** — ne full service mesh, ali dovoljno da AvaX app
može da bude dobar citizen u microservices okruženju.

### Components

```text
components/Runtime/ServiceCommunication/
  System/
    PublicSurface/ServiceCommunication.php
    Capabilities/ServiceDiscovery/
      RegisterService.php
      ResolveService.php
      DeregisterService.php
      HealthCheckService.php
    Capabilities/ServiceToService/
      CallService.php
      CallServiceWithRetry.php
      CallServiceWithTimeout.php
      CallServiceWithCircuitBreaker.php
      PropagateCorrelationContext.php
    Capabilities/ConfigCenter/
      FetchRemoteConfig.php
      CacheRemoteConfig.php
      WatchConfigChanges.php
    Foundation/
      ServiceEndpoint.php
      ServiceResolutionFailed.php
      ServiceCallException.php
      ConfigCenterException.php
```

### Service discovery (initial — file/DB based)

```text
- register service with name, host, port, metadata
- resolve service by name -> endpoint(s)
- deregister service on shutdown
- health check endpoint per service
- simple file-based or DB-based registry initially
- pluggable discovery adapters (Consul, etcd, K8s) for future
```

### Service-to-service call

```text
- HTTP call with Avax HTTP client
- automatic correlation ID propagation
- retry policy (links to V4-09)
- timeout (links to V4-09)
- circuit breaker (links to V4-09)
- request signing for authenticated services
```

### Config center (optional — future)

```text
- fetch configuration from remote source
- cache with TTL
- watch for changes and hot-reload
- fallback to local config if center unavailable
```

### Tests

```text
- register service resolves by name
- deregister service no longer resolves
- call service makes HTTP request
- correlation ID propagates through service call
- retry policy applies on failure
- timeout stops slow service call
- circuit breaker opens after threshold
- config center fetch returns cached value
- config center fallback to local config on failure
- config watch detects and hot-reloads changed values
- service health check returns healthy/unhealthy status
- multiple endpoints for same service return all (load balancing ready)
```

### Stage assignment

```text
ServiceDiscovery    — V4-12 (Security & Policy Runtime) or V4-14 (Control Plane)
ServiceToService    — V4-10 (Messaging & Consistency) linkage
ConfigCenter        — V4-04 (Developer Experience) or future stage
```

This is a **V4 stretch goal**, not a V4-01 blocker. Service-to-service basics should exist by V4-10. Full service
discovery by V4-14.

## 27.9 Health Check Endpoint

Inspiracija: Kubernetes probes, Spring Boot Actuator, Laravel health checks, AWS ALB health checks.

**Problem:** Without a health check endpoint, orchestrators (K8s, Docker Compose, load balancers, RoadRunner) cannot
know if the application is alive, ready, or degraded.

### Endpoints

```text
GET /health
  — aggregate health status
  — returns 200 when healthy, 503 when degraded
  — includes component statuses

GET /health/live
  — liveness probe
  — returns 200 if the process is alive
  — no dependency checks (fast, always responds if process is running)
  — used by orchestrators to decide if process needs restart

GET /health/ready
  — readiness probe
  — returns 200 if the app can accept traffic
  — checks database connection, cache, queue, storage
  — used by load balancers to decide if instance should receive traffic
```

### Health check shape

```text
framework/System/Capabilities/HealthCheck/
  CheckApplicationHealth.php
  CheckLiveness.php
  CheckReadiness.php
  AggregateHealthStatus.php
  RegisterHealthCheckProvider.php
  HealthCheckProvider.php            — interface for component health
```

### Provider interface

```text
Every component that can be "unhealthy" must implement:

HealthCheckProvider:
  name(): string                     — "database", "cache", "queue", "storage"
  check(): HealthCheckResult         — ok/degraded/failing
  details(): array<string, mixed>    — diagnostic details
```

### Default providers

```text
DatabaseHealth      — can execute SELECT 1
CacheHealth         — can get/set a key
QueueHealth         — can push/pop a test job (optional)
StorageHealth       — can write/read a temp file
MemoryHealth        — memory usage within limits
DiskSpaceHealth     — disk space available (for storage)
```

### Response format

```json
{
  "status": "healthy",
  "timestamp": "2026-05-09T12:00:00Z",
  "uptime_seconds": 3600,
  "checks": {
    "database": {
      "status": "ok",
      "latency_ms": 2
    },
    "cache": {
      "status": "ok",
      "latency_ms": 1
    },
    "storage": {
      "status": "ok",
      "disk_free_gb": 50
    },
    "memory": {
      "status": "ok",
      "usage_mb": 45,
      "limit_mb": 256
    }
  }
}
```

### Tests

```text
- /health/live returns 200 when process is alive
- /health/ready returns 200 when all dependencies healthy
- /health/ready returns 503 when database is down
- /health/ready returns 503 when cache is down
- /health aggregate includes all component statuses
- HealthCheckProvider interface is implementable by components
- DatabaseHealth executes SELECT 1 and reports result
- MemoryHealth reports usage within limits
- health check response is valid JSON
- health endpoint does not expose secrets in details
```

### Gate

**Every production mode (react, roadrunner, swoole, frankenphp, fpm) must serve /health, /health/live, /health/ready.**
This is required by V4-14 (Runtime Doctor & Control Plane) at minimum.

## 27.10 Updated Stage Order with Acceptance Gates

Ovo ažurira sekciju 3 (V4 Core Architecture Shape) i sekciju 23 (V4 Stage Order & Dependencies).

### Updated V4 stage list

```text
V4-00  Integrity Lock & Stage Definition
V4-01  Runtime App Layer + Zero-Dependency Feeling + Error Handling + Health Check baseline
V4-02  Reactive HTTP Runtime (ReactPHP) + Serve Modes
V4-03  Warm Worker Safety + Warm State Contract + MemoryGuard
V4-04  Developer Experience + Route Cache + Configuration as Code
V4-05  Data Platform Productization
V4-06  Storage Platform
V4-07  Database Muscle + Connection Pooling
V4-08  Queue & Worker Runtime
V4-09  Reliability Engine
V4-10  Messaging & Consistency + Service-to-Service basics
V4-11  Observability & Telemetry
V4-12  Security & Policy Runtime + Service Discovery basics
V4-13  System Design Runtime Kit
V4-14  Runtime Doctor & Control Plane + Health Check endpoints
V4-15  Reference Applications
V4-16  Benchmarks & Production Proof + Cold/Warm measurements
V4-17  Optional Runtime Adapters (RoadRunner/Swoole/FrankenPHP)
```

### Stage acceptance gates

Each stage must pass its gate before the next dependent stage can start:

**V4-00 Gate:**

```text
- full test suite GREEN (0 skipped, 0 failures)
- PHPStan clean
- all governance checks pass
- stage lock active
- V4 master plan written
```

**V4-01 Gate:**

```text
- Avax::create() returns App
- App->get/post/put/patch/delete/any register routes
- App->use registers middleware
- App->run serves HTTP response (smoke mode)
- closure route executes
- class route executes with Container autowiring
- SecureRequest autowires into controller
- string return -> text response
- array/DataObject return -> JSON response
- exception maps to error response
- global middleware runs
- route middleware runs
- architecture test: App public surface is thin
- Zero-Dependency Feeling: no internal classes leaked
- Error Handling: global exception handler works
- Health Check baseline: /health endpoint exists
```

**V4-02 Gate:**

```text
- ReactPHP runtime can start
- ReactPHP serves HTTP request
- React request converts to AvaX request
- AvaX response converts to React response
- middleware works through ReactPHP
- SecureRequest works through ReactPHP
- graceful shutdown stops event loop
- serve mode matrix: dev, react, smoke modes work
- runtime-specific code stays behind Runtime/ReactPhp
```

**V4-03 Gate:**

```text
- WarmStateContract defines allowed warm vs must reset
- AllowedWarmState lists stateless singletons, route table, config
- MustResetState lists request, user, correlation, session, scoped
- ResetWarmRequestState resets all must-reset items
- Current user from request 1 NOT visible in request 2
- Correlation ID from request 1 NOT visible in request 2
- Container scoped instance different between requests
- MemoryGuard tracks memory per request
- MemoryGuard soft limit triggers warning
- MemoryGuard hard limit triggers worker recycle
- MemoryGuard max_requests triggers worker recycle
- DetectLeakedState catches configured static leak
- worker reload triggers correctly
- No runtime adapter serves second request without passing these tests
```

**V4-04 Gate:**

```text
- make:secure-request generates correct file
- make:data-object generates correct file
- make:controller generates correct file
- make:flow generates correct file
- make:migration generates correct file
- make:test generates correct file
- generated code follows how-to rules
- doctor runs and reports useful output
- doctor exits non-zero on integrity failure
- Route cache: compile, write, load, invalidate works
- Configuration as Code: config/ directory structure exists
- config:publish command works
```

**V4-05 Gate:**

```text
- DataObject attributes generate JSON schema
- Required maps to required field
- StringType maps to string
- Min/Max map to minLength/maxLength or numeric
- RegexPattern maps to pattern
- SecureRequest route generates requestBody schema
- response DataObject generates response schema
- route list generates OpenAPI paths
```

**V4-06 Gate:**

```text
- local disk put/get/exists/delete works
- local disk uses Filesystem through composition
- unknown disk throws DiskNotFound
- temporary URL on local disk throws TemporaryUrlNotSupported
- Filesystem has zero dependency on Storage
```

**V4-07 Gate:**

```text
- migration can create table
- transaction wraps work
- query builder creates safe parameterized query
- schema model can inspect/build schema
- outbox can write event inside transaction
- DataMapper can persist and retrieve entity
- UnitOfWork tracks changes and flushes
- IdentityMap prevents duplicate object loading
- Connection Pool: creates min connections at boot
- Connection Pool: acquire/release works
- Connection Pool: release resets transaction state
- Connection Pool: PoolExhaustedException on max
- Connection Pool: health check replaces dead connection
```

**V4-08 Gate:**

```text
- dispatch job works
- worker runs job
- failed job retries
- max retries moves to dead letter
- worker heartbeat works
- graceful stop works
- memory limit triggers stop
- max jobs limit triggers stop
- memory queue driver works for testing
```

**V4-09 Gate:**

```text
- retry retries only configured exceptions
- retry respects max attempts
- timeout stops slow operation
- circuit opens after threshold
- circuit half-opens after cooldown
- circuit closes on success in half-open
- bulkhead limits concurrent operations
- fallback returns fallback value on failure
- idempotency prevents duplicate operation
- rate limiter rejects over limit
```

**V4-10 Gate:**

```text
- outbox write happens in same transaction
- relay publishes pending message
- failed publish retries
- max retries moves to DLQ
- consumer inbox prevents duplicate processing
- projection updates read model
- event envelope records metadata
- message envelope serializes/deserializes
- Service-to-Service: basic HTTP call with correlation propagation
```

**V4-11 Gate:**

```text
- correlation ID created per request
- correlation ID propagated through middleware
- redaction removes secrets from logs
- audit event recorded with actor/action/resource/time
- trace span starts and stops
- metric point recorded
- OpenTelemetry exporter format correct if implemented
```

**V4-12 Gate:**

```text
- signed request verifies
- bad signature rejected
- secret value redacted from logs
- policy allow/deny works
- feature enabled by env
- feature enabled by user
- percentage rollout deterministic
- policy context carries request metadata
- Service Discovery: register/resolve/deregister works
```

**V4-13 Gate:**

```text
- capacity model recommends queue workers for high write load
- high read ratio recommends cache/read model
- eventual consistency model recommends outbox/projection
- high fanout recommends async messaging
- architecture report contains actionable recommendations
```

**V4-14 Gate:**

```text
- doctor runs and reports
- doctor exits non-zero on integrity failure
- /health returns 200 with aggregate status
- /health/live returns 200 when process alive
- /health/ready returns 200 when dependencies healthy
- /health/ready returns 503 when database down
- queue status shows pending/failed counts
- cache status shows memory usage
- storage status shows disk usage
- feature flags shows current state
- failed jobs shows recent failures
- HealthCheckProvider interface implementable by components
```

**V4-15 Gate:**

```text
- at least 3 reference apps pass all their tests
- each reference app proves: routes, controllers, SecureRequest, DataTransfer
- each reference app has tests, docs
- reference apps cover: HTTP, API, Database, Queue, Storage, Observability
```

**V4-16 Gate:**

```text
- cold start time measured
- warm start time measured
- cold throughput measured (requests/second)
- warm throughput measured (requests/second)
- cold memory measured
- warm memory measured
- memory growth measured
- at least 3 runs, median reported
- p50, p95, p99 latency reported
- benchmark evidence written to EVIDENCE/benchmarks/
```

**V4-17 Gate:**

```text
- V4-03 Warm Worker Safety is GREEN (hard gate)
- RoadRunner runtime adapter works
- Swoole runtime adapter works (if implemented)
- FrankenPHP runtime adapter works (if implemented)
- all adapters pass Warm State Contract tests
- all adapters pass MemoryGuard tests
- all adapters serve /health, /health/live, /health/ready
- runtime-specific code does not leak into core public API
```

### Updated stage dependency graph

```text
V4-00  Integrity Lock & Stage Definition     — no deps
       |
V4-01  Runtime App Layer                     — V4-00
       |   (Zero-Dependency Feeling, Error Handling, Health Check baseline)
       |
V4-02  Reactive HTTP Runtime (ReactPHP)      — V4-01
       |   (Serve Modes: dev, react, smoke)
       |
V4-03  Warm Worker Safety                    — V4-01 (parallel ok with V4-02)
       |   (Warm State Contract, MemoryGuard — first-class features)
       |
       +---- V4-04  Developer Experience      — V4-01 (parallel ok)
       |          (Route Cache, Configuration as Code)
       |
       +---- V4-05  Data Platform Product     — V4-01 (parallel ok)
       |
       +---- V4-06  Storage Platform          — V4-01 (parallel ok)
       |
       +---- V4-07  Database Muscle           — V4-01 (parallel ok)
       |          (Connection Pooling — first-class feature)
       |
V4-08  Queue & Worker Runtime                — V4-07 (needs DB driver)
       |
V4-09  Reliability Engine                    — V4-01 (parallel ok)
       |
V4-10  Messaging & Consistency               — V4-08 + V4-09 + V4-07
       |   (Service-to-Service basics)
       |
V4-11  Observability & Telemetry             — V4-01 (parallel ok)
       |
V4-12  Security & Policy Runtime             — V4-01 (parallel ok)
       |   (Service Discovery basics)
       |
V4-13  System Design Runtime Kit            — V4-10 + V4-09 + V4-08
       |
V4-14  Runtime Doctor & Control Plane       — V4-04 + V4-08 + V4-11
       |   (Health Check endpoints: /health, /health/live, /health/ready)
       |
V4-15  Reference Applications               — V4-01 through V4-14 (incremental)
       |
V4-16  Benchmarks & Performance Proof       — V4-15
       |   (Cold/Warm measurements required)
       |
V4-17  Optional Runtime Adapters            — V4-03 (hard gate) + V4-14
```

### Critical path (updated)

```text
V4-00 -> V4-01 -> V4-02 -> V4-03 -> V4-17
                |              |
                +-> V4-07 -> V4-08 -> V4-10 -> V4-13
                |                    |
                +-> V4-09 -----------+
                |
                +-> V4-11 -> V4-14 -> V4-17
                |          |
                +-> V4-12 -+
                |
                +-> V4-04 -> V4-14
                |
                +-> V4-05
                |
                +-> V4-06
                           |
                           +-> V4-15 -> V4-16
```

Key changes from original:

```text
1. V4-01 now includes Zero-Dependency Feeling, Error Handling, Health Check baseline
2. V4-02 now includes Serve Modes matrix (dev, react, smoke)
3. V4-03 now includes Warm State Contract and MemoryGuard as first-class features
4. V4-04 now includes Route Cache and Configuration as Code
5. V4-07 now includes Connection Pooling as first-class feature
6. V4-10 now includes Service-to-Service basics
7. V4-12 now includes Service Discovery basics
8. V4-14 now includes full Health Check endpoints (/health, /health/live, /health/ready)
9. V4-16 now requires Cold/Warm measurements
10. V4-17 depends on V4-03 (hard gate) AND V4-14 (health checks)
```

---

# 28. Component Composition & Runtime Self-Usage Policy

Ovo rešava jednu opasnost: da AvaX ima "moćne komponente" koje stoje sa strane, a framework ih realno ne koristi.

**Poenta:**

```text
Ako AvaX ima Concurrency, Parallelism, Cache, CallableSerialization, DataStack,
DataTransfer, Compiled Routes, Connection Pooling, onda AvaX runtime mora
da ih koristi tamo gde ima smisla.

Komponente ne smeju biti samo biblioteka pored frameworka.
Moraju postati unutrašnji mišići samog frameworka.
```

## 28.1 Internal Dogfooding Rule

```text
AvaX core must use AvaX components internally when they are the correct abstraction.

Do not duplicate capabilities.
Do not reimplement local mini-versions.
Do not bypass existing components for convenience.
Do not call Reflection repeatedly at runtime when metadata can be cached/compiled.
Do not use raw arrays for complex runtime state when DataStack structures fit better.
Do not use synchronous/sequential code when Concurrency/Parallelism is the correct runtime model.
```

**Anti-patterns (forbidden):**

```text
- Router implements its own route cache instead of using Cache component
- Container builds its own dependency graph instead of using DataStack/Graph
- SecureRequest implements its own hydrator instead of using DataTransfer
- Queue worker spawns processes directly instead of using Parallelism
- Database creates new connection per query instead of using ConnectionPool
- Outbox implements its own retry instead of using Reliability
- Doctor reimplements governance checks instead of using existing tooling
- Any component reimplements serialization instead of using CallableSerialization
```

## 28.2 Hot Path Optimization Policy

**Hot path =** code that runs per request, per route match, per controller invocation, per DataTransfer hydration, per
database query, per queue job.

**Hot paths in AvaX:**

```text
- route matching
- container resolution
- controller invocation
- SecureRequest/DataTransfer hydration
- middleware pipeline execution
- database query execution
- queue job dispatch and execution
- HTTP response normalization
- exception handling
- correlation ID propagation
```

**Hot paths must avoid:**

```text
- repeated Reflection (class metadata, attributes, property types)
- repeated config parsing
- repeated route compilation
- repeated container dependency graph building
- repeated attribute scanning
- unnecessary serialization/deserialization
- linear route scans where compiled maps/tries work
- raw array chaos for complex runtime state
- direct process spawning outside Parallelism
- direct queue failure handling outside Reliability/DeadLetter
```

**Use instead:**

```text
- compiled route tree (cached lookup, not linear scan)
- compiled container graph (built once, resolved many times)
- cached configuration (immutable after boot)
- cached reflection metadata (class shape, attributes, types)
- cached DataTransfer shapes (field metadata, caster map, required set)
- DataStack structures (Map, Set, Sequence, Graph, Trie)
- connection pools (not per-query new connections)
- immutable warm-state metadata
- CallableSerialization for any callable/closure persistence
- Parallelism for any multi-process work
- Reliability for retry/timeout/circuit-breaker behavior
```

### Reflection Budget

```text
Reflection is allowed in:
- build phase (boot, compile, register)
- first-use metadata discovery (lazy compile)
- tests/tools/scaffolding
- doctor/inspect commands

Reflection is FORBIDDEN in:
- per-request route matching (use compiled route cache)
- per-request controller resolution (use compiled container graph)
- per-request DataTransfer hydration (use cached class-shape metadata)
- per-request middleware resolution (use compiled pipeline)
- per-query connection creation (use ConnectionPool)
- per-job serialization (use pre-compiled worker payloads)
```

### Required Caches

```text
Route metadata cache          — compiled route tree, method+path -> handler map
Container dependency cache    — compiled service map, dependency graph, singleton registry
DataTransfer class-shape cache — field types, attributes, casters, required fields
Attribute metadata cache      — scanned attributes per class, cached by class name
Schema metadata cache         — generated schema per DataObject, cached
Middleware pipeline cache     — compiled middleware stack per route/group
Configuration cache           — parsed config values, immutable after boot
```

## 28.3 Serialization Policy

```text
Use serialization only at correct boundaries:
- compiled cache (file-based route cache, container cache)
- worker payload (Parallelism process pool)
- route closure cache (if closure routes need persistence)
- encrypted payloads (DataProtection boundaries)
- persistence boundaries (queue job storage, outbox records)
```

**Rules:**

```text
Callable/closure serialization MUST go through CallableSerialization.
Worker payloads MUST be signed before dispatch.
No unsafe unserialize before verification.
No raw serialize/unserialize for security-sensitive data.
Serialization must not leak into hot request path unless unavoidable.
```

## 28.4 Component Reuse Matrix

| Component                                       | Must be used by                                                                                               | Purpose                                     | Proof required                                                        |
|-------------------------------------------------|---------------------------------------------------------------------------------------------------------------|---------------------------------------------|-----------------------------------------------------------------------|
| **DataStack** (Map, Set, Sequence, Graph, Trie) | Router (route tree), Container (dependency graph), Queue (job ordering), Scheduler, Observability (span tree) | Replace raw arrays with explicit structures | Architecture test: no duplicate local structures where DataStack fits |
| **DataTransfer**                                | SecureRequest (hydration/validation), SchemaGeneration, ResponseNormalization, Scaffolding                    | Single hydration/validation vocabulary      | No duplicated hydrator outside DataTransfer                           |
| **SecureRequest**                               | App runtime (controller invocation), ErrorHandling (validation response)                                      | Request-as-DTO with Container autowiring    | SecureRequest is the only request hydration path                      |
| **Cache**                                       | Router (compiled routes), Container (compiled metadata), DataTransfer (class-shape cache), Config, Schema     | Avoid repeated Reflection/parsing           | Metadata cache tests, invalidation tests                              |
| **CallableSerialization**                       | Parallelism (worker payloads), Router (closure route cache if persistent), Scaffolding (callable templates)   | Safe closure serialization                  | Signed payload tests, verify-before-unserialize tests                 |
| **Concurrency**                                 | ReactPHP runtime (async orchestration), async HTTP/DB calls, Event dispatcher                                 | Cooperative async within process            | async()/await() tests, interleaving tests                             |
| **Parallelism**                                 | Queue workers, process pool, CPU-heavy jobs, batch operations                                                 | Multi-process execution                     | PID-based tests, worker payload tests                                 |
| **Reliability**                                 | Queue (retry), Outbox (relay retry), Service-to-Service (retry/timeout), Database (safe retry)                | Retry/timeout/circuit-breaker/fallback      | Retry respects max attempts, timeout stops slow ops                   |
| **Queue**                                       | Outbox relay, async event dispatch, scheduled jobs, background work                                           | Asynchronous work execution                 | Dispatch/run/retry/dead-letter tests                                  |
| **Database/ConnectionPool**                     | Database (query execution), Cache (Redis connection), Queue (DB driver), Outbox (DB write)                    | Connection reuse in long-lived runtime      | Pool creates min at boot, release resets state                        |
| **ErrorHandling**                               | App runtime, SecureRequest, Worker, HTTP, ReactPHP, Queue                                                     | Unified exception classification/rendering  | Exception maps to correct HTTP status                                 |
| **Observability**                               | HTTP runtime, Queue, Database, Outbox, Service-to-Service, HealthCheck                                        | Correlation/tracing/metrics/audit           | Correlation ID propagates through all boundaries                      |
| **RuntimeDoctor**                               | HealthCheck, governance validation, integrity checks                                                          | Unified diagnostic command                  | Doctor uses existing checks, not duplicates                           |
| **Storage/Filesystem**                          | Cache (file driver), Config (file-based), Scaffolding (file generation), compiled cache storage               | File operations                             | Filesystem has zero dependency on Storage                             |

## 28.5 Runtime Composition Rules

### Runtime App Layer uses:

```text
Router              — route registration and matching
Container           — controller resolution and autowiring
HTTP                — Request/Response objects
Middleware          — pipeline execution
Response            — response normalization
ErrorHandling       — exception catching and rendering
SecureRequest       — request-as-DTO hydration
DataTransfer        — payload hydration/validation/casting
Observability       — correlation ID, trace span
```

### ReactPHP Runtime uses:

```text
Runtime App Layer   — all App behavior stays behind App API
RequestScope reset  — Warm Worker Safety between requests
MemoryGuard         — memory tracking and worker recycling
ErrorHandling       — exception rendering in async context
Observability       — async trace propagation
Concurrency         — async task orchestration where applicable
Promise/Fiber bridge — ReactPHP Promise to Fiber coordination
```

### Warm Worker Safety uses:

```text
RequestScope        — scoped lifecycle management
Container           — scoped instance flushing
MemoryGuard         — memory tracking and threshold enforcement
Observability       — reset correlation context per request
RuntimeDoctor       — leak detection diagnostics
```

### Queue/Worker uses:

```text
Parallelism         — process pool execution
CallableSerialization — callable payload serialization
Reliability         — retry, timeout, circuit-breaker
DeadLetter          — terminal failure quarantine
Observability       — job trace with correlation ID
ErrorHandling       — job failure classification
```

### Database uses:

```text
ConnectionPool      — connection reuse in long-lived runtime
TransactionManager  — transaction wrapping
Observability       — query span, slow query detection
Reliability         — safe retry where idempotent
HealthCheck         — SELECT 1 liveness check
```

### Outbox/Relay uses:

```text
Database            — transaction + outbox write
Queue/Worker        — relay worker execution
Reliability         — retry policy, idempotency
DeadLetter          — failed publish quarantine
Observability       — event trace through relay
```

### HealthCheck/Doctor uses:

```text
Autoload check      — existing governance
PHPStan status      — existing governance
PHPUnit status      — existing governance
Namespace drift     — existing governance
Public surface      — existing governance
Runtime leaks       — existing governance
Route cache health  — compiled route integrity
DB pool health      — ConnectionPool status
Queue health        — Queue driver status
Cache health        — Cache driver status
MemoryGuard status  — worker memory tracking
Broken refs         — existing governance
```

## 28.6 Architecture Tests For Internal Reuse

Required checks (must be implemented as architecture tests):

```text
- no duplicated hydration logic outside DataTransfer
- no duplicated callable serialization outside CallableSerialization
- no duplicated route matching cache outside Router
- no DataStack/DataTransfer dependency cycles
- no runtime-specific API leakage into core public surface
- no repeated Reflection in hot path where cache exists
- no direct process spawning outside Parallelism
- no direct queue failure handling outside Reliability/DeadLetter
- no direct connection creation outside ConnectionPool (in long-lived runtime)
- no raw unserialize without verification in security-sensitive path
- no configuration parsing on every request
- no middleware pipeline rebuild on every request
```

## 28.7 Performance Proof Requirement

```text
Any V4 component that claims performance benefit must provide evidence:
- before/after benchmark
- cold/warm measurement
- route matching benchmark (compiled vs linear)
- container resolution benchmark (compiled vs per-request Reflection)
- DataTransfer metadata cache benchmark (cached vs per-request scanning)
- process pool benchmark (Parallelism vs sequential)
- memory usage report where relevant
- connection pool benchmark (pooled vs per-query new connection)
```

**Rule:** No performance claim without both cold and warm measurements (Section 27.6).

## 28.8 Stage Gate Update — Composition Proof

Before each V4 stage can be marked GREEN, it must prove:

```text
1. It uses existing AvaX components where appropriate (no duplication).
2. It does not reimplement capabilities that already exist.
3. Hot path behavior is cached/compiled where appropriate.
4. Performance-sensitive behavior has benchmark evidence.
5. Dependency direction is clean (no circular deps between components).
6. Architecture tests for internal reuse pass.
7. Reflection budget is respected (no repeated Reflection in hot path).
8. Serialization policy is followed (safe boundaries, signed payloads).
```

This is in addition to the stage-specific acceptance gates defined in Section 27.10.

## 28.9 Canonical Source Rule

```text
V4_PRODUCT_RUNTIME_AND_ENTERPRISE_MUSCLE.md is the canonical V4 execution plan.
v4-intelligence-governance-observability-plan.md is source material only.
Any future V4 changes must update the canonical file, not create parallel plans.
```

The older plan (`v4-intelligence-governance-observability-plan.md`) remains valid as source material but must not be
used as an independent execution plan. Its concepts are merged into this document as Section 29.

---

# 29. V4 Intelligence, Governance & Diagnostics Plane

This section merges valid concepts from the older `v4-intelligence-governance-observability-plan.md` into the canonical
V4 plan.

**Decision:** These concepts support V4 stages, not replace them. Intelligence must be subservient to runtime execution.

## 29.1 V4 Thesis (from old plan)

V1 makes AvaX real.
V2 makes AvaX enterprise-useful.
V3 makes AvaX system-design-intelligent.
V4 makes AvaX product-ready, self-governing, observable, diagnosable, secure, recoverable, and AI-assisted.

V4 turns AvaX from:

```text
a framework that validates architecture
```

into:

```text
a framework that understands, observes, explains, protects, diagnoses, and safely evolves architecture.
```

## 29.2 Hard Boundary (from old plan)

AvaX does not replace infrastructure. AvaX disciplines infrastructure usage.

V4 must not implement:

```text
custom Kafka, custom Redis Cluster, custom RocksDB, custom Elasticsearch,
custom FFmpeg, custom CDN, custom ML recommendation engine,
custom Raft/Paxos consensus, custom high-frequency matching engine,
custom video transcoding engine, custom distributed database,
custom low-level networking stack
```

V4 may implement:

```text
architecture intelligence, evidence graph, architecture memory,
governance enforcement, AI delivery governance, runtime observability,
operator diagnostics, security certification, database intelligence,
cache intelligence, worker safety doctor, plugin capability security,
template compiler / view doctor, reference architecture products,
safe recovery planning
```

**Correct mental model:**

```text
AvaX does not become the infrastructure.
AvaX becomes the framework that helps teams use infrastructure safely.
```

## 29.3 Architecture Intelligence

**Purpose:** Give AvaX a machine-readable understanding of its own architecture.

**Capabilities:**

```text
IndexProjectKnowledge
ReadGovernanceRules
ReadHowToRules
MapComponentOwnership
ClassifyComponentMuscle
DetectArchitectureDrift
DetectPublicSurfaceViolation
DetectRuntimeLeak
DetectGenericNamingViolation
CompareCurrentTreeWithBackup
CompareCurrentTreeWithGitHistory
ClassifyMissingBehavior
ClassifySkeletonComponent
ExplainComponentState
ExplainOwnershipBoundary
ExplainPromotionRisk
```

**Stage assignment:** This feeds V4-14 (Runtime Doctor & Control Plane) and V4-04 (Developer Experience). Architecture
intelligence is the diagnostic brain behind `php avax doctor` and `php avax architecture:check`.

## 29.4 Evidence Graph

**Purpose:** Track what is actually proven, not what documents merely claim.

**Core Concepts:**

```text
EvidenceNode, EvidenceSource, ValidationCommand, ValidationResult,
ProofSlice, BehaviorClaim, Contradiction, StageGate, PromotionDecision,
ComponentState, EvidenceTimeline
```

**Required Behavior:** AvaX must know:

```text
- which tests prove which behavior
- which reports prove which stage
- which commands were run
- which static checks passed
- which component is GREEN/YELLOW/RED
- which proof slice unlocked the next stage
- which claims are stale
- which claims are contradicted by current evidence
```

**Rule:** No proof, no GREEN.

**Stage assignment:** This is the evidence backbone for all V4 stage gates (Section 27.10). Evidence Graph is not a
separate stage — it is the validation model that every stage must use.

## 29.5 AI Delivery Governance

**Purpose:** Make AI-assisted development staged, safe, narrow, and evidence-driven.

**Capabilities:**

```text
ResolveActiveStage, RejectOutOfScopeWork, GenerateCodexPrompt,
GenerateAgentTask, ReviewAgentOutput, RequireProofBeforePromotion,
EnforceNoV2BeforeV1Green, EnforceNoV3BeforeV2Baseline,
EnforceNoV4BeforeV3Proof, EnforceHowToRules, DetectBroadRefactorRisk,
DetectSkeletonRecovery, DetectUnsafeRestore, WriteCompletionReport,
WriteNextAllowedAction
```

**Required Agent Workflow:**

```text
1. Read CURRENT_TRUTH.md
2. Read AGENTS.md
3. Read EXECUTION.md
4. Read TODO.md
5. Read relevant .agents/how-to/*.md
6. Resolve active stage
7. Refuse out-of-stage work
8. Work only on allowed slice
9. Run required validation
10. Write proof report
11. Update truth documents
12. State next allowed action
```

**Stage assignment:** This is already implemented in `AGENTS.md` (Sections 1-4, 17-21). AI Delivery Governance is the
execution contract, not a separate V4 stage. It governs all V4 work.

## 29.6 Safe Recovery Engine

**Purpose:** Recover old behavior without resurrecting old architecture.

**Inputs:**

```text
avax-backup.txt, Framework.txt, Components.txt, git history,
current source tree, current tests, how-to-*.md rules,
CURRENT_TRUTH.md, EXECUTION.md, TODO.md
```

**Recovery Rules:**

```text
Old behavior is valuable.
Old structure is not automatically valuable.
Skeleton code is not acceptable.
Recovered code must fit current architecture.
Recovered code must have tests.
Recovered code must pass static validation.
Recovered code must not unlock V2/V3/V4 prematurely.
Recovered code must not reintroduce forbidden naming.
```

**Stage assignment:** This feeds V4-04 (Developer Experience — `php avax doctor`) and is the recovery discipline behind
all V4 stage gates. Not a separate stage.

## 29.7 Architecture Memory

**Purpose:** Preserve why the system is shaped the way it is.

**Artifacts:**

```text
ArchitectureDecision, ComponentHistory, RecoveryDecision,
NamingDecision, BoundaryDecision, PromotionDecision,
DeprecationDecision, StageDecision, RuntimeDecision,
SecurityDecision
```

**AvaX Must Be Able To Explain:**

```text
why a folder exists
why a public surface exists
why an adapter boundary exists
why a component is experimental
why a legacy alias exists
why a feature is V2 and not V1
why a V3 model is validation-only
why a V4 intelligence feature is not runtime infrastructure
```

**Stage assignment:** Architecture Memory feeds V4-14 (Runtime Doctor) and V4-04 (Developer Experience). It is the "why"
behind doctor output.

## 29.8 Runtime Worker Safety Doctor (from old plan)

**Purpose:** Prove that AvaX is safe in long-lived PHP runtimes.

**Problems Solved:**

```text
request state leaks, static state leaks, memory growth,
unclosed resources, missing reset hooks, unsafe singletons,
worker lifecycle drift, signal handling gaps, runtime mode confusion
```

**Product Commands:**

```text
php avax runtime:doctor
php avax worker:doctor
php avax memory:doctor
php avax reset:doctor
php avax state:leaks
```

**Capabilities:**

```text
DetectStaticStateLeak, DetectRequestStateLeak, DetectWorkerMemoryGrowth,
VerifyResetHooks, VerifyWorkerSafeComponent, CheckSignalHandling,
CheckResourceCleanup, ExplainRuntimeSafety
```

**Required Tests:**

```text
worker request loop does not leak request state
reset hooks run after request
memory growth is detected
static state risk is reported
unsafe singleton is flagged
runtime doctor reports UNKNOWN instead of fake GREEN
```

**Non-Goals:**

```text
Do not implement custom Swoole/RoadRunner.
Do not replace runtime servers.
Do not hide state leaks.
```

**Stage assignment:** This is fully merged into V4-03 (Warm Worker Safety). The Runtime Worker Safety Doctor is V4-03.
MemoryGuard (Section 27.5) and Warm State Contract (Section 27.1) are the implementation of this concept.

## 29.9 Database Intelligence (from old plan)

**Purpose:** Make AvaX able to detect and explain database risks before production pain.

**Problems Solved:**

```text
slow queries, N+1 queries, unsafe raw SQL, missing bindings,
schema drift, missing indexes, transaction leaks, deadlock-prone flows,
read/write split mistakes, JSONB/EAV query risks, connection pool pressure
```

**Product Commands:**

```text
php avax db:doctor
php avax db:slow
php avax db:n-plus-one
php avax db:schema:diff
php avax db:index:advise
php avax db:transactions:audit
php avax db:jsonb:audit
php avax db:eav:audit
php avax db:pool
```

**Capabilities:**

```text
QueryTimeline, SlowQueryDetection, NPlusOneDetection, QueryFingerprint,
TransactionAudit, SchemaDriftDetection, IndexRecommendations,
ReadWriteSplitDiagnostics, ConnectionPoolPressure, JsonbStorageAudit,
EavModelingAudit, QueryShapeAnalysis, ShardingAdvice
```

**Required Tests:**

```text
slow query is detected
N+1 pattern is detected
unsafe SQL interpolation is flagged
missing binding is flagged
schema drift is detected
index recommendation is generated
transaction leak is detected
JSONB/EAV risk is reported
```

**Non-Goals:**

```text
Do not build a custom database.
Do not replace PostgreSQL/MySQL.
Do not auto-create production indexes without explicit approval.
Do not pretend all query optimization can be automated.
```

**Stage assignment:** Database Intelligence feeds V4-07 (Database Muscle) and V4-14 (Runtime Doctor). The diagnostic
commands (`db:doctor`, `db:slow`, `db:n-plus-one`) are part of the Doctor component. Database intelligence capabilities
are diagnostics, not core database operations.

## 29.10 Cache Intelligence (from old plan)

**Purpose:** Make AvaX cache behavior observable, explainable, and safe.

**Problems Solved:**

```text
low hit ratio, cache stampede, hot keys, stale data risk,
bad TTL choices, cache node failure, missing warming,
unclear invalidation, write-through/write-behind confusion
```

**Product Commands:**

```text
php avax cache:doctor
php avax cache:warm
php avax cache:explain <key>
php avax cache:hot-keys
php avax cache:stampede
php avax cache:hit-ratio
```

**Capabilities:**

```text
CacheHierarchy, CacheHitRatio, CacheMissTracking, CacheStampedeDetection,
CacheWarming, TagInvalidation, StaleWhileRevalidateDiagnostics,
ConsistentHashing, CacheNodeHealth, HotKeyDetection, CacheKeyExplanation
```

**Required Tests:**

```text
cache hit/miss metrics are recorded
stampede risk is detected
stale-while-revalidate behavior is explained
cache warming path is validated
hot key is detected
cache node health is reported
```

**Stage assignment:** Cache Intelligence feeds V4-14 (Runtime Doctor) and V4-11 (Observability & Telemetry). Cache
diagnostic commands are part of the Doctor component. Cache intelligence capabilities are diagnostics + observability,
not core cache operations.

## 29.11 Security Certification Suite (from old plan)

**Purpose:** Make production security measurable. Security is not decoration. Security is a production gate.

**Problems Solved:**

```text
unsafe encryption, AES-CBC without authentication,
unserialize on user/session data, @unserialize suppression,
unsafe SQL interpolation, weak cookies, missing CSRF,
missing signed request checks, secret leakage, missing redaction,
unsafe plugin permissions, field encryption gaps
```

**Product Commands:**

```text
php avax security:doctor
php avax security:audit
php avax crypto:doctor
php avax secrets:doctor
php avax sessions:doctor
php avax sql:safety
```

**Capabilities:**

```text
DetectUnsafeEncryption, DetectUnserializeUsage, DetectSuppressedSecurityFailure,
DetectUnsafeSql, DetectSecretLeak, CheckCookieSecurity, CheckCsrfCoverage,
CheckSignedRequestCoverage, CheckRedactionCoverage, CheckFieldEncryption,
SecurityFinding, SecurityCertificationReport
```

**Required Tests:**

```text
unsafe encryption is flagged
unserialize usage is flagged
@ suppression is flagged
unsafe SQL interpolation is flagged
missing CSRF is detected
secret leak is detected
redaction prevents sensitive payload logging
```

**Final Rule:** AvaX cannot claim production-ready while Security Certification is RED.

**Stage assignment:** Security Certification is fully merged into V4-12 (Security & Policy Runtime). The security doctor
commands (`security:doctor`, `security:audit`, `crypto:doctor`) are part of the Doctor component. Security Certification
provides the security baseline proof required by V4-12 acceptance gate.

## 29.12 Observability / OpenTelemetry Suite (from old plan)

**Purpose:** Expose operational truth from running systems.

**Problems Solved:**

```text
unknown latency, unknown queue depth, unknown retry storms,
unknown projection lag, unknown cache hit ratio, unknown SLO violations,
untraceable request flow, missing correlation IDs, payload leakage in logs
```

**Product Commands:**

```text
php avax observability:doctor
php avax observability:summary
php avax trace:explain
php avax metrics:export
php avax slo:check
php avax profile:hot-path
```

**Capabilities:**

```text
RecordLatency, RecordFailure, RecordRetry, RecordTimeout,
RecordCircuitOpen, RecordBulkheadReject, RecordQueueDepth,
RecordProjectionLag, RecordCacheHitRatio, RecordCacheMiss,
RecordStaleServed, RecordLockWait, RecordDeadLetter, RecordOutboxLag,
RecordConnectionPoolPressure, RecordMemoryGrowth, RecordWorkerRestart,
RecordSloViolation, OpenTelemetryTraceBackend, PrometheusMetricsBackend,
InMemoryMetricsBackend, FileMetricsBackend, NullMetricsBackend,
TraceCorrelation, RuntimeTimeline
```

**Required Tests:**

```text
request id is recorded
correlation id is propagated
trace id is propagated
latency sample is recorded
queue depth sample is recorded
SLO violation is detected
redaction prevents sensitive logs
OpenTelemetry export shape is valid
Prometheus metrics shape is valid
```

**Non-Goals:**

```text
Do not build Grafana.
Do not build Prometheus.
Do not log sensitive payloads by default.
```

**Stage assignment:** Observability Suite is fully merged into V4-11 (Observability & Telemetry). The observability
capabilities listed here extend the V4-11 feature set with specific metric types, backends, and diagnostic commands.

## 29.13 Plugin Capability Security (from old plan)

**Purpose:** Allow extension/plugin ecosystems without letting plugins destroy the framework boundary.

**Problems Solved:**

```text
unsafe plugins, unbounded plugin permissions, hidden dependencies,
unsafe boot lifecycle, plugin namespace drift, plugin version incompatibility,
plugin access to forbidden capabilities, plugin supply-chain risk
```

**Product Commands:**

```text
php avax plugin:doctor
php avax plugin:permissions
php avax plugin:explain vendor/package
php avax plugin:graph
php avax plugin:security
```

**Capabilities:**

```text
PluginManifest, PluginCapabilityGrant, PluginPermission,
PluginSandboxPolicy, PluginBootLifecycle, PluginDependencyGraph,
VerifyPluginSafety, VerifyPluginCompatibility, ExplainPluginPermissions,
DetectUnsafePluginAccess
```

**Required Tests:**

```text
plugin manifest is validated
plugin permission violation is detected
plugin dependency graph is built
unsafe plugin access is blocked
plugin lifecycle order is verified
```

**Non-Goals:**

```text
Do not build a marketplace in V4.
Do not implement WebAssembly sandboxing yet.
Do not allow plugins to bypass PublicSurface.
```

**Stage assignment:** Plugin Capability Security is a **V4 stretch goal / Labs item**. It does not block V4-01 through
V4-16. If implemented, it belongs in a future stage after V4-14 or in V5 planning.

## 29.14 Template Compiler / View Doctor (from old plan)

**Purpose:** Make the Presentation/View layer fast, safe, inspectable, and cacheable.

**Problems Solved:**

```text
unsafe escaping, slow view rendering, unclear compiled templates,
template cache drift, missing sandboxing, XSS risk, hard-to-debug view logic
```

**Product Commands:**

```text
php avax view:compile
php avax view:cache
php avax view:clear
php avax view:explain home.index
php avax view:doctor
```

**Capabilities:**

```text
TemplateLexer, TemplateParser, TemplateAst, CompileTemplate,
CacheCompiledView, EscapeTemplateOutput, ContextualEscaping,
TemplateSandbox, ExplainTemplateAst, DetectUnsafeTemplateOutput
```

**Required Tests:**

```text
template compiles to cached PHP
escaped output is safe
raw output requires explicit marker
template AST is explainable
view cache invalidates correctly
unsafe output is flagged
```

**Non-Goals:**

```text
Do not build a full frontend framework.
Do not allow unsafe raw output by default.
Do not make template compiler part of V3.
```

**Stage assignment:** Template Compiler / View Doctor is a **V4 stretch goal / Labs item**. AvaX already has BladeOne (
`eftec/bladeone`) as a dependency. If a View layer becomes active in V4, this belongs under V4-04 (Developer Experience)
or a future stage. Not a V4-01 blocker.

## 29.15 Architecture Doctor Productization (from old plan)

**Purpose:** Turn governance checks into a usable product command.

**Product Commands:**

```text
php avax architecture:doctor
php avax architecture:drift
php avax architecture:explain components/DataStack/Database
php avax architecture:why-green DataStack/Database
php avax architecture:next
```

**Checks:**

```text
component shape, duplicate ownership, namespace drift,
public surface leaks, runtime leaks, forbidden names,
empty folders, one-class-per-file, stale docs, stale evidence,
unproven completion, skeleton components, missing tests
```

**Output Rules:**

```text
GREEN means proven.
YELLOW means partial.
RED means blocked.
UNKNOWN means not measured.
```

**Stage assignment:** Architecture Doctor is fully merged into V4-14 (Runtime Doctor & Control Plane) and V4-04 (
Developer Experience). The `php avax doctor` command and architecture checks are the implementation of this concept.

## 29.16 Reference Architecture Products (from old plan)

**Purpose:** Turn V3 modeling into concrete, runnable, teachable product examples.

**Reference Products:**

```text
reference-architectures/url-shortener/
reference-architectures/distributed-rate-limiter/
reference-architectures/webhook-ingestion/
reference-architectures/notification-delivery/
reference-architectures/parking-lot/
reference-architectures/news-feed/
reference-architectures/payment-workflow/
```

**Each Reference Architecture Must Include:**

```text
runtime implementation, capacity model, consistency model,
messaging model, failure model, observability report,
security checklist, benchmark report, architecture doctor output,
deployment assumption, operator guide
```

**Required Commands:**

```text
php avax system-design:check reference-architectures/url-shortener
php avax architecture:doctor reference-architectures/url-shortener
php avax observability:summary reference-architectures/url-shortener
php avax security:doctor reference-architectures/url-shortener
```

**Stage assignment:** Reference Architecture Products are fully merged into V4-15 (Reference Applications). The
reference apps listed in V4-15 (Section 19) are the implementation. Each reference app must include the V3 models (
Capacity, Consistency, Messaging) and the diagnostics output from V4-14.

## 29.17 Merged Concept Summary

| Old Plan Concept                    | Merged Into V4 Stage                   | Status                                     |
|-------------------------------------|----------------------------------------|--------------------------------------------|
| Architecture Intelligence           | V4-14 (Doctor) + V4-04 (DX)            | Merged                                     |
| Evidence Graph                      | All stage gates (Section 27.10)        | Backbone model                             |
| AI Delivery Governance              | AGENTS.md execution contract           | Already implemented                        |
| Safe Recovery Engine                | V4-04 (DX) + stage gates               | Merged                                     |
| Architecture Memory                 | V4-14 (Doctor) + V4-04 (DX)            | Merged                                     |
| Runtime Worker Safety Doctor        | V4-03 (Warm Worker Safety)             | Merged — MemoryGuard + Warm State Contract |
| Database Intelligence               | V4-07 (Database) + V4-14 (Doctor)      | Merged                                     |
| Cache Intelligence                  | V4-11 (Observability) + V4-14 (Doctor) | Merged                                     |
| Security Certification Suite        | V4-12 (Security & Policy Runtime)      | Merged                                     |
| Observability / OpenTelemetry Suite | V4-11 (Observability & Telemetry)      | Merged                                     |
| Plugin Capability Security          | Labs / V5 planning                     | Stretch goal                               |
| Template Compiler / View Doctor     | Labs / future V4 or V5                 | Stretch goal                               |
| Architecture Doctor Productization  | V4-14 (Doctor) + V4-04 (DX)            | Merged                                     |
| Reference Architecture Products     | V4-15 (Reference Applications)         | Merged                                     |

**Key rule:** Intelligence, governance, and diagnostics concepts support V4 stages. They do not precede V4-01 (Runtime
App Layer). Runtime comes first; intelligence observes, diagnoses, and protects it.

---

# 30. Framework Maturity Gate Summary

Ovih 10 gates su **non-negotiable** za V4 production-ready claim:

| #  | Gate                    | Stage             | Hard Gate?                                   |
|----|-------------------------|-------------------|----------------------------------------------|
| 1  | Warm State Contract     | V4-03             | YES — no runtime adapter without it          |
| 2  | Connection Pooling      | V4-07             | YES — long-lived runtime requires it         |
| 3  | Compiled Route Cache    | V4-04             | NO — optimization, not blocker               |
| 4  | Serve Modes Matrix      | V4-02             | YES — at least dev + smoke + react           |
| 5  | MemoryGuard             | V4-03             | YES — first-class Warm Worker Safety feature |
| 6  | Cold/Warm Benchmarks    | V4-16             | YES — no performance claim without both      |
| 7  | Zero-Dependency Feeling | V4-01             | YES — App API must stay tiny                 |
| 8  | Microservices Toolkit   | V4-10/V4-12/V4-14 | NO — stretch goal, not V4 blocker            |
| 9  | Health Check Endpoint   | V4-14             | YES — production mode requires it            |
| 10 | Updated Stage Order     | All               | YES — gates control stage progression        |

**Plus Component Composition (Section 28):**

| #  | Gate                   | Stage                        | Hard Gate?                               |
|----|------------------------|------------------------------|------------------------------------------|
| 11 | Internal Dogfooding    | All V4 stages                | YES — no trophy components               |
| 12 | Hot Path Optimization  | All V4 stages                | YES — no repeated Reflection in hot path |
| 13 | Component Reuse Matrix | All V4 stages                | YES — architecture tests must pass       |
| 14 | Performance Proof      | V4-16 + any perf-claim stage | YES — no claim without evidence          |

---

# 31. Final Statement

Ovaj V4 plan definiše šta AvaX postaje: **framework koji ima start dugme, runtime, developer DX, production proof i
enterprise muscle.**

Redosled je bitan:

```text
1. App Layer (start dugme, zero-dependency feeling, error handling, health baseline)
2. ReactPHP Runtime (prvi runtime, serve modes)
3. Warm Worker Safety (warm state contract, memoryguard — sigurnost pre ekspanzije)
4. DX (developer experience, route cache, configuration as code)
5. Data/Storage/Database (podaci, connection pooling)
6. Queue/Reliability/Messaging (pouzdanost, service-to-service basics)
7. Observability/Security/Doctor (proof, service discovery, health endpoints)
8. Reference Apps (dokaz)
9. Benchmarks (merenje, cold/warm)
10. Runtime Adapters (ekspanzija — only after warm worker safety proven)
```

**Komponente moraju biti unutrašnji mišići, ne trofejne biblioteke.**

```text
Router koristi DataStack + Cache.
Container koristi Graph + Cache + compiled metadata.
DataTransfer koristi cached reflection, ne per-request scanning.
SecureRequest koristi DataTransfer, ne mini-validator.
Queue koristi Parallelism + CallableSerialization + Reliability.
Database koristi ConnectionPool, ne per-query connection.
Outbox koristi Queue + Reliability + DeadLetter.
ReactPHP koristi Warm Safety + MemoryGuard + Observability.
Doctor koristi existing governance checks, ne duplicate.
```

V4 ne počinje implementacijom dok stage lock nije GREEN.

**Intelligence, governance, and diagnostics concepts (Section 29) are merged.**
Old plan `v4-intelligence-governance-observability-plan.md` is source material only.
No parallel V4 plans exist.

**Status: V4-00 ACTIVE — Stage definition complete. Framework Maturity Gates added. Component Composition added.
Intelligence/Governance/Diagnostics merged. Canonical source rule established. Next: V4-01 Runtime App Layer
implementation.**

---

# 32. V4 Branch Policy

## 32.1 Branch Roles

```text
master = stable protected branch.
         Holds the current clean baseline, including the official V4 plan.
         Receives V4 only when V4 is production-ready.
         No direct feature work.
         No V4 implementation directly on master.

main   = active V4 development / integration branch.
         Must be updated by merging master.
         V4 development happens on main through stage branches.
```

## 32.2 Branch Rules

```text
- master keeps the current clean baseline, including the official V4 plan.
- main must be updated by merging master.
- V4 development happens on main through stage branches.
- No direct feature work on master.
- No V4 implementation directly on master.
- master receives V4 only when V4 is production-ready.
- Every V4 stage branch starts from main.
- Every merge into main must pass full validation.
- Every merge into master must be a release-grade merge.
```

## 32.3 Feature Branch Naming

```text
v4/01-runtime-app-layer
v4/02-reactphp-runtime
v4/03-warm-worker-safety
v4/04-developer-experience
v4/05-data-platform-productization
v4/06-storage-platform
v4/07-database-muscle
v4/08-queue-worker-runtime
v4/09-reliability-engine
v4/10-messaging-consistency
v4/11-observability-telemetry
v4/12-security-policy-runtime
v4/13-system-design-runtime-kit
v4/14-runtime-doctor-control-plane
v4/15-reference-applications
v4/16-benchmarks-production-proof
v4/17-optional-runtime-adapters
```

## 32.4 Required Validation Before Merging Any V4 Branch into main

```bash
composer validate --no-check-publish
composer dump-autoload -o
composer test:full
vendor/bin/phpstan analyse framework components tests labs/SystemDesignKit --memory-limit=1G
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
php tooling/refactor/check-component-canonical-shape.php
php tooling/refactor/check-advanced-pattern-folder-violations.php
```

## 32.5 Merge Rules

```text
V4 stage branch -> main: requires full validation GREEN.
main -> master: release-grade merge only, after V4 production-ready proof.
master -> main: merge master into main before starting any new V4 stage branch.
```

## 32.6 Next Allowed Git Actions

1. Merge master into main.
2. Start V4-01 Runtime App Layer from main only after validation passes.
