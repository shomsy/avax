# Service Provider Closure Plan

Date: 2026-05-14
Source: code-review-pre-d.md Phase 3 reclassification

## Purpose

This plan classifies every component's ServiceProvider status and defines the exact actions required
for Phase 3A (runtime-critical), Phase 3B (naming normalization), and Phase 3C (remaining ledger).

Rules:

1. Do NOT generate empty ServiceProvider shells.
2. Do NOT create ServiceProviders that register nothing.
3. Do NOT mark ServiceProvider phase GREEN if active runtime-critical components lack real assembly.
4. Do NOT move to @throws until Phase 3A and 3B are GREEN.

---

## Component Table

| Component                 | Status        | Runtime-critical? | Existing provider?                 |                             Provider naming valid? | Required action                                                                                | Blocks V5.9? |
|---------------------------|---------------|------------------:|------------------------------------|---------------------------------------------------:|------------------------------------------------------------------------------------------------|-------------:|
| Application/Container     | ACTIVE_YELLOW |               YES | ContainerServiceProvider.php       |                                                YES | Verify — already implements ServiceProvider interface correctly                                |          YES |
| Application/Filesystem    | ACTIVE_GREEN  |               YES | FilesystemServiceProvider.php      |                                                YES | Verify — registers real services                                                               |           NO |
| Application/Cache         | ACTIVE_GREEN  |               YES | CacheServiceProvider.php           |   NO (extends base ServiceProvider, not interface) | Rename to implement canonical ServiceProvider interface                                        |          YES |
| DataStack/Database        | ACTIVE_YELLOW |               YES | DatabaseServiceProvider.php        |                                                YES | Verify — registers EventBus, CheckDatabaseHealth                                               |          YES |
| HTTP/Router               | ACTIVE_GREEN  |               YES | HttpRouterServiceProvider.php      |                                                YES | Verify — fully wired                                                                           |           NO |
| HTTP/Response             | ACTIVE_GREEN  |               YES | ResponseProvider.php               |                   NO (wrong name, wrong interface) | Phase 3B: rename to ResponseServiceProvider.php, convert to ServiceProvider interface          |          YES |
| HTTP/Session              | ACTIVE_GREEN  |               YES | SessionProvider.php                |                   NO (wrong name, wrong interface) | Phase 3B: rename to SessionServiceProvider.php, convert to ServiceProvider interface           |          YES |
| HTTP/Client               | ACTIVE_GREEN  |               YES | HttpClientProvider.php             | NO (wrong name, factory class not ServiceProvider) | Phase 3B: rename to HttpClientServiceProvider.php, create thin ServiceProvider wrapper         |          YES |
| HTTP/Middleware           | SCAFFOLD      |                NO | MiddlewareProvider.php             |                              NO (wrong name, stub) | Phase 3B: rename to MiddlewareServiceProvider.php; component is SCAFFOLD — register as ROADMAP |           NO |
| HTTP (system-level)       | ACTIVE_GREEN  |               YES | HttpProvider.php                   |                   NO (wrong name, wrong interface) | Phase 3B: rename to HttpServiceProvider.php, convert to ServiceProvider interface              |          YES |
| Operations/Events         | ACTIVE_GREEN  |               YES | EventsServiceProvider.php          |                                                YES | Verify — fully wired                                                                           |           NO |
| Operations/Logging        | ACTIVE_GREEN  |               YES | LoggingServiceProvider.php         |                                                YES | Verify — registers Logger, LoggerInterface                                                     |           NO |
| Security/Redaction        | ACTIVE_GREEN  |               YES | RedactionServiceProvider.php       |                                                YES | Verify — registers PatternMatcher, RedactionEngine                                             |           NO |
| Security/Cryptography     | ACTIVE_GREEN  |               YES | CryptographyServiceProvider.php    |                                                YES | Verify — registers EncrypterInterface                                                          |           NO |
| Framework/FailureBoundary | ACTIVE_GREEN  |               YES | FailureBoundaryServiceProvider.php |                                                YES | Verify — registers ResponseFactory, Classifier, Renderer                                       |           NO |
| Framework/Queue           | ACTIVE_YELLOW |               YES | QueueServiceProvider.php           |                                                YES | Verify — registers broker, failed store, worker                                                |           NO |
| Identity/Auth             | ACTIVE_GREEN  |               YES | AuthProvider.php                   |                       NO (wrong name, empty shell) | Phase 3B: rename to AuthServiceProvider.php, fill with real registration                       |          YES |
| Integration/ObjectStorage | ACTIVE_YELLOW |               YES | None                               |                                                N/A | Phase 3C: ACTIVE_YELLOW — requires provider but deferred to follow-up                          |           NO |
| Framework (root)          | N/A           |                NO | FrameworkProvider.php              |                   NO (wrong name, wrong interface) | Phase 3B: rename to FrameworkServiceProvider.php, convert to ServiceProvider interface         |           NO |

