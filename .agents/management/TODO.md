# ToDo: Avax Enterprise-Grade Recovery Plan

> **Target:** 10/10 Enterprise Grade Quality
> **Goal:** Full production-ready enterprise PHP framework
> **Current State:** 10/10 Enterprise Recovery slice complete
> **Verification:** `./vendor/bin/phpunit --no-coverage --testsuite EnterpriseRecovery` => 83 tests, 118 assertions

---

## Enterprise Graduation Plan

| Task                                 | Status    | Evidence                                                      |
|--------------------------------------|-----------|---------------------------------------------------------------|
| TASK-E001 HTTP Built-in Server       | COMPLETED | `php avax serve` standalone command                           |
| TASK-E002 Session Storage            | COMPLETED | file, Redis, database session stores and driver factory       |
| TASK-E003 Cache Stores               | COMPLETED | Redis, Memcached, tagged cache support                        |
| TASK-E004 Database Migrations        | COMPLETED | migration, fresh, refresh, seeder commands                    |
| TASK-E005 Queue/Background Jobs      | COMPLETED | queue, Redis queue, task bus                                  |
| TASK-E006 API Rate Limiting          | COMPLETED | rate middleware, Redis sliding window limiter, headers        |
| TASK-E007 WebSocket Support          | COMPLETED | connection pool, channels, broadcast, presence, client helper |
| TASK-E008 File Cloud Storage         | COMPLETED | storage adapter contract, local and S3 adapters, signed URLs  |
| TASK-E009 Email Queue                | COMPLETED | mail queue, SMTP transport, mailable builder                  |
| TASK-E010 Test Coverage Expansion    | COMPLETED | EnterpriseRecovery suite with integration and unit coverage   |
| TASK-E011 Auto API Documentation     | COMPLETED | OpenAPI generator, Swagger UI, docs routes                    |
| TASK-E012 Monitoring & Observability | COMPLETED | metrics, health, Sentry, dashboard data                       |
| TASK-E013 Blade Views                | COMPLETED | directives, components, slots, stacks, cache clear            |
| TASK-E014 CLI Commands Expansion     | COMPLETED | make, migrate, cache, explainable, doctor commands            |
| TASK-E015 Security Hardening         | COMPLETED | CSRF, escaping, mass assignment guard, audit log              |
| TASK-E016 Performance Optimization   | COMPLETED | query, route, config caching and lazy values                  |

---

## The AvaX Difference

| Task                                       | Status    | Evidence                                                                        |
|--------------------------------------------|-----------|---------------------------------------------------------------------------------|
| TASK-SD001 Runtime Doctor & Safety         | COMPLETED | `runtime:doctor`, runtime safety, state leak detection, static state inspection |
| TASK-SD002 Explainable Intelligence        | COMPLETED | `container:why`, `container:scope-audit`, `routes:explain`, `routes:conflicts`  |
| TASK-SD003 Execution Tracing               | COMPLETED | tracing timeline capability and runtime event model                             |
| TASK-SD004 Configuration Schema Validation | COMPLETED | typed config schema, validation, `config:explain`                               |
| TASK-SD005 Static Architecture Guard       | COMPLETED | `architecture:check` and architecture tooling                                   |

---

## Muscular Core

| Task                                    | Status    | Evidence                                                                   |
|-----------------------------------------|-----------|----------------------------------------------------------------------------|
| TASK-M001 Resilience Component          | COMPLETED | retry, circuit breaker, backoff, retry/break flows                         |
| TASK-M002 Concurrency & Async           | COMPLETED | run concurrent tasks, await task, cancellation, event loop adapter         |
| TASK-M003 Realtime                      | COMPLETED | realtime public surface, channels, presence, WebSocket broadcasting        |
| TASK-M004 Execution Tracing             | COMPLETED | tracing timeline capability                                                |
| TASK-M005 Runtime Safety                | COMPLETED | runtime safety capability and leak/static scanners                         |
| TASK-M006 Secrets & Vault               | COMPLETED | secrets public surface, read/redact flows, stores and encryption           |
| TASK-M007 Scheduler                     | COMPLETED | scheduler public surface, register/run due flows, cron/history             |
| TASK-M008 Database Safety & Governance  | COMPLETED | slow query, fingerprinting, transactions, N+1 diagnostics                  |
| TASK-M009 Test Doubles & Fakes          | COMPLETED | event, cache, HTTP and task fakes                                          |
| TASK-M010 Multi-Runtime Abstraction     | COMPLETED | Swoole, FrankenPHP, RoadRunner, Workerman adapters and reset flows         |
| TASK-M011 Architectural Normalization   | COMPLETED | canonical System paths used; compatibility wrappers retained intentionally |
| TASK-M012 Queue -> Tasks                | COMPLETED | `Tasks` public surface and `TaskBus`                                       |
| TASK-M013 Explainable Commands          | COMPLETED | container, route, config and runtime doctor commands                       |
| TASK-M014 Kubernetes-Ready Probes       | COMPLETED | readiness and liveness flows                                               |
| TASK-M015 Runtime Isolation Guard       | COMPLETED | runtime isolation guard and check flow                                     |
| TASK-M016 Unified Storage Drivers       | COMPLETED | Redis/file driver boundaries across cache, session, queue and storage      |
| TASK-M017 Global Event Interceptors     | COMPLETED | event, audit, security and rate-limit integration surfaces                 |
| TASK-M018 Traceable Component Contracts | COMPLETED | timeline and observability hooks                                           |
| TASK-M019 Foundation Extraction         | COMPLETED | shared result/failure primitives and foundation lanes                      |
| TASK-M020 AvaX Protocol                 | COMPLETED | `Result`, `Failure`, `ResettableState`                                     |

