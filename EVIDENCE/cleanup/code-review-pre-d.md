# Code Review: Phases 0→D — How-To Rule Violations

**Date:** 2026-05-14
**Scope:** Phases 0, B, C, D
**Reviewer:** Automated governance scan
**Source of Truth:** `.agents/how-to/*.md`, `AGENTS.md`

---

## Table of Contents

1. [DI Violations (how-to-dependency-injection.md)](#1-di-violations)
2. [Naming Violations (AGENTS.md + how-to-architecture.md)](#2-naming-violations)
3. [Folder Structure Violations (how-to-design-components.md)](#3-folder-structure-violations)
4. [Code Style Violations (how-to-code-style.md)](#4-code-style-violations)
5. [Missing @throws (how-to-code-style.md)](#5-missing-throws)
6. [Missing ServiceProviders (how-to-dependency-injection.md)](#6-missing-serviceproviders)
8. [Phase D: PublicSurface Violations](#8-phase-d-publicsurface-violations)
9. [Summary](#9-summary)

---

## 1. DI Violations

**Rule source:** `how-to-dependency-injection.md`
**Core rule:** `new Class()` FORBIDDEN outside composition root/factory/test.
**Core rule:** `?? new Fallback()` FORBIDDEN — register fallbacks in ServiceProvider.
**Severity:** BLOCKER / HIGH

### 1.1 `?? new` Fallback Pattern (13 occurences, BLOCKER)

| # | File | Line | Code | Fallback | Fix |
|---|------|------|------|----------|-----|
| 1 | `framework/System/Capabilities/Runtime/Runtime.php` | 101 | `$this->handleIncomingHttp ?? new HandleIncomingHttp(...)` | HandleIncomingHttp + nested ResponseFactory | Inject HandleIncomingHttp through constructor (partially done, fallback remains) |
| 2 | `framework/System/Capabilities/PreCommit/PreCommit.php` | 56 | `$this->preCommitConfig ?? new PreCommitConfig()` | PreCommitConfig | Make required constructor param |
| 3 | `framework/System/Capabilities/TracingTimeline/Tracing.php` | 48 | `$this->runtimeTimeline?->begin(...) ?? new TraceSpan(...)` | TraceSpan | Inject TraceSpan factory via constructor |
| 4-7 | `framework/System/Flows/BootApplication/BuildApplicationState.php` | 26-29 | `$this->xxx ?? new Xxx()` ×4 | ComponentRegistry, RequestScopeStore, RuntimeContext, StateResetRegistry | Make all 4 required constructor params |
| 8-13 | `framework/System/Flows/CreateApplication/CreateApplication.php` | 55-70 | `$this->xxx ?? new Xxx()` ×6 | ProjectPath, ComponentRegistry, RequestScopeStore, RuntimeContext, StateResetRegistry, ResponseFactory | Make all 6 required constructor params |

### 1.2 `= new ClassName()` Constructor Defaults (14 occurences, HIGH)

| # | File | Line | Code | Fix |
|---|------|------|------|-----|
| 1 | `framework/System/Capabilities/Runtime/Cli/CliRuntime.php` | 14 | `private CliInputReader $cliInputReader = new CliInputReader()` | Required constructor param |
| 2 | `framework/System/Capabilities/Runtime/Cli/CliRuntime.php` | 15 | `private CliOutputWriter $cliOutputWriter = new CliOutputWriter()` | Required constructor param |
| 3 | `framework/System/Capabilities/Runtime/PhpFpm/PhpFpmRuntime.php` | 15 | `private PhpFpmRequestReader $phpFpmRequestReader = new PhpFpmRequestReader()` | Required constructor param |
| 4 | `framework/System/Capabilities/Runtime/PhpFpm/PhpFpmRuntime.php` | 16 | `private PhpFpmResponseSender $phpFpmResponseSender = new PhpFpmResponseSender()` | Required constructor param |
| 5 | `framework/System/Capabilities/Security/RequestSigning/VerifyInternalRequestSignature.php` | 16 | `private NonceStore $nonceStore = new NonceStore()` | Required constructor param |
| 6 | `framework/System/Configuration/BuildApplication/ApplicationBuilder.php` | 42 | `private Clock $clock = new SystemClock()` | Required constructor param or inject via ServiceProvider |
| 7 | `framework/System/Capabilities/FailureBoundary/Capabilities/CleanupAfterFailure/CleanupAfterFailure.php` | 24 | `private FailureCleanupRegistry $registry = new FailureCleanupRegistry()` | Required constructor param |
| 8-9 | `components/HTTP/System/Capabilities/Kernel/HttpKernel.php` | 28-29 | `private readonly BootHttpKernel $bootHttpKernel = new BootHttpKernel()`, `private readonly TerminateHttpKernel $terminateHttpKernel = new TerminateHttpKernel()` | Required constructor params |
| 10 | `components/SystemDesign/System/PublicSurface/SystemDesignKit.php` | 49 | `private Filesystem $filesystem = new Filesystem()` | Required constructor param |
| 11-12 | `framework/System/Capabilities/Security/PolicyEngine/EvaluatePolicy.php` | 27, 51 | `PolicyContext $context = new PolicyContext()` (×2, method params) | Remove defaults, require PolicyContext at call site |
| 13 | `framework/System/Configuration/BuildApplication/ApplicationBuilder.php` | 49 | `$runDoctor = new RunDoctor()` (inside constructor closure) | Inject via constructor |
| 14 | `framework/System/Flows/RunConsoleCommand/RunConsoleCommand.php` | 152 | `$config = new PreCommitConfig()` (inside method) | Inject via constructor |

### 1.3 Static Mutable State Bypassing DI (MEDIUM)

| # | File | Line | Class/Method | Violation | Fix |
|---|------|------|-------------|-----------|-----|
| 1 | `components/Security/System/PublicSurface/Security.php` | 114-121 | `auditLog(): SecurityAuditLog` | `self::$securityAuditLog ??= new SecurityAuditLog()` — static state with lazy `new` | Convert to instance DI, register in ServiceProvider |

---

## 2. Naming Violations

**Rule source:** `AGENTS.md` Section 8, `how-to-architecture.md` Section 22
**Core rule:** `Manager`, `Service`, `Handler`, `Helper`, `Utils` FORBIDDEN as file/class names.
**Severity:** HIGH

### 2.1 `*Manager.php` Files (5 violations, HIGH)

| # | File | Class | Suggested Fix |
|---|------|-------|---------------|
| 1 | `framework/System/Capabilities/Runtime/StateResetManager.php` | `StateResetManager` | Rename to `ResetApplicationState` |
| 2 | `components/Security/DataProtection/System/Capabilities/KeyManager/KeyManager.php` | `KeyManager` | Rename to `RotateEncryptionKeys` or `ManageKeyStore` |
| 3 | `components/Security/Privacy/System/Capabilities/RetentionPolicyManager/RetentionPolicyManager.php` | `RetentionPolicyManager` | Rename to `EnforceRetentionPolicy` |
| 4 | `components/DataStack/Database/System/Capabilities/ORM/EntityManager.php` | `EntityManager` | Rename to `ManageEntityPersistence` (or governance exception — Doctrine pattern) |
| 5 | `components/DataStack/Database/System/PublicSurface/EntityManager.php` | `EntityManager` | Same as above |

### 2.2 `*Service.php` Files (5 violations, HIGH)

| # | File | Class | Suggested Fix |
|---|------|-------|---------------|
| 1 | `framework/System/Flows/ExplainContainerService/ExplainContainerService.php` | `ExplainContainerService` | Rename folder+file to `DescribeContainerService` |
| 2 | `components/Security/DataProtection/System/Capabilities/EncryptionService/EncryptionService.php` | `EncryptionService` | Rename to `EncryptData` |
| 3 | `components/Application/Container/System/Capabilities/Execution/BuildService.php` | `BuildService` | Rename to `AssembleContainerServices` |
| 4 | `components/Application/Container/System/Flows/ExplainService/ExplainService.php` | `ExplainService` | Rename to `DescribeContainerService` |
| 5 | `components/Application/Container/System/Flows/ResolveService/ResolveService.php` | `ResolveService` | Rename to `ResolveContainerService` |

### 2.3 `*Handler.php` Files (7 violations, HIGH)

| # | File | Class | Suggested Fix |
|---|------|-------|---------------|
| 1 | `framework/System/Flows/HandleIncomingHttp/ConfiguredRoutesHttpHandler.php` | `ConfiguredRoutesHttpHandler` | Rename to `DispatchConfiguredRoute` |
| 2 | `components/API/ApiBlueprint/System/Capabilities/RestApi/SortHandler.php` | `SortHandler` | Rename to `ApplySortParameter` |
| 3 | `components/API/ApiBlueprint/System/Capabilities/RestApi/PaginationHandler.php` | `PaginationHandler` | Rename to `ApplyPagination` |
| 4 | `components/API/ApiBlueprint/System/Capabilities/RestApi/FilterHandler.php` | `FilterHandler` | Rename to `ApplyFilterParameter` |
| 5 | `components/Operations/Queue/System/Capabilities/Job/JobHandler.php` | `JobHandler` | Rename to `ExecuteQueuedJob` |
| 6 | `components/Operations/Logging/System/Capabilities/ErrorHandling/GlobalErrorHandler.php` | `GlobalErrorHandler` | Rename to `CaptureUnhandledErrors` |
| 7 | `components/Operations/Logging/System/Capabilities/ErrorHandling/ShutdownErrorHandler.php` | `ShutdownErrorHandler` | Rename to `CaptureShutdownFailures` |

---

## 3. Folder Structure Violations

**Rule source:** `how-to-design-components.md` Sections 6-9
**Severity:** HIGH / MEDIUM

### 3.1 Forbidden Folder `Diagnostics` in `framework/System/Capabilities/` (HIGH)

**Path:** `framework/System/Capabilities/Diagnostics/`
**Contents:** `RuntimeTimeline.php`, `CorrelationId.php`, `DiagnosticContext.php`, `RuntimeEvent.php`, `Diagnostics.php`, `TraceId.php`
**Rule:** Section 6.7 — `Diagnostics` is FORBIDDEN as a default top-level System folder. Section 6.12 — Diagnostics are capabilities, not a mandatory root folder.
**Fix:** Rename to exact diagnostic capabilities, e.g.:
- `Diagnostics/` → `Capabilities/RuntimeTimeline/`
- `Capabilities/DiagnosticContext/` (split by responsibility)

### 3.2 Testing Folders Inside System/ (MEDIUM)

| # | Path | Rule | Fix |
|---|------|------|-----|
| 1 | `components/HTTP/Client/System/Capabilities/Testing/` | Section 6.13 — Tests FORBIDDEN inside System/ | Move to central `tests/` tree |
| 2 | `components/Application/Container/System/Capabilities/Composition/Testing/` | Same | Move to central `tests/` tree |

### 3.3 TestingFakes Folder (MEDIUM)

**Path:** `framework/System/Capabilities/TestingFakes/`
**Rule:** Section 6.7 — Concept word as folder name. "Testing" is equivalent to forbidden "Tests".
**Fix:** Move fakes to their owning component or to `tests/Support/Fakes/`.

### 3.4 Non-Canonical `Runtime/` Inside `framework/System/` (HIGH)

**Path:** `framework/System/Runtime/`
**Subdirs:** `MemoryGuard/`, `ReactPhp/`, `WarmApplication/`
**Rule:** Section 6 — Only `PublicSurface/`, `Flows/`, `Capabilities/`, `Configuration/`, `Foundation/` allowed as top-level System/ folders.
**Fix:** Move all content into `framework/System/Capabilities/Runtime/` — it IS a runtime capability.

### 3.5 PHP Files Directly in System/ Root (14 files, 5 components, HIGH)

**Rule:** Section 6 — Files must live inside canonical subdirectories.

| Component | Files | Suggested Target |
|-----------|-------|-----------------|
| `Application/Cache/System/` | `AvaxCache.php`, `CacheContract.php`, `CacheFailure.php`, `CacheResult.php`, `CacheResultState.php`, `CompiledCache.php` | PublicSurface/ or Foundation/ |
| `Application/Container/System/` | `Container.php`, `ContainerInterface.php`, `ContextContainer.php` | PublicSurface/ or Capabilities/ |
| `DataStack/Database/System/` | `Database.php`, `DatabaseInterface.php` | PublicSurface/ |
| `HTTP/Request/System/` | `Request.php` | PublicSurface/ or Capabilities/ |
| `Identity/Auth/System/` | `Auth.php`, `DefaultAuth.php` | PublicSurface/ or Capabilities/ |

### 3.6 Component Name on Forbidden List — No Exception (MEDIUM)

| Component | Forbidden Word | Fix |
|-----------|---------------|-----|
| `components/DeveloperTools/Testing/` | `Testing` (equivalent to forbidden `Tests`) | Rename to e.g. `DeveloperTools/TestSupport` or file governance exception |

### 3.7 Governance Exceptions Already Filed (ACCEPTED)

| Component | Exception | Expiry |
|-----------|-----------|--------|
| `components/API/Contracts` | GE-001 | V5.9 review |
| `components/DeveloperTools/Diagnostics` | GE-002 | V5.9 review |
| `components/Operations/Events` | GE-003 | V5.9 review |

---

## 4. Code Style Violations

**Rule source:** `how-to-code-style.md`
**Severity:** HIGH

### 4.1 `?Type` Nullable Format (23 violations, HIGH)

**Rule:** Nullable types MUST be `Type|null`, NOT `?Type`.

#### In `framework/System/`:

| # | File | Line | Current | Should Be |
|---|------|------|---------|-----------|
| 1 | `framework/System/Capabilities/FailureBoundary/.../CompileFailurePolicies.php` | 78 | `): ?CompiledMethodPolicy` | `): CompiledMethodPolicy\|null` |
| 2 | `framework/System/Capabilities/FailureBoundary/.../ReadCompiledFailurePolicies.php` | 19 | `): ?CompiledMethodPolicy` | `): CompiledMethodPolicy\|null` |
| 3 | `framework/System/Capabilities/FailureBoundary/Foundation/CompiledPolicyCache.php` | 18 | `): ?CompiledMethodPolicy` | `): CompiledMethodPolicy\|null` |
| 4 | `framework/System/Capabilities/FailureBoundary/Foundation/FailurePolicy.php` | 56 | `): ?FailureAction` | `): FailureAction\|null` |

#### In `components/HTTP/System/`:

| # | File | Line | Current | Should Be |
|---|------|------|---------|-----------|
| 5 | `components/HTTP/System/Capabilities/Uri/Uri.php` | 37 | `: ?int` | `: int\|null` |
| 6 | `components/HTTP/System/Capabilities/Enums/HttpReasonPhrase.php` | 34 | `: ?self` | `: self\|null` |
| 7 | `components/HTTP/System/Capabilities/Headers/ResponseHeaders.php` | 29 | `: ?array` | `: array\|null` |
| 8 | `components/HTTP/System/Foundation/Values/HttpReasonPhrase.php` | 157 | `: ?string` | `: string\|null` |
| 9 | `components/HTTP/System/Foundation/Values/HeaderName.php` | 135 | `: ?self` | `: self\|null` |
| 10 | `components/HTTP/System/Foundation/Values/RequestOption.php` | 62 | `: ?self` | `: self\|null` |
| 11 | `components/HTTP/System/Foundation/Values/HttpMethod.php` | 26 | `: ?self` | `: self\|null` |
| 12 | `components/HTTP/System/Foundation/Values/HttpStatusCode.php` | 81 | `: ?self` | `: self\|null` |
| 13 | `components/HTTP/System/Foundation/Values/ContentType.php` | 74 | `: ?self` | `: self\|null` |
| 14 | `components/HTTP/System/Foundation/Values/ContentType.php` | 134 | `: ?string` | `: string\|null` |
| 15 | `components/HTTP/System/PublicSurface/Request.php` | 32 | `: ?string` | `: string\|null` |

#### In `components/SystemDesign/`:

| # | File | Line | Current | Should Be |
|---|------|------|---------|-----------|
| 16-22 | `components/SystemDesign/System/Capabilities/FailureSimulation/FailureSimulation.php` | 77, 93, 123, 153, 181, 213, 231 | `private function ...(): ?array` (×7) | `private function ...(): array\|null` |
| 23 | `components/SystemDesign/System/Capabilities/SchemaValidation/NativeYamlParser.php` | 273 | `: ?int` | `: int\|null` |
| 24 | `framework/System/Capabilities/FailureBoundary/Capabilities/EnforceTimeout/EnforceTimeout.php` | 24 | `?int $timeoutMs` | `int\|null $timeoutMs` |

---

## 5. Missing @throws Documentation

**Rule source:** `how-to-code-style.md` — Methods that throw MUST document with `@throws`.
**Severity:** MEDIUM
**Total:** ~781 across ~400 files

### 5.1 In `framework/System/` (~85 missing)

Key examples:

| File | Throws | Missing @throws |
|------|--------|-----------------|
| `RuntimeContext.php:27` `startRequest()` | `FrameworkMisconfigured` | Yes |
| `RequestScopeStore.php:21` `open()` | `FrameworkMisconfigured` | Yes |
| `RequestScopeStore.php:37` `current()` | `RequestScopeNotOpen` | Yes |
| `ComponentRegistry.php:31` `register()` | `FrameworkMisconfigured` | Yes |
| `EvaluatePolicy.php:56` `evaluateOrFail()` | `PolicyDeniedException` | Yes |
| `LoadCachedRoutes.php:29` `load()` | `RouteCacheFailed` | Yes |
| `Server.php:62` `findRouterFile()` | `RuntimeException` | Yes |
| `App.php:190` `run()` | unspecified | Yes |
| Various `ConvertPhpErrorToThrowable.php` | `PhpErrorException`, `ErrorException` | Yes |
| Various `Foundation/Paths/*.php:18-22` | `RuntimeException` | Yes |

### 5.2 In `components/*/System/` (~696 missing)

Areas most affected:
- `DataStack/Database/` (~100)
- `Identity/Auth/Credentials/Tokens/` (~100)
- `Operations/Queue/Saga/MessageBus/Scheduler/` (~150)
- `SystemDesign/System/` (~80)
- `HTTP/*` (~60)
- `Security/Cryptography/DataProtection/Privacy` (~40)

**Fix:** For each `throw new` statement, add `@throws` to the method's PHPDoc block.

---

## 6. Missing ServiceProviders

**Rule source:** `how-to-dependency-injection.md` Section 4.1 — "Every component MUST have exactly one ServiceProvider."
**Severity:** HIGH

### 6.1 Components WITH ServiceProvider (conforming naming) — 4

| Component | Path |
|-----------|------|
| Application/Cache | `System/Configuration/CacheServiceProvider.php` |
| HTTP/Router | `System/Configuration/HttpRouterServiceProvider.php` |
| [framework] FailureBoundary | `framework/System/Capabilities/FailureBoundary/Configuration/FailureBoundaryServiceProvider.php` |
| [framework] Queue | `framework/System/Capabilities/Queue/Configuration/QueueServiceProvider.php` |

### 6.2 Components WITH Provider but WRONG naming (7, MEDIUM)

Should be `*ServiceProvider.php`, not `*Provider.php`:

| # | Actual Path | Expected Name |
|---|-------------|---------------|
| 1 | `HTTP/Client/.../HttpClientProvider.php` | `HttpClientServiceProvider.php` |
| 2 | `HTTP/Middleware/.../MiddlewareProvider.php` | `MiddlewareServiceProvider.php` |
| 3 | `HTTP/Response/.../ResponseProvider.php` | `ResponseServiceProvider.php` |
| 4 | `HTTP/Session/.../SessionProvider.php` | `SessionServiceProvider.php` |
| 5 | `HTTP/System/.../HttpProvider.php` | `HttpServiceProvider.php` |
| 6 | `Identity/Auth/.../AuthProvider.php` | `AuthServiceProvider.php` |
| 7 | `framework/.../FrameworkProvider.php` | `FrameworkServiceProvider.php` |

### 6.3 Components MISSING ServiceProvider (76 components, HIGH)

Full list by area:

| Area | Missing Components |
|------|-------------------|
| **API** | ApiBlueprint, Contracts, GraphQL, OpenAPI, SchemaGeneration (5) |
| **Application** | Config, Container, DateTime, Facade, FeatureFlags, Filesystem, Localization, Pipeline, Storage, Text, Validation (11) |
| **CLI** | Console, System (2) |
| **DataStack** | Data, DataTransfer, Database, Persistence (4) |
| **DeveloperTools** | CodeGeneration, Diagnostics, Documentation, DumpDebugger, Dx, System, Testing (7) |
| **Foundation** | CallableSerialization (1) |
| **HTTP** | AfterResponse, ApiVersioning, ContentNegotiation, Context, Dispatcher, Request, SecureRequest, Security, URI (9) |
| **Identity** | Access, Credentials, ExternalIdentity, Security, System, Tenancy, Tokens (7) |
| **Integration** | ObjectStorage (1) |
| **Operations** | ApplicationWorkflow, BackgroundProcesses, Concurrency, Delivery, Events, Filesystem, Logging, Mail, MemoryLifecycle, MessageBus, Notifications, Observability, Parallelism, Queue, Realtime, Resilience, RuntimeSupervision, Scheduler, System, Tasks (20) |
| **Presentation** | System, View (2) |
| **Security** | Cryptography, DataProtection, Hashing, Privacy, Redaction, Secrets, System (7) |
| **SystemDesign** | System, examples, reference-architectures, schemas (4) |

**Fix:** Create `System/Configuration/<ComponentName>ServiceProvider.php` for each missing component. At minimum for runtime-critical components: Database, Events, Logging, Container, Filesystem.

---

## 8. Phase D: PublicSurface Violations

**Rule source:** `how-to-design-components.md` Sections 6.2, 6.7, 23 + `how-to-clean-code.md` Section 5.4
**Core rule:** `PublicSurface/` RECEIVES and DELEGATES. Must NOT contain business logic, loops, match, orchestration, or
implementation details.

## 9. Summary

### Severity Breakdown

| Severity    | Count                                                                                                                                                                              | Category                                      |
|-------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-----------------------------------------------|
| **BLOCKER** | 13                                                                                                                                                                                 | `?? new` fallback anti-pattern                |
| **HIGH**    | 19 files + 14 defaults + 5 naming + 5 naming + 7 naming + 1 folder + 1 non-canonical + 14 orphan files + 76 missing providers + 23 ?Type + 1605 array types + 3 Phase D violations | Mixed DI/naming/structure/style/PublicSurface |
| **MEDIUM**  | 781 @throws + 2 Testing folders + 1 TestingFakes + 1 component name + 7 provider naming + 2 Phase D violations                                                                     | Documentation/structure/PublicSurface         |
| **LOW**     | 1 empty `framework/Foundation/` + Phase D markers                                                                                                                                  | Cleanup                                       |

| Priority | Fix                                                                  | Impact                            |
|----------|----------------------------------------------------------------------|-----------------------------------|
| 1        | Remove all 13 `?? new` fallback patterns                             | BLOCKER — DI integrity            |
| 2        | Remove all 14 `= new ClassName()` defaults                           | HIGH — DI hygiene                 |
| 3        | Create ServiceProviders for runtime-critical components missing them | HIGH — architectural completeness |
| 4        | Fix `framework/System/Runtime/` → move to `Capabilities/Runtime/`    | HIGH — canonical shape            |
| 5        | Move 14 orphan System/ root files into canonical subdirs             | HIGH — filesystem purity          |
| 6        | Rename 17 Manager/Service/Handler files                              | HIGH — naming law                 |
| 7        | Fix 23 `?Type` → `Type                                               | null`                             | HIGH — code style |
| 8        | Relocate/fix `framework/System/Capabilities/Diagnostics/`            | HIGH — forbidden folder           |
| 9        | Add `@throws` for 781 missing (start with framework/ first)          | MEDIUM — documentation            |
| 10       | Add array type PHPDoc for 1605 bare `array` returns                  | HIGH — type safety                |
| **D-1**  | **Extract Router.php business logic into Capabilities/**             | **HIGH** — D-B.01                 |
| **D-2**  | **Extract GraphQLSchema.php logic into Capabilities/**               | **MEDIUM** — D-B.02               |
| **D-3**  | **Extract Workflow.php saga logic + fix DI bypass**                  | **MEDIUM** — D-B.03               |

---

### 7.1 Hollow / No-Op PublicSurface Files (D-A)

Files that exist as PublicSurface classes but add zero or near-zero behavioral value.

#### D-A.01 ✅ `SchemaBuilder::hasTable()` — DELETED

**Status:** FIXED — file no longer exists.

#### D-A.02 ✅ `Testing::verifyContracts()` — FIXED

**Status:** FIXED — now delegates to `ContractVerifier` capability instead of returning input.

#### D-A.03/04/05 — `Command`, `DomainEvent`, `Query` marker interfaces (ACCEPTED)

| File                                                                    | Rule                               | Severity | Assessment                                                                                                                                                        |
|-------------------------------------------------------------------------|------------------------------------|----------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `components/Operations/MessageBus/System/PublicSurface/Command.php`     | Section 6.2 — hollow PublicSurface | LOW      | **ACCEPTED** — documented marker interface with PHPDoc explaining purpose. Per `how-to-design-components.md` §23, marker interfaces are allowed in PublicSurface. |
| `components/Operations/MessageBus/System/PublicSurface/DomainEvent.php` | Same                               | LOW      | Same — documented marker                                                                                                                                          |
| `components/Operations/MessageBus/System/PublicSurface/Query.php`       | Same                               | LOW      | Same — documented marker                                                                                                                                          |

These are accepted by the independent review as "intentional design" (see review
`29-independent-whole-system-review.md`).

#### D-A.06 ✅ `ResponseInterface` — ACCEPTED

**File:** `components/HTTP/Response/System/PublicSurface/ResponseInterface.php`
**Rule:** Section 6.2 — hollow PublicSurface
**Severity:** LOW
**Assessment:** **ACCEPTED** — extends PSR-7 `ResponseInterface` with a docblock explaining purpose: "decouple internal
AvaX code from direct PSR-7 dependency at the boundary." This IS behavioral: it provides a framework-level type alias.

#### D-A.07 ✅ `CacheReadTarget` — DELETED

**Status:** FIXED — file no longer exists.

#### D-A.08/09/10/11 — Cache re-exports — DELETED

| File                                                 | Status    |
|------------------------------------------------------|-----------|
| `Cache/System/PublicSurface/CacheFacade.php`         | ✅ DELETED |
| `Cache/System/PublicSurface/CacheRegistry.php`       | ✅ DELETED |
| `Cache/System/PublicSurface/CompiledCacheTarget.php` | ✅ DELETED |
| `Cache/System/PublicSurface/ReadFromCache.php`       | ✅ DELETED |

#### D-A.12 ❌ `Cache/System/PublicSurface/Read/RuntimeCacheTarget.php`

**File:** `components/Application/Cache/System/PublicSurface/Read/RuntimeCacheTarget.php`
**Class:** `RuntimeCacheTarget`
**Rule:** Section 6.2 — hollow PublicSurface (class has only `__construct` + `kind()` method returning enum + static
factory)
**Severity:** LOW
**Assessment:** This is a thin DTO/value object. While it's minimal, it IS a legitimate value object (target
specification for cache reads). Per Section 23, public value objects are allowed in PublicSurface. Not a violation.

#### D-A.12 ❌ `Cache/System/PublicSurface/Read/CompiledCacheTarget.php`

**File:** `components/Application/Cache/System/PublicSurface/Read/CompiledCacheTarget.php`
**Class:** `CompiledCacheTarget`
**Same assessment as above** — legitimate value object. Not a violation.

#### D-A.13 ✅ `Middleware.php` — DELETED

**Status:** FIXED — file no longer exists.

---

### 7.2 PublicSurface Contains Business Logic (D-B) ❌

**Rule:** `how-to-design-components.md` Section 6.2 — FORBIDDEN: "business logic, runtime machinery, flow
implementation" in PublicSurface. PublicSurface RECEIVES and DELEGATES.

#### D-B.01 ❌ `Router.php` — MAJOR VIOLATION (HIGH)

**File:** `components/HTTP/Router/System/PublicSurface/Router.php` (300 lines)
**Class:** `Router`
**Rules violated:**

1. **Section 6.2** — PublicSurface must NOT contain:
    - `preg_replace_callback` for URL parameter substitution (lines 145-158) — business logic
    - `foreach` + `preg_match` for query string building (lines 162-173) — business logic
    - Middleware pipeline construction with `foreach` + closures (lines 234-251) — orchestration logic
    - Response normalization with type checks `is_string`, `is_array`, `instanceof` (lines 263-278) — business logic
    - Method not allowed response creation (lines 281-294) — implementation detail
    - Not found response creation (lines 296-299) — implementation detail

2. **Section 23** — PublicSurface must stay small (300 lines is NOT small)

**Current state vs plan:** The anonymous class (mentioned in fix-this.md) has been removed, but the rest remains. Some
improvement has been made (middleware pipeline, URL building, response normalization still in PublicSurface).

**Fix:**

- Extract `substituteRouteParams()` + query string logic → `Capabilities/UrlBuilder/SubstituteRouteParameters.php`
- Extract middleware pipeline assembly → `Capabilities/MiddlewarePipeline/BuildPipeline.php`
- Extract `normalizeToResponse()` → `Capabilities/ResponseNormalization/NormalizeControllerResult.php`
- Extract `createMethodNotAllowedResponse()`, `createNotFoundResponse()` → `Capabilities/ErrorResponseBuilding/`
- Router.php should then be only: route registration methods (`get`, `post`, `group`, `fallback`) + `dispatch()` that
  delegates

#### D-B.02 ❌ `GraphQLSchema.php` — VIOLATION (MEDIUM)

**File:** `components/API/GraphQL/System/PublicSurface/GraphQLSchema.php` (186 lines)
**Class:** `GraphQLSchema`
**Rules violated:**

1. **Section 6.2** — PublicSurface must NOT contain:
    - `foreach` loop in `fieldsFromMap()` (line 120-122) — business logic
    - `match` expressions in `findRootField()` (lines 134-138) and `rootTypeForOperation()` (lines 143-148) — decision
      logic
    - Nested `array_map` in `toArray()` (lines 157-183) — serialization logic

**Note:** The `foreach` loop is simple transformation (map fields to list). The `match` expressions are accessors. These
are borderline — they ARE delegation logic, not heavy business logic. But per strict reading of Section 6.2, even this
belongs in a Capability.

**Fix:**

- Extract `fieldsFromMap()` → `Capabilities/SchemaFieldAssembly/BuildFieldsFromMap.php`
- Extract `toArray()` → `Capabilities/SchemaSerialization/SchemaToArray.php`
- Keep `findRootField()` and `rootTypeForOperation()` as thin accessors OR move to capability
- PublicSurface should expose only: `define()`, `withQueryField()`, `withMutationField()`, `withObjectType()`,
  `findType()`, `toArray()`

#### D-B.03 ❌ `Workflow.php` — VIOLATION (MEDIUM)

**File:** `components/Operations/ApplicationWorkflow/System/PublicSurface/Workflow.php` (122 lines)
**Class:** `Workflow`
**Rules violated:**

1. **Section 6.2** — PublicSurface must NOT contain:
    - Saga state checking and conditional throw in `resume()` (lines 82-86) — business logic
    - Saga lookup and compensation dispatch in `cancel()` (lines 106-112) — orchestration logic
    - `SagaExecutor` instantiation with `new SagaExecutor()` in constructor (line 29) — DI bypass
    - Conditional saga state checks (lines 75-87, 106-110) — business logic

2. **Section 6.2** — `new Class()` in PublicSurface is FORBIDDEN (line 29: `new SagaExecutor()`)

**Fix:**

- Inject `SagaExecutor` through constructor instead of `new SagaExecutor()` (DI rule)
- Extract resume logic: saga lookup → store lookup + state validation belongs in Capability
- Extract cancel logic: compensation dispatch belongs in Capability
- Workflow.php should only: `start()`, `resume()`, `cancel()` — each delegating to SagaExecutor

---

### 7.3 Remaining Files Outside System/ (D-B cross-check)

**Rule:** `AGENTS.md` Section 7 — all production code must live inside System/.

All previously identified files have been moved to System/. CLEAN.

---

### 7.4 Other Phase D Items

#### D-C: `throw new` with NotImplemented/TODO

**Scan result:** No violations found in `components/*/System/PublicSurface/` or `framework/System/PublicSurface/`.
CLEAN.

#### D-D: PublicSurface Classification

Not yet completed. Every changed PublicSurface file should be classified as INTERNAL_ONLY / PUBLIC_COMPATIBLE /
PUBLIC_BREAKING / DEPRECATED_COMPATIBILITY_SHIM / ROADMAP_NOT_ACTIVE. This is a documentation task, not a code change.

---

### Phase D Summary

| Item                                                | Severity   | Status                                                                               |
|-----------------------------------------------------|------------|--------------------------------------------------------------------------------------|
| D-A.01 SchemaBuilder                                | LOW        | ✅ DELETED                                                                            |
| D-A.02 Testing::verifyContracts                     | LOW        | ✅ FIXED                                                                              |
| D-A.03-05 Marker interfaces                         | LOW        | ✅ ACCEPTED                                                                           |
| D-A.06 ResponseInterface                            | LOW        | ✅ ACCEPTED                                                                           |
| D-A.07 CacheReadTarget                              | LOW        | ✅ DELETED                                                                            |
| D-A.08-12 Cache re-exports                          | LOW        | ✅ DELETED (RuntimeCacheTarget, CompiledCacheTarget kept as legitimate value objects) |
| D-A.13 Middleware                                   | LOW        | ✅ DELETED                                                                            |
| **D-B.01 Router — business logic in PublicSurface** | **HIGH**   | ❌ **300 lines, preg_replace_callback, foreach pipeline, response normalization**     |
| **D-B.02 GraphQLSchema — foreach/match/array_map**  | **MEDIUM** | ❌ **186 lines, field building, serialization logic**                                 |
| **D-B.03 Workflow — saga orchestration logic**      | **MEDIUM** | ❌ **122 lines, state checks, SagaExecutor DI bypass**                                |
| D-C throw NotImplemented                            | LOW        | ✅ CLEAN                                                                              |
| D-D PublicSurface classification                    | LOW        | ⏳ Not done (documentation task)                                                      |