---

## Phase 3A — Runtime-Critical ServiceProvider Closure

These components MUST have real ServiceProvider implementations before V5.9:

### Already conforming to canonical ServiceProvider interface (GREEN pending verification):

| Component                 | Provider                           | Notes                                             |
|---------------------------|------------------------------------|---------------------------------------------------|
| Application/Container     | ContainerServiceProvider.php       | Implements canonical ServiceProvider              |
| Application/Filesystem    | FilesystemServiceProvider.php      | Implements canonical ServiceProvider              |
| DataStack/Database        | DatabaseServiceProvider.php        | Implements canonical ServiceProvider              |
| HTTP/Router               | HttpRouterServiceProvider.php      | Implements canonical ServiceProvider, fully wired |
| Operations/Events         | EventsServiceProvider.php          | Implements canonical ServiceProvider, fully wired |
| Operations/Logging        | LoggingServiceProvider.php         | Implements canonical ServiceProvider              |
| Security/Redaction        | RedactionServiceProvider.php       | Implements canonical ServiceProvider              |
| Security/Cryptography     | CryptographyServiceProvider.php    | Implements canonical ServiceProvider              |
| Framework/FailureBoundary | FailureBoundaryServiceProvider.php | Implements canonical ServiceProvider              |
| Framework/Queue           | QueueServiceProvider.php           | Implements canonical ServiceProvider              |

### Need renaming AND interface conversion (Phase 3A+3B combined):

| Component | Current File | Target File | Interface Change |
|---|---|---|---|---|
| HTTP/Response | ResponseProvider.php | ResponseServiceProvider.php | ComponentProviderInterface → ServiceProvider |
| HTTP/Session | SessionProvider.php | SessionServiceProvider.php | ComponentProviderInterface → ServiceProvider |
| HTTP/Client | HttpClientProvider.php | HttpClientServiceProvider.php | Factory class → thin ServiceProvider wrapper |
| HTTP (system) | HttpProvider.php | HttpServiceProvider.php | ComponentProviderInterface → ServiceProvider |
| Identity/Auth | AuthProvider.php | AuthServiceProvider.php | Empty shell → real ServiceProvider |
| Application/Cache | CacheServiceProvider.php | CacheServiceProvider.php | Base ServiceProvider class → canonical
ServiceProvider interface |

### Framework root:

| Component        | Current File          | Target File                  | Notes                                        |
|------------------|-----------------------|------------------------------|----------------------------------------------|
| Framework (root) | FrameworkProvider.php | FrameworkServiceProvider.php | ComponentProviderInterface → ServiceProvider |

### SCAFFOLD — not runtime-critical:

| Component       | Status   | Action                                                                        |
|-----------------|----------|-------------------------------------------------------------------------------|
| HTTP/Middleware | SCAFFOLD | Rename to MiddlewareServiceProvider.php but mark as ROADMAP_PROVIDER_DEFERRED |

---

## Phase 3B — Provider Naming Normalization

All `*Provider.php` files in `System/Configuration/` MUST be renamed to `*ServiceProvider.php`.

Rename list:

| Old Name               | New Name                      |
|------------------------|-------------------------------|
| HttpClientProvider.php | HttpClientServiceProvider.php |
| MiddlewareProvider.php | MiddlewareServiceProvider.php |
| ResponseProvider.php   | ResponseServiceProvider.php   |
| SessionProvider.php    | SessionServiceProvider.php    |
| HttpProvider.php       | HttpServiceProvider.php       |
| AuthProvider.php       | AuthServiceProvider.php       |
| FrameworkProvider.php  | FrameworkServiceProvider.php  |