---

## Naming Consolidation

| Task                                     | Status    | Evidence                                                                                       |
|------------------------------------------|-----------|------------------------------------------------------------------------------------------------|
| TASK-N001 Shortcuts                      | COMPLETED | Composer points to `System/PublicSurface/shortcuts.php`; missing Data shortcut target restored |
| TASK-N002 System Root Taxonomy           | COMPLETED | canonical `System/Capabilities`, `System/Flows`, `System/PublicSurface` paths present          |
| TASK-N003 Technical Category Refactoring | COMPLETED | runtime APIs isolated behind runtime capability guard                                          |
| TASK-N004 Global Structural Purge        | COMPLETED | canonical paths preserved with compatibility aliases for legacy entry points                   |
| TASK-N005 Component Sanitization Audit   | COMPLETED | touched and new code follows the local how-to structure                                        |

---

## Previous Recovery Tasks

| Task                           | Status    |
|--------------------------------|-----------|
| TASK-R001 Namespace Fix        | COMPLETED |
| TASK-R002 Boot Chain           | COMPLETED |
| TASK-R003 Test Suite           | COMPLETED |
| TASK-R004 CLI Entry            | COMPLETED |
| TASK-R005 Facades              | COMPLETED |
| TASK-R006 Dependencies         | COMPLETED |
| TASK-R007 Capabilities         | COMPLETED |
| TASK-R008 Integration Tests    | COMPLETED |
| TASK-R009 Error Handling       | COMPLETED |
| TASK-R010 Release Policy       | COMPLETED |
| TASK-R011 Dead Code            | COMPLETED |
| TASK-R012 Release Policy Final | COMPLETED |

---

## 🚧 Active Structural & Naming Audits (The Final Purge)

Iako su glavni taskovi "COMPLETED", sledeći propusti su detektovani tokom "Zero Tolerance" skeniranja arhitekture:

### 1. Root Directory Anarchy (Component Sanitization)

- [ ] **Identity/Auth**:
    - Obristati/premestiti skripte: `check.php`, `fix_test_imports.php`, `legacy-class-aliases.php`, `merge-files.sh`.
      Skripta merge-files.sh mora da ostane u root-u avax-a.
    - Očistiti artefakte: `Auth.txt` (3.3MB), `composer.phar`, `build/`.
    - Dokumentaciju (`REFAKTOR.md`, `complete-this.md`, `CHANGES_SUMMARY.txt`) preseliti negde u .agents folder.
    - Srediti `integrations/` folder (u `System/Configuration` ili `System/Capabilities`).
- [ ] **DataStack/Database**:
    - Pomeriti/obrisati ilegalne foldere: `Code-Review-And-ToDo/`, `Integrations/`.
    - Očistiti artefakte: `Database.txt`, `merge-files.sh`. Ostaviti merge-files.sh da postoji samo u root-u avax-a.
    - Obrisati duplikate PublicSurface fajlova iz root-a (`Database.php`, `EntityManager.php`, `Migrations.php`,
      `Query.php`, `Schema.php`, `Telemetry.php`, `Transactions.php`).
- [ ] **Application/Config**:
    - Preseliti `AuthConfig.php` i `Configurator/` u `System/Configuration` ili `System/Capabilities`.

### 2. The "Zero Tolerance" Naming Purge (Managers, Services, Support)

- [ ] **Manager Prekršaji**:
    - `WorkerManager` ➔ `Workers` ili `WorkerPool` (`WorkerManager/System/PublicSurface/WorkerManager.php`).
    - `ChannelManager` ➔ `Channels` ili `ChannelRegistry` (`Realtime/System/Capabilities/Channels/ChannelManager.php`).
    - `TransactionManager` ➔ `Transactions` (`Database/System/Capabilities/Transactions/TransactionManager.php`).
- [ ] **Service Prekršaji**:
    - `BuildService` ➔ `Builder`, `ResolveService` ➔ `Resolver`, `ExplainService` ➔ `Explanation` (u
      `Application/Container/`).
    - `ExplainContainerService` ➔ `ExplainContainerResolution` (u `framework/System/Flows/ExplainContainerService/`).
- [ ] **Zabranjeni `Support` folderi** (Preimenovati u imenice koje viču - "Screaming"):
    - `OAuth/Support/` ➔ `OAuth/Elements/`
    - `OpenIDConnect/Support/` ➔ `OpenIDConnect/Protocol/`
    - `SingleSignOn/FederationSupport/` ➔ `SingleSignOn/Federation/`
    - `Tenancy/AdminRealmSupport/` ➔ `Tenancy/AdminRealm/`
    - `IdentitySync/SCIM/Support/` ➔ `IdentitySync/SCIM/Directories/`
    - `Access/RiskBasedAccess/Support/` ➔ `Access/RiskBasedAccess/Signals/` ili `Metrics/`
    - `Database/.../Telemetry/Support/` ➔ `.../Telemetry/Trackers/` ili `Metrics/`
    - `Data/.../Internal/Support/` ➔ `.../Internal/Outcomes/` ili `Mechanics/`
- [ ] **Systemski "Service" Prekršaji**:
    - `ServiceProvider` klase (Cache, Database, Auth) ➔ **`Registrar`** (npr. `CacheRegistrar`, `DatabaseRegistrar` -
      jedinstveno pravilo za ceo AvaX).
    - `ServiceMap` komponenta ➔ `DependencyMap` ili `DependencyGraph`.
    - `MakeServiceCommand` i `ServiceGenerator` ➔ `MakeActionCommand` / `CapabilityGenerator`.
    - `StatefulServiceDetector` ➔ `StatefulDependencyDetector`.
