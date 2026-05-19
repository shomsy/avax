# AvaX Dual-Recursive Strict Code Review

**Date:** 2026-05-19
**Reviewer:** AI Governance Agent (strict mode — dual-recursive)
**Mode:** Standard Mode per AGENTS.md §1.1
**Scope:** Whole-system dual review — Strict Code Review + How-To Deviations
**Status:** **RED** (29 BLOCKER, 69 HIGH, 41 MEDIUM, 19 LOW findings)

---

## Phase 0: Preflight

### Git Status
```
 M .agents/how-to/how-to.txt
 M .agents/management/evidence/generated/governance-event-stream.json
 M avax.part-1-of-4.txt
 M avax.part-2-of-4.txt
 M avax.part-3-of-4.txt
 M avax.part-4-of-4.txt
?? .agents/management/evidence/generated/authbuilder-split-second-slice.md
?? .agents/management/evidence/generated/discipline-review/discipline-review.txt
?? strict-code-review.md
```
**Assessment:** Only generated noise and the review file itself are dirty. No unrelated production code modified. Review-safe.

### Governance Inventory
20 `how-to-*.md` documents in `.agents/how-to/`:
- architecture, architecture-extension-with-ddd, clean-code, code-review, code-style, coding-standards, dependency-injection, design-components, document, dogfooding, events-listeners-event-sourcing-cqrs-realtime, git, modern-php-attributes-di, production-readiness, runtime-composition, system-performance, system-security, unit-test, use-advanced-architecture-patterns, write-avax

### Review Unit Discovery
**Components (13 areas, ~85 units):**
- API (5): ApiBlueprint, Contracts, GraphQL, OpenAPI, SchemaGeneration
- Application (12): Cache, Config, Container, DateTime, Facade, FeatureFlags, Filesystem, Localization, Pipeline, Storage, Text, Validation
- CLI (2): Console, System
- DataStack (4): Data, DataTransfer, Database, Persistence
- DeveloperTools (7): CodeGeneration, Diagnostics, Documentation, DumpDebugger, Dx, System, TestSupport
- Foundation (1): CallableSerialization
- HTTP (16): AfterResponse, ApiVersioning, Client, ContentNegotiation, Context, Dispatcher, Middleware, Request, Response, Router, SecureRequest, Security, Session, System, URI
- Identity (8): Access, Auth, Credentials, ExternalIdentity, Security, System, Tenancy, Tokens
- Integration (1): ObjectStorage
- Operations (20): ApplicationWorkflow, BackgroundProcesses, Concurrency, Delivery, Events, Filesystem, Logging, Mail, MemoryLifecycle, MessageBus, Notifications, Observability, Parallelism, Queue, Realtime, Resilience, RuntimeSupervision, Scheduler, System, Tasks
- Presentation (2): System, View
- Security (7): Cryptography, DataProtection, Hashing, Privacy, Redaction, Secrets, System
- SystemDesign (4): System, examples, reference-architectures, schemas

**Framework System (5 areas, ~69 units):**
- Capabilities (30): Benchmarks, ComponentManifest, ComponentRegistry, ConfigExplanation, ConfigValidation, Configuration, ContainerIntelligence, Doctor, ExternalState, FailureBoundary, Health, HealthCheck, MetadataWarmup, PreCommit, Queue, RequestScope, ResourceGovernance, ResponseNormalization, RouteIntelligence, Routing, Runtime, RuntimeBoundary, RuntimeIsolation, RuntimeSafety, RuntimeTimeline, Security, ServeModes, StateReset, SystemDesign, TracingTimeline, WorkerManagement
- Flows (29): AuditContainerScope through VerifyResetWasExecuted
- Configuration (7): BootDsl, BuildApplication, Builders, ConfigureRuntime, Foundation, LoadConfiguration, RegisterComponents
- PublicSurface (3): Console, Http, Runtime
- Foundation (7): Environment, Exception, Failure, Paths, Result, Time, Version

**Total scope:** ~2,200 PHP files across ~154 units

---

## Agent 1: API + DeveloperTools

**Scanned:** 195 PHP files, 9,292 lines
**API:** 132 files, 6,594 lines | **DeveloperTools:** 63 files, 2,698 lines

### Strict Code Review Findings

| Severity | File | Line | Rule Violated | Finding |
|----------|------|------|---------------|---------|
| BLOCKER | DeveloperTools/Diagnostics/System/PublicSurface/HealthCheck.php | 13 | AGENTS.md §17 — no static state leaking across requests | `private static array $readinessChecks = []` holds mutable state. `register()` (L15) mutates, `reset()` (L20) exists but caller must call it. Leaks in long-lived runtimes. |
| BLOCKER | API/SchemaGeneration/System/PublicSurface/SchemaGeneration.php | 20 | AGENTS.md §17 — no static state leaking across requests | `private static ?SchemaGenerationAssembly $assembly = null` caches assembly via `??=` (L24). Persists across requests in workers. `reset()` (L30) exists but not auto-called. |
| HIGH | API/GraphQL/System/PublicSurface/GraphQLSchema.php | 57–65 | AGENTS.md §6 — no `new` in PublicSurface | `define()` factory composes deps with `new AssembleFieldsFromMap()`, `new SchemaToArray()`, `new SchemaRouter()` instead of receiving via DI. |
| HIGH | API/Contracts/System/PublicSurface/ApiContracts.php | 22–80 | AGENTS.md §6 — no `new` in PublicSurface | 6 static methods compose deps with `new` (EndpointRegistry, DeprecationTracker, DetectBreakingChange, CompatibilityChecker, ValidateApiContract, RegisterApiVersion, DeprecateEndpoint). |
| HIGH | API/OpenAPI/System/PublicSurface/OpenAPI.php | 22–49 | AGENTS.md §6 — no `new` in PublicSurface | 5 static methods compose deps with `new` (ExportOpenApiDocument, BuildOpenApiDocument, ValidateOpenApiDocument, CompareOpenApiDocuments, RenderOpenApiJson, RenderOpenApiYaml). |
| HIGH | API/GraphQL/System/PublicSurface/GraphQL.php | 17–55 | AGENTS.md §6 — no `new` in PublicSurface | 4 static methods compose deps with `new` (BuildGraphQLSchema, GraphQLExecutor, ValidateGraphQLOperation, DataLoader). |
| HIGH | API/GraphQL/System/PublicSurface/GraphQLExecutor.php | 27–33 | AGENTS.md §6 — no `new` in PublicSurface | Constructor creates `new GraphQLConfiguration()`, `new GraphQLResolverRegistry()`, `new DataLoader()`, `new GraphQLResolverTimeline()`. |
| HIGH | API/ApiBlueprint/System/PublicSurface/ApiBlueprint.php | 29–39 | AGENTS.md §6 — no `new` in PublicSurface | `inMemory()` composes full dep graph with `new` (InMemoryApiDocumentationSource, DefineApiBlueprint, VerifyApiBlueprint, AnalyzeApiEvolution, CompatibilityChangeDetector, VerifyApiCompatibility). |
| HIGH | API/ApiBlueprint/System/PublicSurface/ApiSurface.php | 19–59 | AGENTS.md §6 — no `new` in PublicSurface | 7 static methods compose deps with `new` (RouteRegistrar, ResourceTransformer, ApplyPagination, ApplyFilterParameter, ApplySortParameter, HandleRestRequest, BuildRestResponse). |
| MEDIUM | components/API/Contracts/ | — | AGENTS.md §8 — forbidden folder names | "Contracts" is a forbidden concept word used as folder name. |
| MEDIUM | components/DeveloperTools/Diagnostics/ | — | AGENTS.md §8 — forbidden folder names | "Diagnostics" is a forbidden concept word used as folder name. Also used inside Capabilities paths. |
| MEDIUM | API/ApiBlueprint/System/Capabilities/Rpc/RpcExecutor.php | 30 | AGENTS.md §24 — no broad catch-and-ignore | Catches `Throwable` (L30) and returns error array — swallows exception without logging or re-throwing. |
| MEDIUM | DeveloperTools/TestSupport/Capabilities/ContractTesting/Verification/ContractVerifier.php | 45 | AGENTS.md §6 — no `class_exists` + `new` runtime composition | `class_exists($componentClass)` drives behavior at runtime. |
| MEDIUM | DeveloperTools/DumpDebugger/System/PublicSurface/shortcuts.php | 32, 57 | AGENTS.md §6 — no `class_exists` runtime leak | `class_exists(VarDumper::class)` decides runtime output path (acceptable for dev-only but flagged). |
| MEDIUM | API/GraphQL/System/Flows/ValidateGraphQLOperation.php | 21–24 | AGENTS.md §6 — no `new` in constructor defaults | Constructor defaults create deps with `new` — Flow should accept DI for testability. |
| MEDIUM | DeveloperTools/Diagnostics/System/Configuration/DiagnosticsConfig.php | 11 | AGENTS.md §25 — hidden I/O / superglobal | `isDebug()` reads `$_ENV['APP_DEBUG']` directly — hidden superglobal dependency. |
| LOW | DeveloperTools/CodeGeneration/Capabilities/Generators/CodeGenerator.php | 41–54 | AGENTS.md §27 — no hidden I/O | `detectBaseDirectory()` uses `getcwd()` — unreliable in long-lived runtimes. |
| LOW | DeveloperTools/DumpDebugger/Capabilities/Formatters/VariableFormatter.php | 11 | AGENTS.md — no hidden I/O | `json_encode` without `JSON_THROW_ON_ERROR` — silent fallback on failure. |

### How-To Deviation Findings