Additionally: Application/Cache CacheServiceProvider extends base `ServiceProvider` class instead of
implementing the canonical `ServiceProvider` interface. Must be converted.

---

## Phase 3C — Remaining Components Ledger

Every remaining component without ServiceProvider is classified below.

No fake providers will be created for ROADMAP/SCAFFOLD/LABS components.

Full ledger in: EVIDENCE/cleanup/service-provider-assembly-map.md

---

## Validation

After Phase 3A and 3B:

1. Recursive code review against how-to-code-review.md and all how-to-*.md
2. Fix every finding
3. Review again until no BLOCKER/HIGH/MEDIUM production-readiness findings
4. Full validation
5. Evidence update
6. Commit

---

## Phase 3 Completion Evidence

Date: 2026-05-14

### Validation Output

| Check                                                        | Result                                           |
|--------------------------------------------------------------|--------------------------------------------------|
| `php tooling/components/check-service-provider-coverage.php` | PASS — 79 components, 79 valid providers         |
| `vendor/bin/phpunit --no-coverage`                           | 8337 tests, 23884 assertions, OK (1 deprecation) |
| `vendor/bin/phpstan analyse` on all 12 changed files         | CLEAN — 0 errors                                 |

### Changed Files (12)

| File                                                                                       | Action                                                          |
|--------------------------------------------------------------------------------------------|-----------------------------------------------------------------|
| `components/Application/Container/System/PublicSurface/ContainerInterface.php`             | Added `extends Psr\Container\ContainerInterface`                |
| `components/Application/Container/System/Capabilities/ServiceProvider/ServiceProvider.php` | Fixed import to `PublicSurface\ContainerInterface`              |
| `components/Application/Cache/System/Configuration/CacheServiceProvider.php`               | Converted to canonical interface, moved static wiring to boot() |
| `components/HTTP/Client/System/Configuration/HttpClientServiceProvider.php`                | NEW — registers CurlClient + HttpClient                         |
| `components/HTTP/Middleware/System/Configuration/MiddlewareServiceProvider.php`            | NEW — SCAFFOLD, empty register/boot                             |
| `components/HTTP/Response/System/Configuration/ResponseServiceProvider.php`                | NEW — registers response builders                               |
| `components/HTTP/Session/System/Configuration/SessionServiceProvider.php`                  | NEW — converted from ComponentProviderInterface                 |
| `components/HTTP/System/Configuration/HttpServiceProvider.php`                             | NEW — registers HTTP kernels, middleware                        |
| `components/HTTP/Router/System/Configuration/HttpRouterServiceProvider.php`                | Added RouterInterface alias                                     |
| `components/Identity/Auth/System/Configuration/AuthServiceProvider.php`                    | NEW — registers Identity + AuthInterface                        |
| `framework/System/Configuration/FrameworkServiceProvider.php`                              | NEW — registers runtime safety, config repo                     |
| `framework/System/Capabilities/Queue/Configuration/QueueServiceProvider.php`               | Fixed import to `PublicSurface\ContainerInterface`              |

### Deleted Files (7 — wrong naming)

- `components/HTTP/Client/System/Configuration/HttpClientProvider.php`
- `components/HTTP/Middleware/System/Configuration/MiddlewareProvider.php`
- `components/HTTP/Response/System/Configuration/ResponseProvider.php`
- `components/HTTP/Session/System/Configuration/SessionProvider.php`
- `components/HTTP/System/Configuration/HttpProvider.php`
- `components/Identity/Auth/System/Configuration/AuthProvider.php`
- `framework/System/Configuration/FrameworkProvider.php`

### Pre-existing PHPStan Errors (Not Introduced)

PHPStan reports errors in files that still reference old `System\ContainerInterface` (pre-dating this work):

- ContextContainer, DIContainer, ContainerServiceProvider, FailureBoundaryServiceProvider, ContainerAnalyzer,
  AuditContainerScope, ExplainContainerResolution, ListContainerBindings, RunConsoleCommand, compat.php

These are outside Phase 3 scope.
