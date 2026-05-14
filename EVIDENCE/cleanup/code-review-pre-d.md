# Code Review: Pre-Phase D — How-To Rule Violations

**Date:** 2026-05-14
**Scope:** Phases 0, B, C (everything up to Phase D)
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
7. [Summary](#7-summary)

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

## 7. Summary

### Severity Breakdown

| Severity | Count | Category |
|----------|-------|----------|
| **BLOCKER** | 13 | `?? new` fallback anti-pattern |
| **HIGH** | 19 files + 14 defaults + 5 naming + 5 naming + 7 naming + 1 folder + 1 non-canonical + 14 orphan files + 76 missing providers + 23 ?Type + 1605 array types | Mixed DI/naming/structure/style |
| **MEDIUM** | 781 @throws + 2 Testing folders + 1 TestingFakes + 1 component name + 7 provider naming | Documentation/structure |
| **LOW** | 1 empty `framework/Foundation/` | Cleanup |

### Top 10 Priority Fixes

| Priority | Fix | Impact |
|----------|-----|--------|
| 1 | Remove all 13 `?? new` fallback patterns | BLOCKER — DI integrity |
| 2 | Remove all 14 `= new ClassName()` defaults | HIGH — DI hygiene |
| 3 | Create ServiceProviders for runtime-critical components missing them | HIGH — architectural completeness |
| 4 | Fix `framework/System/Runtime/` → move to `Capabilities/Runtime/` | HIGH — canonical shape |
| 5 | Move 14 orphan System/ root files into canonical subdirs | HIGH — filesystem purity |
| 6 | Rename 17 Manager/Service/Handler files | HIGH — naming law |
| 7 | Fix 23 `?Type` → `Type|null` | HIGH — code style |
| 8 | Relocate/fix `framework/System/Capabilities/Diagnostics/` | HIGH — forbidden folder |
| 9 | Add `@throws` for 781 missing (start with framework/ first) | MEDIUM — documentation |
| 10 | Add array type PHPDoc for 1605 bare `array` returns | HIGH — type safety |