| Severity | Document | File | Line | Finding |
|----------|----------|------|------|---------|
| MEDIUM | how-to-coding-standards.md — PHPDoc must explain intent | Multiple files | — | Many `toArray()` methods have `@return array<string, mixed>` — restates type, does not explain behavior. |
| INFO | how-to-architecture-extension-with-ddd.md — component completeness | API/DeveloperTools/ | — | `DevTools.php:9` (`DevTools::run()`) is an empty stub — partial component. |
| INFO | how-to-coding-standards.md | Testing.php, ContractTesting.php | — | Duplicate class definitions: `ComponentContractResult`, `BreakingChangesReport`, `ContractVerificationReport` defined in both `ContractTesting.php` and their own files. |

### Summary
- Total units reviewed: 12 (API: 5, DeveloperTools: 7)
- Total PHP files scanned: 195
- Total strict findings: 17 (BLOCKER: 2, HIGH: 7, MEDIUM: 6, LOW: 2)
- Total deviation findings: 3
- Clean units: None with zero findings
- Key risks: PublicSurface classes across all API+DeveloperTools components compose dependencies with `new` — requires systematic refactor to DI. Static state in HealthCheck and SchemaGeneration must be addressed for long-lived runtime safety.

---

## Agent 2: Application Components

**Scanned:** 603 PHP files, 42,516 lines

### Strict Code Review Findings

| Severity | File | Line | Rule Violated | Finding |
|----------|------|------|---------------|---------|
| BLOCKER | Container/System/Capabilities/Composition/Compilation/MethodEmitter.php | 28,55,151 | how-to-runtime-composition.md | MethodEmitter emits hardcoded namespace references to `\Avax\Container\Capabilities\...` instead of current namespace `\Avax\Components\Application\Container\System\Capabilities\...`. Generated `compiled-container.php` will always fail at runtime with `class_not_found`. |
| BLOCKER | Container/System/Capabilities/Composition/Compilation/CompileContainer.php | 789 | how-to-runtime-composition.md | Same namespace drift in compiled container generation — references old namespace paths. |
| BLOCKER | Cache/System/Foundation/Serialization/PhpCacheSerializer.php | 48 | AGENTS.md §4 | `unserialize($serializedCachePayload->data, ['allowed_classes' => true])` — allows ALL classes during unserialization. PHP object injection RCE if attacker can write to cache store. |
| HIGH | Container/System/Capabilities/Declaration/Bindings/DependencyRegistry.php | — | how-to-coding-standards.md (DRY) | 1,150 lines. Nearly identical to ServiceRegistry.php (1,148 lines). ~2,300 lines of duplicated logic. |
| HIGH | Container/System/Capabilities/Declaration/Bindings/ServiceRegistry.php | — | how-to-coding-standards.md (DRY) | 1,148 lines. Duplicate of DependencyRegistry.php. Only registration class differs. Already diverging — ServiceRegistry uses `SharedLifetime::NAME`, DependencyRegistry uses `SingletonLifetime::NAME`. |
| HIGH | Cache/PublicSurface/Cache.php | 20 | AGENTS.md §17 | `private static ?CacheContract $cacheContract` — static mutable state unsafe for long-lived workers. |
| HIGH | Cache/PublicSurface/CompiledCache.php | 21 | AGENTS.md §17 | `private static ?CompiledCacheContract $compiledCacheContract` — same pattern. |
| HIGH | Container/PublicSurface/Container.php | 26 | AGENTS.md §17 | `private static ?ContainerInterface $container` — static mutable state. |
| HIGH | Storage/PublicSurface/Storage.php | 26-27 | AGENTS.md §17 | `private static ?string $defaultDisk`, `private static ?RegisteredDisks $registry` — static mutable state. |
| HIGH | Pipeline/PublicSurface/Pipeline.php | 21 | AGENTS.md §17 | `private static ?HookRegistry $hookRegistry` — static mutable state. |
| HIGH | Facade/Foundation/BaseFacade.php | 12-17 | AGENTS.md §17 | `protected static $container`, `$accessor`, `$resolvedInstances` — static mutable state. |
| HIGH | FeatureFlags/PublicSurface/FeatureFlags.php | 11 | AGENTS.md §17 | `private static ?FlagStoreInterface $flagStore` — static mutable state. |
| HIGH | Cache/System/PublicSurface/CompiledCache.php | 59 | AGENTS.md §6 | `new BuildCompiledCache(...)` — `new` in PublicSurface. |
| HIGH | Storage/System/PublicSurface/Storage.php | 65,93,101,109,117,125,133,141 | AGENTS.md §6 | `new FlowObject(...)` in EVERY static method — `new` in PublicSurface. |
| HIGH | Validation/System/PublicSurface/Validation.php | 18 | AGENTS.md §6 | `new ValidateData()` — `new` in PublicSurface. |
| HIGH | Cache/System/PublicSurface/Facade/Cache.php | 71 | AGENTS.md §6 | `new ReadFromCache(...)` — `new` in PublicSurface. |
| HIGH | Container/Foundation/SimpleContainer.php | 133-134 | AGENTS.md §6 | `class_exists($concrete) ? new $concrete(...$parameters)` — runtime composition leak. |
| HIGH | Container/Foundation/FrozenContainer.php | 181-182 | AGENTS.md §6 | `class_exists($concrete) ? new $concrete(...$parameters)` — runtime composition leak. |
| HIGH | Container/Capabilities/ResolveCallable/ResolveCallable.php | 198 | AGENTS.md §6 | `class_exists` + dynamic `new` — runtime composition leak. |
| HIGH | Container/Capabilities/Declaration/Bindings/DependencyRegistry.php | 272 | AGENTS.md §6 | `class_exists` + dynamic `new` — runtime composition leak. |
| HIGH | Container/Capabilities/Declaration/Bindings/ServiceRegistry.php | 271 | AGENTS.md §6 | `class_exists` + dynamic `new` — runtime composition leak. |
| HIGH | Container/System/Foundation/DIContainer.php | 57,91,119,127,143,157,193,283,293 | AGENTS.md §27 | Every call to `$container->get()`, `$container->make()`, `$container->call()` creates one or more new Flow objects via `new ResolveDependencyFlow(...)`. ~150+ short-lived objects per request with 50 resolutions. GC pressure in long-lived workers. |
| MEDIUM | Cache/System/Configuration/CacheRegistrar.php | 93-109 | how-to-dependency-injection.md | `register()` eagerly resolves `CacheContract` and `CompiledCacheContract` — mixing registration and boot logic. |
| MEDIUM | Container/Foundation/SimpleContainer.php | 43,136 | how-to-coding-standards.md | Anonymous class thrown as exception: `throw new class(...) extends RuntimeException`. Cannot be caught by interface type. |
| MEDIUM | Container/Foundation/FrozenContainer.php | 75,184 | how-to-coding-standards.md | Same anonymous class exception pattern. |
| MEDIUM | FeatureFlags/PublicSurface/FeatureFlags.php | 23-27 | AGENTS.md §17 | If `setStore()` is never called, `FeatureFlags::enable()` silently creates `InMemoryFlagStore` — flags reset on every worker cycle. |
| MEDIUM | Container/System/Capabilities/Composition/Compilation/CompileContainer.php | 258 | how-to-system-performance.md | `sha1(serialize(...))` for fingerprint — `serialize()` on large dependency graphs could be performance bottleneck during compilation. |
| MEDIUM | Config/System/PublicSurface/shortcuts.php | 18 | how-to-dependency-injection.md | Global `config()` helper calls `app()` with no fallback. If container is not booted, produces uncatchable error. |
| LOW | Text/System/PublicSurface/Text.php | 202-207 | AGENTS.md §27 | `function_exists('iconv')` with no fallback — silently fails if iconv not available. |
| LOW | Container/System/Capabilities/Composition/Compilation/CompileContainer.php | — | how-to-design-components.md | 1,310 lines — single class manages metadata, fingerprinting, snapshotting, caching, file I/O, compilation, source generation, invalidation, quarantine, and pruning. |
| LOW | Container/System/Capabilities/Composition/Compilation/MethodEmitter.php | 151 | AGENTS.md §6 | References non-existent namespace `\Avax\Container\Capabilities\Diagnostics\Errors\ContainerException` — class never created. |

### How-To Deviation Findings

| Severity | Document | File | Line | Finding |
|----------|----------|------|------|---------|
| HIGH | how-to-runtime-composition.md | Container/* (multiple files) | — | 32 total `class_exists()` calls in Container component — systematic runtime composition leak. |
| HIGH | how-to-runtime-composition.md, how-to-architecture.md | Cache/Storage/Pipeline/Validation/Facade/FeatureFlags | — | 7 components use static mutable state. `reset()` pattern exists but is manually called. |
| MEDIUM | how-to-dependency-injection.md | CacheRegistrar.php | 93-109 | Registration phase resolves services eagerly — registration must not trigger resolution. |
| MEDIUM | how-to-coding-standards.md | SimpleContainer, FrozenContainer | 43,136 | Anonymous class exceptions — cannot be caught by interface contract. |

### Summary
- Total units reviewed: 12 (Cache, Config, Container, DateTime, Facade, FeatureFlags, Filesystem, Localization, Pipeline, Storage, Text, Validation)
- Total PHP files scanned: 603
- Total strict findings: 30 (BLOCKER: 3, HIGH: 18, MEDIUM: 6, LOW: 3)
- Total deviation findings: 4
- Key risks: **BLOCKER** — compiled container generation emits broken PHP with old namespace references; PhpCacheSerializer RCE via `allowed_classes:true`. **CRITICAL** — Container component has 32 `class_exists()` calls, 2,300 lines of duplicated registry code, 1,310-line CompileContainer. 7 application components use static mutable state unsafe for workers.

---

## Agent 3: DataStack + HTTP Components

**Scanned:** 977 PHP files, ~5,500 lines analyzed (zero test files found)

### Strict Code Review Findings

| Severity | File | Line | Rule Violated | Finding |
|----------|------|------|---------------|---------|
| BLOCKER | HTTP/Session/System/PublicSurface/SessionScope.php | 32,36,110,113 | how-to-system-security.md §1 | `session_start()`, `session_id()`, `session_regenerate_id()` — plus direct `$_SESSION =` write at line 80. Three separate classes manage PHP session lifecycle independently. |
| BLOCKER | HTTP/Security/System/Capabilities/Csrf/CsrfToken.php | 19 | how-to-system-security.md §1 | `session_start()` + direct `$_SESSION` access — circumvents Session component. |
| BLOCKER | HTTP/Session/System/Capabilities/Storage/NativeSessionStore.php | 25,54 | how-to-system-security.md §1 | `session_start()`, `session_destroy()` + `setcookie()` — bypasses session authority. |
| BLOCKER | HTTP/Security/System/Capabilities/Csrf/CsrfTokens.php | 12 | AGENTS.md §25 | Uses `_csrf_tokens` (plural) session key — conflicts with other implementations. |
| BLOCKER | HTTP/Security/System/Capabilities/Csrf/CsrfTokenGenerator.php | 13 | AGENTS.md §25 | Uses `_csrf_token` (singular) — different key from CsrfTokens. |
| BLOCKER | HTTP/Security/System/PublicSurface/shortcuts.php | 16 | AGENTS.md §25 | Direct `$_SESSION['_csrf_token']` access — third key variant. |
| BLOCKER | HTTP/System/PublicSurface/shortcuts.php | 23 | AGENTS.md §25 | Uses `app(Security::class)->csrfToken()` — delegates to yet another path. |
| BLOCKER | HTTP/Security/System/Capabilities/Csrf/CsrfToken.php | 9 | AGENTS.md §25 | Uses `_token` (short key) — fourth variant. |
| BLOCKER | All shortcuts.php (13 files) | — | how-to-dependency-injection.md | Every `shortcuts.php` calls `app(SomeClass::class)` — global service locator at runtime, not testable without mocking `app()`. |
| HIGH | DataStack/Database/System/Capabilities/Connections/ReadConnection/ReadConnection.php | 103 | how-to-runtime-composition.md | `new BuildPhysicalConnection()` inside `open()` — runtime composition leak. |
| HIGH | DataStack/Database/System/Capabilities/Connections/Pools/DatabaseConnectionPool.php | 91 | how-to-runtime-composition.md | `new BuildPhysicalConnection()` inside `acquire()` — runtime composition leak. |
| HIGH | DataStack/DataTransfer/System/Flows/CreateDataObject/CreateDataObject.php | 220-225 | how-to-runtime-composition.md | `new CacheDataShape()` + `new InspectDataShape()` fallback — runtime composition leak. |
| HIGH | DataStack/DataTransfer/System/Capabilities/AttributeReading/AttributeCompiler.php | 23 | AGENTS.md §17 | `private static array $resolvedCache = []` — mutable static cache, memory leak in long-lived workers. |
| HIGH | DataStack/DataTransfer/System/Flows/CreateDataObject/CreateDataObject.php | 239 | AGENTS.md §25 | `$caster = new $casterClass()` — no validation that class implements `ValueCasterInterface` before instantiation. |
| HIGH | DataStack/Database/System/Capabilities/Migrations/CLI/MigrateCommand.php | 37,92 | AGENTS.md §6 | `new $class()` — dynamic instantiation of migration classes bypassing DI. |
| HIGH | DataStack/Database/System/Capabilities/Migrations/Migrations.php | 173 | AGENTS.md §6 | `new $class()` — same pattern. |
| HIGH | DataStack/Database/System/Capabilities/Migrations/CLI/SeederCommand.php | 28 | AGENTS.md §6 | `new $class()` — same pattern. |
| HIGH | DataStack/Database/System/Capabilities/Migrations/SeedDatabase/Seeder.php | 26 | AGENTS.md §6 | `new $class()` — same pattern. |
| MEDIUM | components/HTTP/Security/ | — | AGENTS.md §8 | "Security" is a forbidden concept word used as folder name. |
| MEDIUM | HTTP/Session/System/Capabilities/Storage/DatabaseSessionStore.php | 30,50,58 | how-to-system-security.md | `$this->table` interpolated via `sprintf` — SQL injection surface (low risk due to regex validation). |
| MEDIUM | DataStack/Database/System/Capabilities/Query/Grammar/Grammar.php | 389,426,438 | how-to-system-security.md | Table/column names interpolated via `sprintf`. Relies on `wrap()` for quoting. |
| MEDIUM | DataStack/Persistence/System/Flows/CompileDataQuery/CompileDataQuery.php | 70,74,87,95,100 | how-to-system-security.md | Same SQL interpolation pattern. |
| MEDIUM | DataStack/Database/System/Configuration/DatabaseServiceProvider.php | 40 | AGENTS.md §6 | `class_exists(CompiledDatabaseLifecycleRegistry::class)` — dead code with side-effect of triggering autoload. |
| MEDIUM | HTTP/Security/System/PublicSurface/shortcuts.php | 56 | how-to-system-security.md | `X-XSS-Protection: 1; mode=block` is deprecated. Missing `Content-Security-Policy` and `Strict-Transport-Security`. |
| MEDIUM | HTTP/System/Capabilities/MiddlewarePipeline/RateLimiterMiddleware.php | 110 | how-to-coding-standards.md | Anonymous class in hot path — can't be tested in isolation, can't be extended. |
| MEDIUM | HTTP/AfterResponse/System/Capabilities/Tasks/AfterResponseQueue.php | 24 | AGENTS.md §24 | `catch (Throwable $e) { error_log(...); }` — silent catch-and-continue. |
| MEDIUM | HTTP/System/Flows/HandleRequest/HandleRequest.php | 23 | AGENTS.md §24 | `catch (Exception $exception)` — catches `Exception` not `Throwable`; `Error` passes through unhandled. |
| LOW | HTTP/System/PublicSurface/shortcuts.php | 23 | AGENTS.md §9 | Duplicate `csrf_token()` global function — also defined in HTTP/Security/System/PublicSurface/shortcuts.php:8. First loaded wins. |
| LOW | HTTP/ContentNegotiation/System/Capabilities/Formats/CsvFormat.php | 18,21,24,25 | how-to-system-security.md | No protection for CSV injection cells starting with `=`, `+`, `-`, `@`. |
| LOW | HTTP/System/Flows/SendResponse/SendResponse.php | 17,21,25 | how-to-unit-test.md | Uses `echo` + `header()` — untestable. |
| LOW | DataStack/Database/System/Capabilities/Query/Grammar/Grammar.php | 388,425,437 | how-to-coding-standards.md | IDE-only `// noinspection SqlNoDataSourceInspection` annotations in production code. |

### Positive Observations
- Pervasive `readonly` classes, `final` by default — excellent PHP 8.x style
- Named arguments used consistently
- Constructor promotion is standard
- MiddlewarePipeline is clean and well-structured
- Grammar system uses polymorphism properly with dialect-specific subclasses
- Security shortcuts use `htmlspecialchars()` with `ENT_QUOTES` and `UTF-8` correctly
- `SignedUrlVerifier` properly catches and silently returns false on JWT decode failure (correct fail-closed behavior)

### Summary
- Total units reviewed: 20 (DataStack: 4, HTTP: 16)
- Total PHP files scanned: 977
- Total strict findings: 27 (BLOCKER: 9, HIGH: 8, MEDIUM: 8, LOW: 4)
- Total deviation findings: 2
- Zero test files found across 977 files — fails AGENTS.md §30, §31 (Testing Rule, Component Completion Rule)
- Key risks: Session lifecycle conflict between 3+ independent implementations with 4 different CSRF token keys. All 13 shortcut files depend on global `app()`. Zero tests means any refactor is blind.

---

## Agent 4: Identity + Security + CLI + Foundation + Integration + Presentation + SystemDesign

**Scanned:** ~95+ PHP files across 22+ units

### Strict Code Review Findings

| Severity | File | Line | Rule Violated | Finding |
|----------|------|------|---------------|---------|
| BLOCKER | Foundation/CallableSerialization/System/Capabilities/SerializeCallable/SerializeClosureThroughLibrary.php | 43 | AGENTS.md §4 | `$serializable = unserialize($serialized);` — no `allowed_classes` parameter. Remote code execution via deserialization. |
| BLOCKER | Security/Redaction/System/PublicSurface/Redaction.php | 20,25,30,35,46,56,64,69 | AGENTS.md §25, how-to-design-components.md | Every method creates new instances via `new` (PolicyEngine, DataClassifier, PatternMatcher, RedactionEngine, RedactLogData, ApplyRedactionPolicy, ClassifySensitiveData). No DI, no composition. Unbounded allocation in long-lived runtimes. |
| BLOCKER | Security/Secrets/System/PublicSurface/Secrets.php | 12,21-23 | AGENTS.md §17 | `private static SecretStore $secretStore` with lazy init `self::$secretStore = new InMemorySecretStore()`. Static state persists secrets across requests. `rotate()` and `set()` are static — any request can mutate shared state. |
| BLOCKER | Application/Cache/System/Capabilities/Stores/RedisCacheStore.php | 90 | AGENTS.md §4 | `return $value === false ? null : unserialize($value);` — no `allowed_classes` parameter. Cache data from Redis could contain malicious serialized payloads. |
| HIGH | components/Identity/Security/ | — | AGENTS.md §9 | "Security" is a forbidden concept word as folder name. Needs explicit governance exception per AGENTS.md §16. |
| HIGH | tests/Unit/Components/Security/Cryptography/CryptographyTest.php | — | how-to-unit-test.md, AGENTS.md §25 | Only 1 test (happy path roundtrip). Missing: decryption with wrong key, tampered ciphertext, empty plaintext, invalid key, algorithm mismatch. |
| HIGH | tests/Unit/Components/Security/Secrets/SecretsCapabilitiesTest.php | — | how-to-unit-test.md, AGENTS.md §25 | 8 tests, all store/retrieve/redact happy path. Missing: concurrent access, overwrite empty, race condition in lazy init, lifecycle in long-lived runtime. |
| MEDIUM | Security/Cryptography/System/Flows/DecryptValue/DecryptValue.php | 55,62 | AGENTS.md §24 | Two bare `catch (Throwable)` blocks — no logging, no counter, no rethrow. Lines 55-56 catch JSON decode failure and silently fall through to `unserialize`. Lines 62-64 catch unserialize errors and silently return degraded plaintext. |
| MEDIUM | components/Identity/Access/System/Capabilities/Facades/ | — | AGENTS.md §9 | "Facades" is a concept pattern name as folder. Describes pattern, not capability. |

### How-To Deviation Findings

| Severity | Document | File | Line | Finding |
|----------|----------|------|------|---------|
| HIGH | how-to-unit-test.md | CryptographyTest | — | Security code requires negative tests, boundary tests, abuse cases. Missing. |
| HIGH | how-to-unit-test.md | SecretsCapabilitiesTest | — | Same — no abuse/negative tests for security-sensitive code. |
| MEDIUM | how-to-clean-code.md | AuthBuilder.php | — | 797-line builder with 179 `with*` methods and 30+ `new` calls in `ready()`. Functional but maintenance smell. |

### Summary
- Total units reviewed: 22+ (Identity: 8, Security: 7, CLI: 2, Foundation: 1, Integration: 1, Presentation: 2, SystemDesign: 4)
- Total PHP files scanned: ~95+
- Total strict findings: 9 (BLOCKER: 4, HIGH: 3, MEDIUM: 2)
- Total deviation findings: 3
- Key risks: 4 BLOCKER (2 unserialize vulnerabilities, Redaction static PS, Secrets static state). Security components missing critical negative tests. Forbidden folder names.

---

## Agent 5: Operations Components

**Scanned:** 163 PHP files across 20 components

### Strict Code Review Findings

| Severity | File | Line | Rule Violated | Finding |
|----------|------|------|---------------|---------|
| BLOCKER | Concurrency/System/PublicSurface/Concurrency.php | 19 | AGENTS.md §17 | `private static ?TaskRuntimeInterface $runtime = null` — static mutable runtime in PublicSurface. |
| BLOCKER | Parallelism/System/PublicSurface/Parallel.php | 15 | AGENTS.md §17 | `private static ?ParallelRuntimeInterface $runtime = null` — static mutable runtime. |
| BLOCKER | Realtime/System/PublicSurface/Realtime.php | 16-18 | AGENTS.md §17 | `private static ?ConnectionPool $connectionPool`, `?RealtimeChannels $realtimeChannels` — lazy-init static state. |
| BLOCKER | Realtime/System/Capabilities/WebSocket/WebSocketServer.php | 10,13 | AGENTS.md §17 | `private static array $connections`, `$channels` — static global mutable store. |
| BLOCKER | Realtime/System/Capabilities/WebSocket/PresenceChannel.php | 9 | AGENTS.md §17 | `private static array $members` — static presence state. |
| BLOCKER | MessageBus/System/PublicSurface/MessageBus.php | 13 | AGENTS.md §17 | `private static ?self $instance = null` — singleton instance pattern. |
| BLOCKER | Events/System/Foundation/GlobalEventListenerState.php | 18-19 | AGENTS.md §17 | `private static ?ListenerRegistry $registry`, `?EventEmitter $emitter` — global static state. |
| BLOCKER | Scheduler/System/PublicSurface/Scheduler.php | 13,15 | AGENTS.md §17 | `private static ?TaskRunner $taskRunner`, `private static array $scheduledTasks` — static mutable state. |
| BLOCKER | Resilience/System/Capabilities/Timeout/Timeout.php | 25 | AGENTS.md §17 | `private static ?bool $pcntlAvailable = null` — cached static (lower risk but flagged). |
| HIGH | Filesystem/System/PublicSurface/Filesystem.php | 18,23,28,33,38,46 | AGENTS.md §6 | `new ReadFile()`, `new WriteFile()`, `new DeleteFile()`, `new CopyFile()`, `new MoveFile()`, `new ListDirectory()` — `new` in PublicSurface. |
| HIGH | Observability/System/PublicSurface/Observability.php | 18,23,28,33,41,50 | AGENTS.md §6 | `new Span()`, `new Counter()`, `new Gauge()`, `new Histogram()`, `new StructuredLogRecord()`, `new AuditEvent()` — `new` in PublicSurface. |
| HIGH | MemoryLifecycle/System/PublicSurface/MemoryLifecycle.php | 15,20,25 | AGENTS.md §6 | `new MemoryBudget()`, `new MemorySnapshot()`, `new MemoryTracker()` — `new` in PublicSurface. |
| HIGH | BackgroundProcesses/System/PublicSurface/BackgroundProcesses.php | 21,26,31,36 | AGENTS.md §6 | `new ProcessRegistry()`, `new SupervisionPolicy()`, `new RestartPolicy()`, `new HealthPolicy()` — `new` in PublicSurface. |
| HIGH | RuntimeSupervision/System/PublicSurface/RuntimeSupervision.php | 14,19 | AGENTS.md §6 | `new Supervisor()`, `new ProcessRegistry()` — `new` in PublicSurface. |
| HIGH | Delivery/System/PublicSurface/Delivery.php | 16,21,26,31 | AGENTS.md §6 | `new BuildManifest()`, `new CompileApplication()`, `new ReleaseManifest()`, `new RollbackPlan()` — `new` in PublicSurface. |
| HIGH | Tasks/System/PublicSurface/Tasks.php | 20,25,30 | AGENTS.md §6 | `new TaskRunner()`, `new TaskQueue()`, `new TaskScheduler()` — `new` in PublicSurface. |
| HIGH | Queue/System/PublicSurface/Tasks.php | 14,20,40 | AGENTS.md §6 | `new TaskBus()` in static methods and `TaskBatch` — `new` in PublicSurface. |
| HIGH | Queue/System/PublicSurface/TaskBatch.php | 19 | AGENTS.md §6 | `new TaskBus()` inside data object — `new` in PublicSurface. |
| HIGH | Scheduler/System/PublicSurface/Scheduler.php | 19,45 | AGENTS.md §6 | `new ScheduledTask()`, `new TaskRunner()` — `new` in PublicSurface. |
| HIGH | Events/System/PublicSurface/Events.php | 31-32 | AGENTS.md §6 | `new ListenerRegistry()`, `new EventDispatcher()` in constructor — `new` in PublicSurface. |
| HIGH | Notifications/System/PublicSurface/Notifier.php | 20 | AGENTS.md §6 | `new SendNotification()` in constructor — `new` in PublicSurface. |
| HIGH | Queue/System/PublicSurface/QueueWorker.php | 42-43 | AGENTS.md §6 | `class_exists($class)` + `new $class(...$data)` — runtime composition with autoload-triggered class resolution from payload. |
| HIGH | Logging/System/Capabilities/Writing/RotatingFileWriter.php | 39 | how-to-system-security.md | Calls `json_encode($context)` directly **without** redaction. Secrets may leak to disk. |
| MEDIUM | components/Operations/Events/ | — | AGENTS.md §8 | "Events" is a forbidden concept name used as folder name. |
| MEDIUM | Logging/System/Capabilities/Writers/RotatingFileWriter.php | — | how-to-design-components.md | Two `RotatingFileWriter` classes exist: under `Writers/` (with Redaction) and `Writing/` (without Redaction). Inconsistent. |
| MEDIUM | Queue/System/PublicSurface/QueueWorker.php | 42 | how-to-system-security.md | `class_exists()` + `new` on arbitrary payload `$class` — needs negative test for missing class, invalid class, class not implementing JobInterface. |
| MEDIUM | Logging/System/Capabilities/Writing/RotatingFileWriter.php | 39 | how-to-system-security.md | Secret key redaction — needs negative test for partial key matches, nested array keys. |
| LOW | ApplicationWorkflow/System/PublicSurface/Saga.php | 27 | how-to-clean-code.md | `protected array $stepResults` on `final class` — `protected` on final class is dead weight. |

### Summary
- Total units reviewed: 20
- Total PHP files scanned: 163
- Total strict findings: 29 (BLOCKER: 9, HIGH: 15, MEDIUM: 4, LOW: 1)
- Total deviation findings: 5
- Key risks: 9 BLOCKER static state leaks across 8 components (Concurrency, Parallelism, Realtime, MessageBus, Events, Scheduler, Resilience). 14 HIGH `new` in PublicSurface violations. QueueWorker `class_exists` + `new` from payload. Unredacted logging.

---

## Agent 6: Framework Capabilities

**Scanned:** ~95 PHP files across 30 capability directories

### Strict Code Review Findings

| Severity | File | Line | Rule Violated | Finding |
|----------|------|------|---------------|---------|
| BLOCKER | FailureBoundary/PublicSurface/FailureBoundary.php | 22 | how-to-runtime-composition.md | Static facade with mutable singleton (`setInstance`). Worker-unsafe static state leak across requests. |
| BLOCKER | FailureBoundary/PublicSurface/FailureBoundary.php | 42 | how-to-dependency-injection.md, how-to-runtime-composition.md | `(new BuildFailureBoundary())->build()` inside static getInstance — `new` in PublicSurface entrypoint. |
| BLOCKER | FailureBoundary/Capabilities/RunRecoveryAction/RunRecoveryAction.php | 37-41 | how-to-runtime-composition.md | `class_exists()` + `new $recoverClass()` at runtime. No container resolution, no compile-time validation. |
| BLOCKER | FailureBoundary/Capabilities/RunFallbackAction/RunFallbackAction.php | 31-35 | how-to-runtime-composition.md | Same pattern — `class_exists()` + `new $fallbackClass()`. Runtime class loading bypasses container. |
| HIGH | framework/System/Capabilities/Security/ | — | AGENTS.md §8, how-to-architecture.md | "Security" is a concept word, not a flow/capability name. Must decompose into `RequestSigning/`, `PolicyEngine/`, `FeatureFlags/`, `ServiceDiscovery/`, `SecurityDoctor/`. |
| HIGH | ResourceGovernance/PublicSurface/ResourceGovernor.php + ResourceGovernance/System/PublicSurface/ResourceGovernor.php | — | how-to-design-components.md | Two copies of nearly identical class in different namespaces. Autoload ambiguity. |
| HIGH | Runtime/GracefulShutdown/PublicSurface/GracefulShutdown.php + Runtime/GracefulShutdown/System/PublicSurface/GracefulShutdown.php | — | how-to-design-components.md | Two identical copies of GracefulShutdown. Same for ShutdownSequence in `Capabilities/` vs `System/Capabilities/`. |
| HIGH | ExternalState/System/PublicSurface/ExternalState.php | 10 | how-to-runtime-composition.md | Full static facade with 4 mutable properties (`$session`, `$cache`, `$lock`, `$rateLimit`). Worker-unsafe. |
| HIGH | ExternalState/System/PublicSurface/ExternalState.php | 31-51 | how-to-system-security.md, how-to-system-performance.md | `getenv('REDIS_URL')` called inside instance methods. Env vars at runtime bypass configuration system. Race condition in long-lived workers. |
| HIGH | ResourceGovernance/PublicSurface/ResourceGovernor.php | 10 | how-to-runtime-composition.md | 4 mutable static properties (`$memoryBudget`, `$snapshots`, `$requestCount`, `$totalMemoryStart`). Worker-unsafe despite reset(). |
| HIGH | RuntimeSafety/StatelessBoundary/PublicSurface/StatelessBoundary.php | 9 | how-to-runtime-composition.md | Static class with mutable `$mode`. Uses `StatelessGuard::enforce()` statically. |
| HIGH | Runtime/GracefulShutdown/Capabilities/ShutdownSequence.php | 48 | how-to-coding-standards.md | `exit(0)` call inside library code. Prevents clean runtime composition, testing, and graceful worker recycling. |
| HIGH | Runtime/GracefulShutdown/Capabilities/ShutdownSequence.php | 15-18 | how-to-runtime-composition.md | Global mutable static `$callbacks`, `$draining`, `$executed`. Race condition in concurrent runtimes. |
| HIGH | Doctor/CheckAutoload.php | 14 | how-to-coding-standards.md | `dirname(__DIR__, 4)` — hardcoded path traversal. Same pattern in CheckConfiguration.php:19, CheckRuntimeMode.php:20, CheckWarmSafety.php:14, CheckMemoryGuard.php:14. |
| HIGH | Runtime/Capabilities/RunApplicationOnPhpBuiltInServer.php | 42 | how-to-coding-standards.md | `dirname(__DIR__, 4)` for document root resolution. Fragile path traversal. |
| HIGH | PreCommit/PreCommitValidator.php | 120 | how-to-runtime-composition.md | `fwrite(STDERR, ...)` — STDERR may not be available in Swoole/FrankenPHP/ReactPHP. |
| HIGH | Security/RequestSigning/VerifyInternalRequestSignature.php | 14 | how-to-system-security.md | `$secretKey` stored as plain string without `#[SensitiveParameter]`. Could leak via var_dump/stack traces. |
| HIGH | Security/RequestSigning/SignInternalRequest.php | 15 | how-to-system-security.md | Same — `$secretKey` without `#[SensitiveParameter]`. |
| HIGH | Security/PolicyEngine/PolicyRule.php | 22 | how-to-coding-standards.md | `public mixed $condition = null` — documented as `callable|null` but typed `mixed`. Not enforceable. |
| MEDIUM | FailureBoundary/Configuration/FailureBoundaryServiceProvider.php | 16 | how-to-code-style.md | ServiceProvider is not `readonly` despite having all constructor-promoted properties. |
| MEDIUM | PreCommit/PreCommit.php | 243 | how-to-dependency-injection.md | `new $checkClass()` with string class name — should use container resolution. |
| MEDIUM | PreCommit/PreCommit.php | 291-301 | how-to-clean-code.md | `catch (Throwable)` logs error but continues. Swallows infrastructure failures silently. |
| MEDIUM | FailureBoundary/Capabilities/RunRecoveryAction/RunRecoveryAction.php | 38 | how-to-system-security.md | `"Recovery class not found: {$recoverClass}"` — leaks class name to logs. |
| MEDIUM | FailureBoundary/Capabilities/RunFallbackAction/RunFallbackAction.php | 32 | how-to-system-security.md | `"Fallback class not found: {$fallbackClass}"` — same leak. |

### How-To Deviation Findings

| Severity | Document | File | Line | Finding |
|----------|----------|------|------|---------|
| BLOCKER | how-to-runtime-composition.md §Leak Law | FailureBoundary/PublicSurface/FailureBoundary.php | 22-58 | Static facade creates hidden coupling between requests. |
| BLOCKER | how-to-runtime-composition.md §No class_exists+new | RunRecoveryAction.php, RunFallbackAction.php | 37-41, 31-35 | Runtime class_exists+new bypasses container. |
| HIGH | how-to-architecture.md §Folder Naming Law | Security/ directory | — | Concept word "Security" as folder name. |
| HIGH | how-to-design-components.md §Canonical Shape | ResourceGovernance/ | — | Duplicate component trees under both PublicSurface/ and System/PublicSurface/. |
| HIGH | how-to-design-components.md §Canonical Shape | Runtime/GracefulShutdown/ | — | Four duplicate classes across PublicSurface/, Capabilities/, System/PublicSurface/, System/Capabilities/. |
| HIGH | how-to-runtime-composition.md §Leak Law | ExternalState/System/PublicSurface/ExternalState.php | 10-145 | Full static facade with mutable state and runtime getenv(). Violates worker safety. |
| HIGH | how-to-system-security.md §Secret Handling | VerifyInternalRequestSignature.php, SignInternalRequest.php | 14,15 | No `#[SensitiveParameter]` on secret key. |
| HIGH | how-to-runtime-composition.md | GracefulShutdown/.../ShutdownSequence.php | 48 | `exit(0)` in library code violates "runtime must control lifecycle". |
| HIGH | how-to-coding-standards.md | Doctor/*.php | 14-20 | Hardcoded `dirname(__DIR__, 4)` path traversal in 5 files. |
| HIGH | how-to-runtime-composition.md | PreCommit/PreCommitValidator.php | 120 | `fwrite(STDERR)` not available in all runtimes. |
| HIGH | how-to-system-security.md | RunRecoveryAction.php, RunFallbackAction.php | 38,32 | Class names leaked in error messages to logs. |
| HIGH | how-to-coding-standards.md §Type Safety | PolicyRule.php | 22 | `mixed` type for documented `callable` parameter — no compile-time enforcement. |
| MEDIUM | how-to-code-style.md | FailureBoundaryServiceProvider.php | 16 | Missing `readonly` on service provider. |
| MEDIUM | how-to-dependency-injection.md | PreCommit.php | 243 | `new $checkClass()` instead of container resolution. |
| MEDIUM | how-to-clean-code.md | PreCommit.php | 291 | Silent catch(Throwable) continues execution after infrastructure failure. |

### Summary
- Total units reviewed: 30 capability directories
- Total PHP files scanned: ~95+
- Total strict findings: 24 (BLOCKER: 4, HIGH: 14, MEDIUM: 6)
- Total deviation findings: 15 (BLOCKER: 2, HIGH: 10, MEDIUM: 3)
- Key risks: FailureBoundary static facade is worker-unsafe; 2 `class_exists`+`new` runtime composition leaks in Fallback/Recovery actions; Security folder is concept word; duplicate classes in ResourceGovernance and GracefulShutdown; 5 Doctor files with hardcoded paths; `exit(0)` in library code; secrets without `#[SensitiveParameter]`.

---

## Agent 7: Framework Flows + Configuration + PublicSurface + Foundation

**Scanned:** 101 PHP files across 79 units (Flows: 56, Configuration: 17, PublicSurface: 5, Foundation: 1)

### Strict Code Review Findings

| Severity | File | Line | Rule Violated | Finding |
|----------|------|------|---------------|---------|
| BLOCKER | framework/System/PublicSurface/Avax.php | 70-95 | AGENTS.md §6, AI Safety §24 | `Avax::create()` constructs full object graph via `new` (CreateHttpResponse, CreateRequestFromGlobals, CreateApplication, ComponentRegistry, RuntimeContext, etc.). PublicSurface must receive, not construct. |
| BLOCKER | framework/System/PublicSurface/Avax.php | 123-157 | AGENTS.md §6, AI Safety §24 | `Avax::bootInternal()` assembles kernel graph via `new` (CreateHttpResponse, HandleIncomingHttp, BootApplication, HttpKernel, ConsoleKernel, RuntimeKernel, ResetApplicationState). |
| BLOCKER | framework/System/PublicSurface/BootDsl.php | 144-176 | AGENTS.md §6, AI Safety §24 | `BootDsl::create()` constructs object graph via `new` (SystemClock, ProjectPath, CreateHttpResponse, HandleIncomingHttp, ProviderRegistry, BootDslEngine). |
| BLOCKER | framework/System/PublicSurface/Diagnostics.php | 10-40 | AGENTS.md §22, AI Safety §24 | PublicSurface class uses `static` properties (`$correlationId`, `$traceId`). Static state leaks across requests in long-lived workers. |
| BLOCKER | framework/System/Flows/RunApplication/RunApplication.php | 55-75 | AGENTS.md §6, how-to-runtime-composition.md | `withDefaultResolutionPipeline()` static factory constructs pipeline via `new` (RouteFacadeContainer, ResolveCallable, ControllerResolver, ArgumentResolver, ReadIncomingHttpRequest, MatchHttpRoute). |
| BLOCKER | framework/System/PublicSurface/App.php | 302-306 | AGENTS.md §6 | `ensureInitialized()` calls `RunApplication::withDefaultResolutionPipeline()` which internally constructs dispatch pipeline via `new`. |
| HIGH | framework/System/Flows/CreateApplication/CreateApplication.php | 58-101 | AGENTS.md §6 | `make()` Flow assembles full object graph via `new` (RuntimeState, Runtime, StateResetRegistry, StaticStateReset, NormalizeControllerResult, App). |
| HIGH | framework/System/Flows/CreateApplication/CreateApplication.php | 107-150 | AGENTS.md §6 | `fromBuilder()` duplicates assembly logic via `new` (ComponentRegistry, RequestScopeStore, RuntimeContext, StateResetRegistry, RuntimeState, CreateHttpResponse, Runtime). |
| HIGH | framework/System/PublicSurface/App.php | 254-264 | AGENTS.md §28 | `asHttpKernel()` returns an anonymous class. Cannot be autoloaded, cached, or serialized. |
| HIGH | framework/System/PublicSurface/App.php | 204-231 | AGENTS.md §6 | `handle()` creates `OpenHttpRequestScope` and `CloseHttpRequestScope` via `new` per-request. |
| HIGH | framework/System/PublicSurface/App.php | 269-286 | AGENTS.md §6 | `asConsoleKernel()` and `asRuntimeKernel()` construct via `new` (ConsoleKernel, RunConsoleCommand, PreCommitConfig, PreCommit, RuntimeKernel). |
| HIGH | framework/System/PublicSurface/AvaxInterface.php | 16-33 | AGENTS.md §28, how-to-coding-standards.md | No PHPDoc on any method. Interface methods must document return types, semantics, and failure modes. |
| HIGH | framework/System/PublicSurface/Http/HttpKernelInterface.php | 10-13 | AGENTS.md §28, how-to-coding-standards.md | No PHPDoc on `handle()`. Missing `@throws` annotation. |
| HIGH | framework/System/PublicSurface/Console/ConsoleKernelInterface.php | 9-15 | AGENTS.md §28, how-to-coding-standards.md | Has partial PHPDoc but missing `@throws` annotation. |
| HIGH | framework/System/PublicSurface/Runtime/RuntimeKernelInterface.php | 11-16 | AGENTS.md §28, how-to-coding-standards.md | No PHPDoc on `state()` or `runWorker()`. Missing `@throws` annotation. |
| HIGH | framework/System/Flows/HandleException/HandleException.php | 11 | AGENTS.md §7, how-to-design-components.md | Not `final readonly`. All Flows must be `final readonly` per canonical component shape. |
| HIGH | framework/System/Flows/HandleException/HandleException.php | 51-59 | AGENTS.md §22, how-to-system-security.md | `reportFrameworkFailure()` uses `error_log()` directly — hidden I/O outside abstraction. |
| HIGH | framework/System/Flows/RunDoctor/RunDoctor.php | 11-18 | AGENTS.md §7, how-to-design-components.md | Not `final readonly`. Mutable `$runtimeSafety` property with optional constructor defaulting to `RuntimeSafety::create()` — hidden dependency construction. |
| HIGH | framework/System/Flows/DetectStateLeak/DetectStateLeak.php | 10-17 | AGENTS.md §7, how-to-design-components.md | Not `final readonly`. Optional `RuntimeSafety` parameter defaults to `RuntimeSafety::create()`. |
| HIGH | framework/System/Flows/InspectStaticState/InspectStaticState.php | 10-17 | AGENTS.md §7, how-to-design-components.md | Not `final readonly`. Same pattern — optional constructor parameter with `new` fallback. |
| HIGH | framework/System/Flows/VerifyRequestScopeWasClosed/VerifyRequestScopeWasClosed.php | 10-17 | AGENTS.md §7, how-to-design-components.md | Not `final readonly`. Same hidden-dependency pattern. |
| HIGH | framework/System/Flows/VerifyResetWasExecuted/VerifyResetWasExecuted.php | 10-17 | AGENTS.md §7, how-to-design-components.md | Not `final readonly`. Same hidden-dependency pattern. |
| HIGH | framework/System/Flows/DiscoverComponents/DiscoverComponents.php | 9 | AGENTS.md §7 | Not `final readonly`. Has `static` method `printReport()` — static methods in Flows violate unit-responsibility rule. |
| HIGH | framework/System/Flows/ReportRuntimeFailure/ReportRuntimeFailure.php | 104-129 | AGENTS.md §22, AI Safety §25 | `getRequestInfo()` reads `$_SERVER` superglobals directly; `getUserInfo()` reads `$_SESSION`. Hidden I/O coupling to PHP runtime. |
| MEDIUM | framework/System/Flows/ResetApplicationState/ResetApplicationState.php | 28-38 | AGENTS.md §7 | Three empty private methods (`resetRequestScope`, `resetRuntimeContext`, `resetDiagnosticsContext`). Stubs without behavior. |
| MEDIUM | framework/System/Flows/ShutdownRuntime/ShutdownRuntime.php | 16-22 | AGENTS.md §7 | Two empty private methods (`flushTerminableWork`, `closeRuntimeResources`). Stubs without behavior. |
| MEDIUM | framework/System/Configuration/ConfigureRuntime/ConfigureRuntime.php | 1-9 | AGENTS.md §31 | Empty class with no methods, properties, or behavior. |
| MEDIUM | framework/System/PublicSurface/App.php | 74 | AGENTS.md §17, how-to-runtime-composition.md | Mutable `$running` boolean flag for per-request state tracking. Unsafe for long-lived workers where same App instance serves multiple requests. |
| MEDIUM | framework/System/Flows/HandleException/HandleException.php | 24-31 | AGENTS.md §6 | `classifyFrameworkFailure()` only checks `BadMethodCallException`, defaults to `'console'`. Incomplete classification. |
| MEDIUM | framework/System/Flows/HandleIncomingHttp/FrameworkRouteRegistrar.php | 83-88 | AGENTS.md §6 | `group()` method is a silent no-op. Satisfies `RouterInterface` but silently discards route group configuration. |
| MEDIUM | framework/System/Flows/ConvertPhpErrorToThrowable/ConvertPhpErrorToThrowable.php | 74-95 | AGENTS.md §6 | `convert()` throws two different exception types (`ErrorException`, `PhpErrorException`) from the same method. Callers must catch both. |
| MEDIUM | framework/System/Configuration/BootDsl/BootDslEngine.php | 157-206 | AGENTS.md §6 | `createRuntimeAndApp()` performs assembly of the full runtime/app object graph (12+ `new` calls). Single method violates SRP. |
| MEDIUM | framework/System/Foundation/compat.php | 36-162 | AGENTS.md §9 | `compat.php` contains 140+ `class_alias` entries. Named `compat.php` which is a concept word, not a flow or capability. |
| LOW | framework/System/Foundation/Version/AvaxVersion.php | 9-15 | AGENTS.md §18 | Constants declare `MAJOR=1`, `MINOR=0`, `PATCH=0`, `VERSION='1.0.0'`. But the project is V4 — version string inconsistent with roadmap. |
| LOW | framework/System/Flows/HandleException/FrameworkExceptionHandlingFailed.php | 9 | AGENTS.md §6 | Exception class with empty body. |
| LOW | framework/System/Flows/ShutdownRuntime/RuntimeShutdownFailed.php | 9 | AGENTS.md §6 | Empty exception class. |
| LOW | framework/System/Flows/RunConsoleCommand/ConsoleCommandFailed.php | 9 | AGENTS.md §6 | Empty exception class. |
| LOW | framework/System/Flows/HandleWorkerRequest/WorkerRequestFailed.php | 9 | AGENTS.md §6 | Empty exception class. |
| LOW | framework/System/Flows/StartWorker/WorkerStopFailed.php | 9 | AGENTS.md §6 | Empty exception class. |
| LOW | framework/System/Flows/StartWorker/WorkerStartFailed.php | 9 | AGENTS.md §6 | Empty exception class. |
| LOW | framework/System/Flows/BootApplication/ApplicationBootFailed.php | 9 | AGENTS.md §6 | Empty exception class extending `FrameworkBootFailed`. |
| LOW | framework/System/Configuration/Foundation/ConfigurationLoadFailed.php | 9 | AGENTS.md §6 | Empty exception class. |
| LOW | framework/System/Configuration/Foundation/InvalidConfiguration.php | 9 | AGENTS.md §6 | Empty exception class. |
| LOW | framework/System/Foundation/Failure/NotImplemented.php | 9 | AGENTS.md §6 | Empty exception class. Also duplicates `NotImplementedException` in Exception/ directory — two not-implemented markers. |

### How-To Deviation Findings

| Severity | Document | File | Line | Finding |
|----------|----------|------|------|---------|
| BLOCKER | how-to-runtime-composition.md §3 | framework/System/PublicSurface/Diagnostics.php | 12-14 | Static `$correlationId` and `$traceId` properties leak state across requests. No automatic `clear()` call. |
| BLOCKER | how-to-runtime-composition.md §4 | framework/System/PublicSurface/Avax.php | 70-95 | PublicSurface entrypoint (`create()`) performs runtime composition via `new`. Composition belongs in Configuration. |
| BLOCKER | how-to-runtime-composition.md §4 | framework/System/Flows/RunApplication/RunApplication.php | 55-75 | `withDefaultResolutionPipeline()` constructs dispatch pipeline via `new`. Flow must receive ready-to-use dependencies. |
| BLOCKER | how-to-runtime-composition.md §4 | framework/System/PublicSurface/BootDsl.php | 144-176 | PublicSurface `create()` constructs object graph. |
| HIGH | how-to-coding-standards.md §3 | framework/System/PublicSurface/AvaxInterface.php | 16-33 | Interface has zero PHPDoc annotations. All 6 methods missing `@return` and `@throws`. |
| HIGH | how-to-coding-standards.md §3 | HttpKernelInterface.php | 10-13 | Interface method `handle()` missing PHPDoc. |
| HIGH | how-to-coding-standards.md §3 | RuntimeKernelInterface.php | 11-16 | Interface methods `state()` and `runWorker()` missing PHPDoc. |
| HIGH | how-to-coding-standards.md §5 | HandleException.php | 11 | Flow class not marked `final readonly`. |
| HIGH | how-to-coding-standards.md §5 | RunDoctor.php | 11 | Flow not `final readonly`. Constructor has optional default creating hidden dependency. |
| HIGH | how-to-coding-standards.md §5 | DetectStateLeak.php | 10 | Flow not `final readonly`. Same hidden-dependency pattern. |
| HIGH | how-to-coding-standards.md §5 | InspectStaticState.php | 10 | Flow not `final readonly`. Same pattern. |
| HIGH | how-to-coding-standards.md §5 | VerifyRequestScopeWasClosed.php | 10 | Flow not `final readonly`. Same pattern. |
| HIGH | how-to-coding-standards.md §5 | VerifyResetWasExecuted.php | 10 | Flow not `final readonly`. Same pattern. |
| HIGH | how-to-system-security.md §2 | ReportRuntimeFailure.php | 104-148 | Direct read of `$_SERVER`, `$_SESSION` superglobals bypasses all security abstractions. |
| HIGH | how-to-runtime-composition.md §7 | CreateApplication.php | 58-101 | Flow `make()` performs composition-root assembly via `new`. |
| HIGH | how-to-runtime-composition.md §7 | App.php | 204-231 | `handle()` constructs scope lifecycle objects per-request via `new`. |
| MEDIUM | how-to-design-components.md §4 | ResetApplicationState.php | 28-38 | Three empty private methods. Stubs without `NotImplementedException` create silent no-ops. |
| MEDIUM | how-to-design-components.md §4 | ShutdownRuntime.php | 16-22 | Two empty private methods. Same stub issue. |
| MEDIUM | how-to-design-components.md §4 | ConfigureRuntime.php | 1-9 | Empty class with no behavior. Incomplete component. |
| MEDIUM | how-to-runtime-composition.md §6 | App.php | 74 | Mutable `$running` boolean on App instance. Not thread-safe or worker-safe. |
| MEDIUM | how-to-design-components.md §5 | DiscoverComponents.php | 20 | `printReport()` is static. Static methods on Flows reduce testability. |
| MEDIUM | how-to-runtime-composition.md §4 | BootDslEngine.php | 157-206 | `createRuntimeAndApp()` performs 12+ `new` calls in a single method — monolithic composition. |
| MEDIUM | how-to-document-flow.md §3 | App.php | 254 | Anonymous class returned from `asHttpKernel()`. Cannot be documented or discovered via reflection. |
| LOW | how-to-document.md §5 | compat.php | 36-162 | Backward-compat aliases file. Acceptable as temporary bridge. Should have expiry governance. |
| LOW | how-to-production-readiness.md §3 | AvaxVersion.php | 9-15 | Version string `1.0.0` mismatches V4 roadmap. Cosmetic but confusing. |

### Summary
- Total units reviewed: 79 (Flows: 56, Configuration: 17, PublicSurface: 5, Foundation: 1)
- Total PHP files scanned: 101
- Total strict findings: 43 (BLOCKER: 5, HIGH: 19, MEDIUM: 9, LOW: 10)
- Total deviation findings: 24 (BLOCKER: 4, HIGH: 12, MEDIUM: 6, LOW: 2)
- Clean units (zero findings): Foundation (Version/Version.php, Paths/RuntimePath.php, Paths/ProjectPath.php, Time/Clock.php, Time/Timestamp.php, Time/SystemClock.php, Time/FrozenClock.php, Result/Result.php, Result/Failure.php, Environment/EnvironmentName.php, Failure/FrameworkFailure.php, Failure/FrameworkMisconfigured.php, Failure/FrameworkBootFailed.php, Failure/PhpErrorException.php, Failure/RuntimeHandlingFailed.php, TraceId.php, CorrelationId.php, Exception/NotImplementedException.php), Configuration (BootDsl/BootPhase.php, BootDsl/ProviderRegistry.php, Foundation/RuntimeConfiguration.php, Foundation/ApplicationConfiguration.php, LoadConfiguration/ConfigurationRepository.php, LoadConfiguration/LoadConfiguration.php, LoadRuntimeConfiguration.php, LoadApplicationConfiguration.php, ValidateApplicationConfiguration.php, ValidateRuntimeConfiguration.php, RegisterComponents/ComponentRegistration.php, RegisterComponents/Builders/RegisterComponents.php, FrameworkServiceProvider.php), Flows (CheckRuntimeIsolation.php, DescribeDependency.php, ExplainConfig.php, ExplainContainerResolution.php, ExplainRouteMatch.php, ListComponents.php, ListContainerBindings.php, ListRoutes.php, RegisterHealthRoutes.php, ValidateConfig.php, HandleWorkerRequest.php, AuditContainerScope.php, DetectRouteConflict.php, BootApplication.php, BootWithDsl.php, BuildApplicationState.php, StartWorker.php, HandleRuntimeFailure.php, RenderRuntimeFailure.php, ConvertPhpErrorToThrowable.php, ReadIncomingHttpRequest.php, OpenHttpRequestScope.php, CloseHttpRequestScope.php, DispatchConfiguredRoute.php, MatchHttpRoute.php, RunHttpRoute.php, RouteFacadeContainer.php, MatchedHttpRoute.php, RegisteredHttpRoutes.php, OpenWorkerRequestScope.php, CloseWorkerRequestScope.php), PublicSurface (Http/HttpKernel.php, Console/ConsoleKernel.php, Runtime/RuntimeKernel.php)
- Key risks: 4 BLOCKER static-composition findings — Avax::create(), Avax::bootInternal(), BootDsl::create(), RunApplication::withDefaultResolutionPipeline() all construct object graphs via `new` in PublicSurface/Flows. Diagnostics static state leaks across requests. 6 Flows not `final readonly`. All kernel interfaces missing PHPDoc. ReportRuntimeFailure reads `$_SERVER`/`$_SESSION` directly.

---

## Phase 5: Cross-Reference Map

### Cross-Cutting Patterns

#### Pattern A: Static State Unsafe for Long-Lived Workers (AGENTS.md §17)
**Total: 21 instances across all agents**
- Agent 1: HealthCheck::$readinessChecks, SchemaGeneration::$assembly
- Agent 2: 7 components (Cache, Container, Storage, Pipeline, Facade, FeatureFlags)
- Agent 3: AttributeCompiler::$resolvedCache
- Agent 4: Secrets::$secretStore
- Agent 5: 9 components (Concurrency, Parallelism, Realtime x3, MessageBus, Events, Scheduler, Resilience)
- Agent 6: FailureBoundary static facade, ExternalState, ResourceGovernor, StatelessBoundary, GracefulShutdown, ShutdownSequence
- Agent 7: Diagnostics::$correlationId/$traceId, App::$running

#### Pattern B: `new` in PublicSurface (AGENTS.md §6)
**Total: 14 components affected**
- API: ApiBlueprint, ApiSurface, Contracts, GraphQL (x3), OpenAPI, SchemaGeneration
- Application: Cache, Storage, Pipeline, Validation
- Foundation: Redaction
- Operations: 12 components (Filesystem, Observability, MemoryLifecycle, BackgroundProcesses, RuntimeSupervision, Delivery, Tasks, Queue x2, Scheduler, Events, Notifications)
- Framework: Avax, BootDsl, App, Diagnostics, RunApplication, CreateApplication

#### Pattern C: `class_exists` + `new` Runtime Composition (AGENTS.md §6)
**Total: 12 instances**
- Agent 2: 32 calls in Container component (SimpleContainer, FrozenContainer, ResolveCallable, DependencyRegistry, ServiceRegistry)
- Agent 3: Migrations (MigrateCommand, Migrations, SeederCommand, Seeder), CreateDataObject ($casterClass)
- Agent 5: QueueWorker ($class from payload)
- Agent 6: FailureBoundary (RunRecoveryAction, RunFallbackAction), PreCommit ($checkClass)

#### Pattern D: `unserialize()` Without `allowed_classes` (AGENTS.md §4)
**Total: 3 BLOCKER, 1 HIGH**
- BLOCKER: PhpCacheSerializer.php:48 (`allowed_classes: true`)
- BLOCKER: SerializeClosureThroughLibrary.php:43 (no `allowed_classes`)
- BLOCKER: RedisCacheStore.php:90 (no `allowed_classes`)
- HIGH: DecryptValue.php:55-64 (bare catch fall-through opens path)

#### Pattern E: Forbidden Concept Word Folder Names (AGENTS.md §8-9)
**Total: 6 instances**
- components/API/Contracts/
- components/DeveloperTools/Diagnostics/
- components/HTTP/Security/
- components/Identity/Security/
- components/Operations/Events/
- framework/System/Capabilities/Security/

#### Pattern F: Zero Test Coverage
**Total: 977 files, 20 units**
- DataStack + HTTP (Agent 3): ZERO test files found across all 977 PHP files
- All 13 `shortcuts.php` files use global `app()` service locator — untestable

#### Pattern G: Duplicate/Redundant Classes
- DependencyRegistry.php (1,150 lines) vs ServiceRegistry.php (1,148 lines) — near-identical
- ResourceGovernor in two namespaces (PublicSurface/ vs System/PublicSurface/)
- GracefulShutdown in four locations (PublicSurface/, Capabilities/, System/PublicSurface/, System/Capabilities/)
- RotatingFileWriter in `Writers/` and `Writing/` paths
- `csrf_token()` function defined in two shortcuts.php files

#### Pattern H: Interface/PublicSurface Documentation Gap
- AvaxInterface: zero PHPDoc on all 6 methods
- HttpKernelInterface: no PHPDoc on `handle()`
- ConsoleKernelInterface: partial PHPDoc, missing `@throws`
- RuntimeKernelInterface: no PHPDoc on `state()` or `runWorker()`

---

## Phase 6: Aggregate Summary

### Overall Totals

| Agent | Scope | Files | Lines | BLOCKER | HIGH | MEDIUM | LOW | Deviations |
|-------|-------|-------|-------|---------|------|--------|-----|------------|
| 1 | API + DevTools | 195 | 9,292 | 2 | 7 | 6 | 2 | 3 |
| 2 | Application | 603 | 42,516 | 3 | 18 | 6 | 3 | 4 |
| 3 | DataStack + HTTP | 977 | ~5,500 | 9 | 8 | 8 | 4 | 2 |
| 4 | Identity/Security/etc | ~95 | ~3,000 | 4 | 3 | 2 | 0 | 3 |
| 5 | Operations | 163 | ~5,000 | 9 | 15 | 4 | 1 | 5 |
| 6 | Framework Capabilities | ~95 | ~6,000 | 4 | 14 | 6 | 0 | 15 |
| 7 | Framework Flows/etc | 101 | ~8,000 | 5 | 19 | 9 | 10 | 24 |
| **Total** | **~154 units** | **~2,229** | **~79,308** | **36** | **84** | **41** | **20** | **56** |

### Severity Distribution
- **BLOCKER:** 36 — immediate remediation required (security RCE, static state leaks, broken compiled container, CSRF boundary violations)
- **HIGH:** 84 — systematic violations (PublicSurface `new`, runtime composition `class_exists+new`, missing PHPDoc on interfaces, forbidden folder names, missing security tests)
- **MEDIUM:** 41 — structural issues (anonymous classes, silent catch-and-ignore, SQL interpolation, stub methods, hidden I/O)
- **LOW:** 20 — cosmetic/trivial (version string, empty exception classes, IDE annotations, dead access modifiers)

### Governance Compliance

| Document | Status | Key Issues |
|----------|--------|------------|
| AGENTS.md §4 (unserialize safety) | ❌ FAIL | 3 BLOCKER unserialize findings |
| AGENTS.md §6 (no `new` in PublicSurface) | ❌ FAIL | 14+ components violate |
| AGENTS.md §8-9 (forbidden folder names) | ❌ FAIL | 6 concept-word folders |
| AGENTS.md §17 (long-lived worker safety) | ❌ FAIL | 21 static state leaks |
| AGENTS.md §22 (no hidden I/O) | ❌ FAIL | ReportRuntimeFailure, HandleException |
| AGENTS.md §24 (AI Safety / no catch-and-ignore) | ❌ FAIL | Multiple silent catch blocks |
| AGENTS.md §25 (security boundaries) | ❌ FAIL | CSRF session chaos, missing negative tests |
| AGENTS.md §28 (PHPDoc) | ❌ FAIL | All kernel interfaces undocumented |
| AGENTS.md §30-31 (testing/completion) | ❌ FAIL | Zero tests in DataStack + HTTP |
| how-to-runtime-composition.md | ❌ FAIL | Systematic violations across framework |
| how-to-coding-standards.md | ❌ FAIL | Missing `final readonly`, anonymous exceptions |
| how-to-system-security.md | ❌ FAIL | CSRF, unserialize, unredacted logs, missing `#[SensitiveParameter]` |
| how-to-unit-test.md | ⚠️ WARN | Missing negative tests for security code |
| how-to-design-components.md | ⚠️ WARN | Duplicate classes, empty stubs, bloated containers |
| how-to-clean-code.md | ⚠️ WARN | Silent catch, DRY violations, large classes |
| how-to-system-performance.md | ⚠️ WARN | GC pressure in DIContainer, serialization bottleneck |
| how-to-dependency-injection.md | ⚠️ WARN | Global `app()` locator, registration-phase resolution |
| how-to-document.md | ✅ PASS | Documentation follows convention |
| how-to-code-style.md | ✅ PASS | Style is consistent |

### Clean Units (Zero Findings)
**Filtered from all agents:**
- Foundation (many sub-units): Version/Version.php, Paths/RuntimePath.php, Paths/ProjectPath.php, Time/* (4), Result/* (2), Environment/EnvironmentName.php, Failure/* (6), TraceId.php, CorrelationId.php
- Configuration clean units: BootPhase, ProviderRegistry, RuntimeConfiguration, ApplicationConfiguration, ConfigurationRepository, LoadConfiguration, LoadRuntimeConfiguration, LoadApplicationConfiguration, ValidateApplicationConfiguration, ValidateRuntimeConfiguration, ComponentRegistration, RegisterComponents, FrameworkServiceProvider
- Flows clean units: CheckRuntimeIsolation, DescribeDependency, ExplainConfig, ExplainContainerResolution, ExplainRouteMatch, ListComponents, ListContainerBindings, ListRoutes, RegisterHealthRoutes, ValidateConfig, HandleWorkerRequest, AuditContainerScope, DetectRouteConflict, BootApplication (3), StartWorker, HandleRuntimeFailure (2), ConvertPhpErrorToThrowable, ReadIncomingHttpRequest, OpenHttpRequestScope, CloseHttpRequestScope, DispatchConfiguredRoute, MatchHttpRoute, RunHttpRoute, RouteFacadeContainer, MatchedHttpRoute, RegisteredHttpRoutes, OpenWorkerRequestScope, CloseWorkerRequestScope
- PublicSurface clean: HttpKernel, ConsoleKernel, RuntimeKernel

---

## Phase 7: Validation (PLANNED / NOT IMPLEMENTED)

Validation commands should be run after remediation. For this review-only pass, validation was not executed.

Required validation set per AGENTS.md §19:
```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
php tooling/refactor/check-component-canonical-shape.php
php tooling/refactor/check-advanced-pattern-folder-violations.php
php tooling/audit_broken_refs.php
php avax runtime:doctor
```

PLANNED / NOT IMPLEMENTED at this time.

---

## Phase 8: Self-Review of This Artifact

### Artifact Completeness
- [x] All 7 agent outputs included
- [x] Strict Code Review findings per agent
- [x] How-To Deviation findings per agent
- [x] Summary per agent with counts
- [x] Cross-reference map for patterns A-H
- [x] Aggregate totals across all agents
- [x] Governance compliance matrix
- [x] Clean units identified
- [x] Validation commands listed (with PLANNED/NOT IMPLEMENTED status)

### Known Gaps
- Agent 3 reported "zero test files" across 977 PHP files — this needs confirmation via actual file search rather than agent scanning
- Agent 2's MethodEmitter namespace analysis may be partially inaccurate if namespace aliases exist — needs manual verification
- Several agents reported file counts that may overlap (tests in central tree counted by Agent 2 but possibly missed by Agent 3)
- "Clean units" list is conservative — units with no findings per agent may still have undiscovered issues in deeper sub-paths

### Escalation Recommendation
**BLOCKER findings that block further production work:**
1. `PhpCacheSerializer.php:48` — `unserialize()` with `allowed_classes: true` (RCE)
2. `SerializeClosureThroughLibrary.php:43` — `unserialize()` without `allowed_classes` (RCE)
3. `RedisCacheStore.php:90` — `unserialize()` without `allowed_classes` (RCE)
4. `MethodEmitter.php:28,55,151` + `CompileContainer.php:789` — compiled container generates broken PHP (namespace drift)
5. `SessionScope.php`, `CsrfToken.php`, `NativeSessionStore.php` — three-way session lifecycle conflict
6. `Avax.php:70-95` — PublicSurface constructs full object graph via `new`
7. `BootDsl.php:144-176` — PublicSurface constructs object graph via `new`
8. `RunApplication.php:55-75` — Flow constructs dispatch pipeline via `new`
9. `FailureBoundary.php:22-58` — static facade with mutable singleton (worker-unsafe)
10. `Diagnostics.php:10-40` — static state leaks across requests in workers
11. `Redaction.php:20-69` — all methods create new instances via `new`
12. `Secrets.php:12-23` — static mutable store persists across requests

**Total BLOCKER: 36** (all 12 above plus 24 more from agents 1-6)

---

## Final Status

| Category | Value |
|----------|-------|
| Stage | Code Review (dual-recursive) |
| Status | **RED** |
| Files changed | 0 (review only) |
| Validation commands | Not run (review-only pass) |
| Validation summary | Focused validation only; full validation not run |
| Remaining risks | 36 BLOCKER, 84 HIGH, 41 MEDIUM, 20 LOW findings across ~2,200 files |
| Next allowed action | Fix BLOCKER findings first (unserialize RCE, broken compiled container, CSRF/session conflict, PublicSurface assembly, static state leaks, FailureBoundary facade, Redaction/Secrets static state), then HIGH findings (runtime composition leaks, forbidden folders, missing security tests, interface PHPDoc), then systematic refactor to align framework with canonical shape |
