# AvaX Code Review: How-To Rule Compliance

**Date:** 2026-05-15
**Branch:** main (014e97b3b)
**Scope:** Full codebase audit against `.agents/how-to/how-to-*.md` governance

---

## 1. Runtime Composition Leaks — 197 violations

**Rule violated:** `how-to-runtime-composition.md` §1-2

- `class_exists()` gating in runtime code = **BLOCKER** (§2.1)
- `new Build*` in runtime code = **BLOCKER** (§2.2)
- `new *Middleware` / `*Dispatcher` / `*Resolver` in runtime = **HIGH** (§2.3)
- `->build()` in runtime execution = **HIGH** (§2.4)
- `$middleware[]` / `$pipeline[]` construction in runtime = **HIGH** (§2.5)
- `?? new` fallback in runtime = **BLOCKER** (§2.6)
- `= new ClassName()` default parameter = **HIGH** (§2.7)

**Total:** 162 HIGH + 35 MEDIUM = 197 violations

### Framework — Flows & Capabilities

| File                                                                                                           | Line     | Pattern                    | Severity | Rule §   |
|----------------------------------------------------------------------------------------------------------------|----------|----------------------------|----------|----------|
| `framework/System/Capabilities/RuntimeBoundary/ReactPhpAdapter.php`                                            | 28       | `class_exists()`           | HIGH     | 2.1      |
| `framework/System/Capabilities/RuntimeBoundary/RoadRunnerAdapter.php`                                          | 40       | `class_exists()`           | HIGH     | 2.1      |
| `framework/System/Capabilities/RuntimeSafety/StaticStateScanner.php`                                           | 35       | `class_exists()`           | HIGH     | 2.1      |
| `framework/System/Capabilities/RuntimeSafety/ComponentHealthScanner.php`                                       | 27,51,71 | `class_exists()` x3        | HIGH     | 2.1      |
| `framework/System/Capabilities/RuntimeSafety/StateLeakDetection/StateLeakDetector.php`                         | 90       | `class_exists()`           | HIGH     | 2.1      |
| `framework/System/Capabilities/FailureBoundary/Capabilities/RunRecoveryAction/RunRecoveryAction.php`           | 37       | `class_exists()`           | HIGH     | 2.1      |
| `framework/System/Capabilities/FailureBoundary/Capabilities/CompileFailurePolicies/CompileFailurePolicies.php` | 33       | `class_exists()`           | HIGH     | 2.1      |
| `framework/System/Capabilities/FailureBoundary/Capabilities/HealthCheck/CheckFailureBoundaryHealth.php`        | 73,83    | `class_exists()` x2        | HIGH     | 2.1      |
| `framework/System/Capabilities/FailureBoundary/Capabilities/RunFallbackAction/RunFallbackAction.php`           | 31       | `class_exists()`           | HIGH     | 2.1      |
| `framework/System/Capabilities/FailureBoundary/Foundation/CompiledMethodPolicy.php`                            | 111      | `class_exists()`           | HIGH     | 2.1      |
| `framework/System/Capabilities/FailureBoundary/PublicSurface/FailureBoundary.php`                              | 42       | `new Build*` + `->build()` | HIGH     | 2.2, 2.4 |

**Fix:** Move `class_exists()` and builder instantiation from runtime to `ServiceProvider` / `System/Configuration`.
Inject ready objects via DI.

### Components — API Area (entire area is affected)

| File                                                                         | Line     | Pattern                             | Severity |
|------------------------------------------------------------------------------|----------|-------------------------------------|----------|
| `components/API/OpenAPI/.../BuildOpenApiDocument.php`                        | 18-19    | `new Builder()`                     | HIGH     |
| `components/API/OpenAPI/.../PublicSurface/OpenAPI.php`                       | 23-24    | `new Builder()` + `?? new`          | HIGH     |
| `components/API/Contracts/.../PublicSurface/ApiContracts.php`                | 22,65    | Registry + `?? new`                 | HIGH     |
| `components/API/ApiBlueprint/.../Flows/HandleJsonApiRequest.php`             | 50-55    | `new Builder()` x2 + `->build()` x2 | HIGH     |
| `components/API/ApiBlueprint/.../Flows/BuildJsonApiResponse.php`             | 19-45    | `new Builder()` x3 + `->build()` x4 | HIGH     |
| `components/API/ApiBlueprint/.../PublicSurface/ApiBlueprint.php`             | 56       | `->build()`                         | HIGH     |
| `components/API/ApiBlueprint/.../PublicSurface/ApiSurface.php`               | 58       | `new Builder()`                     | HIGH     |
| `components/API/GraphQL/.../PublicSurface/GraphQL.php`                       | 17,26,42 | `new Builder()` + `?? new` x2       | HIGH     |
| `components/API/GraphQL/.../PublicSurface/GraphQLSchema.php`                 | 52-54    | `?? new` x3                         | HIGH     |
| `components/API/GraphQL/.../PublicSurface/GraphQLExecutor.php`               | 30       | Registry                            | MEDIUM   |
| `components/API/SchemaGeneration/.../ConvertDataObjectShapeToJsonSchema.php` | 104      | `class_exists()`                    | HIGH     |

**Fix:** All API components need ServiceProviders. Extract builder/registry assembly to `System/Configuration/`.

### Components — DataStack

| File                                                               | Pattern                  | Count |
|--------------------------------------------------------------------|--------------------------|-------|
| `DataStack/DataTransfer/.../CompileClassAttributes.php`            | `class_exists()`         | 1     |
| `DataStack/DataTransfer/.../DataFieldType.php`                     | `class_exists()`         | 2     |
| `DataStack/DataTransfer/.../DataShapeCompiler.php`                 | `class_exists()`         | 2     |
| `DataStack/DataTransfer/.../CreateDataObject.php`                  | `class_exists()`         | 1     |
| `DataStack/Database/.../Migrations.php`                            | Registry                 | 1     |
| `DataStack/Database/.../Repository.php`                            | `class_exists()`         | 1     |
| `DataStack/Database/.../Hydrator.php`                              | Lazy singleton           | 1     |
| `DataStack/Database/.../AttributeMetadataReader.php`               | `?? new`                 | 1     |
| `DataStack/Database/.../DatabaseRepository.php`                    | `class_exists()`         | 1     |
| `DataStack/Database/.../IRBuilder.php`                             | `?? new`                 | 1     |
| `DataStack/Database/.../DeadlockDetector.php`                      | `?? new`                 | 1     |
| `DataStack/Database/.../LazyConnectionPool.php`                    | `?? new`                 | 1     |
| `DataStack/Database/.../DatabaseConnectionPool.php`                | `new Builder()`          | 1     |
| `DataStack/Database/.../ReadConnection.php`                        | `new Builder()`          | 1     |
| `DataStack/Database/.../PublicSurface/ManageEntityPersistence.php` | Lazy singleton + Manager | 2     |
| `DataStack/Persistence/.../BuildDataQuery.php`                     | `class_exists()`         | 1     |
| `DataStack/Persistence/.../ExplainDataQuery.php`                   | `?? new`                 | 1     |

### Components — Container (worst offender)

| File                                                      | Pattern                                 | Count |
|-----------------------------------------------------------|-----------------------------------------|-------|
| `Application/Container/.../CompiledRuntime.php`           | Lazy singleton                          | 1     |
| `Application/Container/.../LazyProxy.php`                 | Lazy singleton                          | 1     |
| `Application/Container/.../Lazy.php`                      | Lazy singleton                          | 1     |
| `Application/Container/.../ResolveDependency.php`         | `class_exists()`                        | 1     |
| `Application/Container/.../ResolveDependencies.php`       | `?? new`                                | 1     |
| `Application/Container/.../ServiceResolver.php`           | `class_exists()`                        | 1     |
| `Application/Container/.../CreateDependencyBlueprint.php` | Lazy + `class_exists()`                 | 2     |
| `Application/Container/.../CreateServiceBlueprint.php`    | Lazy + `class_exists()`                 | 2     |
| `Application/Container/.../DependencyRegistry.php`        | `?? new` + `class_exists()` x3          | 4     |
| `Application/Container/.../ServiceRegistry.php`           | `?? new` + `class_exists()` x3          | 4     |
| `Application/Container/.../AssembleRuntime.php`           | Registry + Builder                      | 3     |
| `Application/Container/.../DependencyCompiler.php`        | `class_exists()` x2                     | 2     |
| `Application/Container/.../ServiceCompiler.php`           | `class_exists()` x2                     | 2     |
| `Application/Container/.../CompileContainer.php`          | `?? new` + `class_exists()` x6          | 7     |
| `Application/Container/.../ResolutionPolicy.php`          | `class_exists()` + `interface_exists()` | 2     |
| `Application/Container/.../ResolveCallable.php`           | `class_exists()`                        | 1     |
| `Application/Container/.../FunctionCaller.php`            | `?? new` + `class_exists()` x2          | 3     |
| `Application/Container/.../ResolutionTimeline.php`        | Lazy singleton                          | 1     |
| `Application/Container/.../CheckCompositionPolicies.php`  | `class_exists()` x3                     | 3     |
| `Application/Container/.../BootProviders.php`             | `class_exists()`                        | 1     |

**Fix:** Container component inherently needs `class_exists()` for autowiring. Accept as documented exception or move
discovery to build phase.

### Components — Cache

| File                                                    | Pattern                     | Count |
|---------------------------------------------------------|-----------------------------|-------|
| `Application/Cache/.../CompiledCacheFreshness.php`      | `?? new`                    | 1     |
| `Application/Cache/.../CacheLockTimeout.php`            | Lazy singleton              | 1     |
| `Application/Cache/.../CacheLockOwner.php`              | Lazy singleton x2           | 2     |
| `Application/Cache/.../CacheHealthDetector.php`         | `?? new`                    | 1     |
| `Application/Cache/.../ReadCachedValueFromReplica.php`  | `?? new`                    | 1     |
| `Application/Cache/.../L1MemoryCache.php`               | `?? new`                    | 1     |
| `Application/Cache/.../FileCacheStore.php`              | `?? new`                    | 1     |
| `Application/Cache/.../StoredCacheRecord.php`           | Lazy singleton              | 1     |
| `Application/Cache/.../RedisCacheStore.php`             | `class_exists()` + `?? new` | 2     |
| `Application/Cache/.../RememberCachedValue.php`         | `?? new` x2                 | 2     |
| `Application/Cache/.../CompileCache/CompileCache.php`   | `new Builder()`             | 1     |
| `Application/Cache/.../PublicSurface/CompiledCache.php` | `new Builder()`             | 1     |

### Components — Operations

| File                                                                          | Pattern                       | Severity |
|-------------------------------------------------------------------------------|-------------------------------|----------|
| `Operations/RuntimeSupervision/.../Supervisor.php:28`                         | Registry                      | MEDIUM   |
| `Operations/RuntimeSupervision/.../PublicSurface/RuntimeSupervision.php:19`   | Registry                      | MEDIUM   |
| `Operations/Parallelism/.../SymfonyProcessParallelRuntime.php:143`            | `?? new`                      | HIGH     |
| `Operations/Parallelism/.../PublicSurface/Parallel.php:19`                    | `new Builder()` + `->build()` | HIGH     |
| `Operations/Observability/.../ObservabilityHealthCheck.php:63,95`             | Collector + `class_exists()`  | MEDIUM   |
| `Operations/BackgroundProcesses/.../PublicSurface/BackgroundProcesses.php:21` | Registry                      | MEDIUM   |
| `Operations/Queue/.../Queue.php:27`                                           | Lazy singleton                | HIGH     |
| `Operations/Queue/.../RedisQueue.php:21`                                      | `class_exists()`              | HIGH     |
| `Operations/Queue/.../QueueState.php:84`                                      | Lazy singleton                | HIGH     |
| `Operations/Queue/.../TaskDispatch.php:33-53`                                 | Resolver + Dispatcher x3      | MEDIUM   |
| `Operations/Queue/.../DeferredDispatcher.php:31`                              | Dispatcher                    | MEDIUM   |
| `Operations/Queue/.../SyncDriver.php:32`                                      | `class_exists()`              | HIGH     |
| `Operations/Queue/.../PublicSurface/QueueWorker.php:42`                       | `class_exists()`              | HIGH     |
| `Operations/Logging/.../CheckLoggingHealth.php:29`                            | `class_exists()`              | HIGH     |
| `Operations/Delivery/.../PublicSurface/Delivery.php:16`                       | `new Builder()`               | HIGH     |
| `Operations/Resilience/.../Fallback.php:55`                                   | `?? new`                      | HIGH     |
| `Operations/Resilience/.../RedisRateLimiter.php:29`                           | `class_exists()`              | HIGH     |
| `Operations/Resilience/.../ExecuteWithReliability.php:167`                    | `?? new`                      | HIGH     |
| `Operations/Tasks/.../PublicSurface/Tasks.php:56`                             | `?? new`                      | HIGH     |
| `Operations/Events/.../CheckEventsHealth.php:36,67`                           | Registry x2                   | MEDIUM   |
| `Operations/Events/.../CompileEventListeners.php:36`                          | Registry                      | MEDIUM   |
| `Operations/Events/.../PublicSurface/Events.php:31-32`                        | Registry + Dispatcher         | MEDIUM   |

### Components — HTTP

| File                                                     | Pattern                                                                  | Severity |
|----------------------------------------------------------|--------------------------------------------------------------------------|----------|
| `HTTP/Router/.../CheckRouterHealth.php:32`               | `class_exists()`                                                         | HIGH     |
| `HTTP/System/.../UriBuilder.php:159`                     | `->build()`                                                              | HIGH     |
| `HTTP/System/.../AppKernel.php:84-88`                    | `class_exists()` x2 + `new Builder()` + `new Middleware()` + `->build()` | HIGH     |
| `HTTP/System/.../AppKernel.php:121,126,178`              | `new Middleware()` x3                                                    | MEDIUM   |
| `HTTP/Session/.../RedisSessionStore.php:33`              | `class_exists()`                                                         | HIGH     |
| `HTTP/Session/.../PublicSurface/Session.php:225`         | `?? new`                                                                 | HIGH     |
| `HTTP/ApiVersioning/.../VersionResolver.php:15`          | Lazy singleton + Registry                                                | MEDIUM   |
| `HTTP/ApiVersioning/.../PublicSurface/ApiVersion.php:24` | Registry                                                                 | MEDIUM   |
| `HTTP/Context/.../PublicSurface/HttpContext.php:27`      | Provider                                                                 | MEDIUM   |
| `HTTP/Request/.../CreateRequestFromRuntime.php:45`       | `?? new`                                                                 | HIGH     |
| `HTTP/Client/.../CurlTransport.php:35`                   | `?? new`                                                                 | HIGH     |

### Components — Identity

| File                                                       | Pattern           | Severity |
|------------------------------------------------------------|-------------------|----------|
| `Identity/Auth/.../SessionIdentity.php:50-53`              | Lazy singleton x4 | HIGH     |
| `Identity/Auth/.../TokenStore.php:30`                      | Lazy singleton    | HIGH     |
| `Identity/Tenancy/.../RollbackTenantSecurityChange.php:32` | `?? new`          | HIGH     |

### Components — Security

| File                                                          | Pattern             | Severity |
|---------------------------------------------------------------|---------------------|----------|
| `Security/Cryptography/.../CheckCryptographyHealth.php:36,45` | `class_exists()` x2 | HIGH     |
| `Security/Redaction/.../PolicyEngine.php:15`                  | Engine              | MEDIUM   |
| `Security/Redaction/.../CheckRedactionHealth.php:31`          | Engine              | MEDIUM   |
| `Security/Redaction/.../RedactLogData.php:12`                 | Engine              | MEDIUM   |
| `Security/Redaction/.../ApplyRedactionPolicy.php:13`          | Engine              | MEDIUM   |
| `Security/Redaction/.../PublicSurface/Redaction.php:20,35`    | Engine x2           | MEDIUM   |

### Components — Other

| File                                                                                 | Pattern                                 | Severity |
|--------------------------------------------------------------------------------------|-----------------------------------------|----------|
| `Integration/ObjectStorage/.../StoreObjectsOnS3.php:120`                             | Middleware                              | MEDIUM   |
| `Foundation/CallableSerialization/.../PublicSurface/CallableSerialization.php:28-72` | `new Builder()` x3 + `->build()`        | HIGH     |
| `DeveloperTools/TestSupport/.../ContractVerifier.php:45`                             | `class_exists()` + `interface_exists()` | HIGH     |
| `DeveloperTools/DumpDebugger/.../PublicSurface/shortcuts.php:32,57`                  | `class_exists()` x2                     | HIGH     |

---

## 2. Constructor Bloat — 139 classes with 8+ dependencies

**Rule violated:** `how-to-dependency-injection.md` §3.1, `how-to-clean-code.md` §5.5 — 0-4 normal, 5-7 check, 8+
architecture warning.

### BLOCKER / WARNING (8+ dependencies)

| Class                                                                                  | Dependencies | Severity        |
|----------------------------------------------------------------------------------------|--------------|-----------------|
| `framework/.../Benchmarks/Foundation/BenchmarkResult.php`                              | 18           | WARNING — Split |
| `framework/.../Runtime/Runtime.php`                                                    | 13           | WARNING — Split |
| `framework/.../FailureBoundary/Foundation/FailurePolicy.php`                           | 12           | WARNING — Split |
| `framework/.../Flows/CreateApplication/CreateApplication.php`                          | 10           | WARNING — Split |
| `framework/.../Configuration/RuntimeConfiguration.php`                                 | 9            | WARNING — Split |
| `framework/.../BuildApplication/Builders/ApplicationBuilder.php`                       | 9            | WARNING — Split |
| `framework/.../FailureBoundary/Capabilities/RunFailurePipeline/RunFailurePipeline.php` | 9            | WARNING — Split |
| `framework/.../FailureBoundary/Configuration/FailureBoundaryConfiguration.php`         | 8            | WARNING — Split |
| `framework/.../FailureBoundary/Foundation/CompiledMethodPolicy.php`                    | 8            | WARNING — Split |
| `framework/.../Flows/RunApplication/RunApplication.php`                                | 8            | WARNING — Split |

All 139 WARNING-level findings follow the same pattern: classes doing too much.

### CHECK (5-7 dependencies — design smell)

139 additional classes have 5-7 deps. These are yellow-level: check responsibility but may be acceptable.

---

## 3. Large Units — 1 BLOCKER + 365 REVIEW

**Rule violated:** `how-to-code-review.md` §21 — Builder >300 lines = BLOCKER, class >300 = REVIEW.

### BLOCKER — Must Fix

| File                                                                     | Lines | Type                  |
|--------------------------------------------------------------------------|-------|-----------------------|
| `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php` | 1729  | Builder — **BLOCKER** |

This single file is 1729 lines. It must be split into:

- `RegisterAuthDefaults.php` — default bindings
- `BuildAuthRuntime.php` — runtime graph assembly
- `AssembleAuthCapabilities.php` — capability wiring

### REVIEW (selected worst)

| File                                                                                                | Lines | Type  |
|-----------------------------------------------------------------------------------------------------|-------|-------|
| `components/Application/Container/System/Capabilities/Composition/Compilation/CompileContainer.php` | 681   | Class |
| `components/DataStack/Database/System/Capabilities/ORM/Repository.php`                              | 578   | Class |
| `components/Application/Cache/System/Capabilities/Health/CacheHealthDetector.php`                   | 512   | Class |
| `components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/DataShapeCompiler.php`   | 483   | Class |
| `components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistry.php`  | 478   | Class |
| `components/Application/Container/System/Capabilities/Composition/Compilation/CompileContainer.php` | 459   | Class |

365 total REVIEW-level findings.

---

## 4. Direct Instantiation in Runtime — FAIL (48 findings)

**Rule violated:** `how-to-dependency-injection.md` §3.4-3.5 — `new Class()` FORBIDDEN outside
ServiceProvider/Configuration/Factory/Test.

### Framework Flows — Constructor Default Parameters

| File                                                                               | Line              | Pattern        |
|------------------------------------------------------------------------------------|-------------------|----------------|
| `framework/System/Flows/AuditContainerScope/AuditContainerScope.php`               | 48                | `= new ...`    |
| `framework/System/Flows/HandleIncomingHttp/FrameworkRouteRegistrar.php`            | 124               | `= new ...`    |
| `framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php`            | 23                | `= new ...`    |
| `framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php`                     | 29                | `= new ...`    |
| `framework/System/Flows/HandleIncomingHttp/HandleIncomingHttp.php`                 | 34,38             | `= new ...` x2 |
| `framework/System/Flows/ExplainContainerResolution/ExplainContainerResolution.php` | 39,49,59          | `= new ...` x3 |
| `framework/System/Flows/DescribeDependency/DescribeDependency.php`                 | 39,49,59          | `= new ...` x3 |
| `framework/System/Capabilities/Runtime/MemoryGuard/MonitorWorkerMemory.php`        | 151,162,173       | `= new ...` x3 |
| `framework/System/Capabilities/Runtime/Runtime.php`                                | 99                | `= new ...`    |
| `framework/System/Capabilities/Runtime/WarmApplication/DetectLeakedState.php`      | 85,97             | `= new ...` x2 |
| `framework/System/Capabilities/Runtime/WarmApplication/HandleWarmRequest.php`      | 48-50             | `= new ...` x3 |
| `framework/System/Capabilities/Runtime/ReactPhp/RunReactHttpServer.php`            | 71,74,90,111      | `= new ...` x4 |
| `framework/System/Capabilities/Runtime/Worker/WorkerLoop.php`                      | 21                | `= new ...`    |
| `framework/System/Capabilities/RuntimeSafety/ResetVerifier.php`                    | 117,141           | `= new ...` x2 |
| `framework/System/Capabilities/RuntimeSafety/StaticStateScanner.php`               | 67,81,110,117     | `= new ...` x4 |
| `framework/System/Capabilities/RuntimeSafety/ComponentHealthScanner.php`           | 31,41,55,72,76,86 | `= new ...` x6 |
| `framework/System/Capabilities/RuntimeSafety/StateLeakDetector.php`                | 94,98,141,149     | `= new ...` x4 |
| `framework/System/Capabilities/Security/PolicyEngine/DefinePolicy.php`             | 34,44             | `= new ...` x2 |
| `framework/System/Capabilities/Security/Doctor/CheckSecurityRuntime.php`           | 27,33,40,48,56    | `= new ...` x5 |

**Fix:** Replace `private SomeClass $dep = new SomeClass()` with constructor injection. Move defaults to ServiceProvider
bindings.

---

## 5. ServiceProvider Coverage — 1 MISSING

**Rule violated:** `how-to-dependency-injection.md` §4.0 — every ACTIVE production component needs a real
ServiceProvider.

### Missing

| Component                    | Status                            | Fix                                                          |
|------------------------------|-----------------------------------|--------------------------------------------------------------|
| `DeveloperTools/TestSupport` | Has real code, no ServiceProvider | Create `System/Configuration/TestSupportServiceProvider.php` |

### SCAFFOLD (accepted exceptions — not blocking)

API (5): `ApiBlueprint`, `Contracts`, `GraphQL`, `OpenAPI`, `SchemaGeneration`
Application (2): `Localization`, `FeatureFlags`
CLI (2): `CLI/`, `CLI/Console`
DataStack (3): `Data`, `DataTransfer`, `Persistence`
DeveloperTools (7): all
Foundation (1): `CallableSerialization`
HTTP (6): `AfterResponse`, `ApiVersioning`, `ContentNegotiation`, `Context`, `Dispatcher`, `Security`, `URI`

These are SCAFFOLD per the component status lock — not blocking but should be promoted when the components become
ACTIVE.

---

## 6. Semantic PHPDoc Gaps — 17,245 HIGH

**Rule violated:** `how-to-document.md` — every production class and public/protected method needs semantic PHPDoc.

This is expected for legacy untouched code. The semantic PHPDoc gate reports 17,245 HIGH findings across the entire
codebase. This must be ratcheted down:

- New code: mandatory immediately (per `how-to-document.md` Phased Adoption rule)
- Touched code: fix while touched
- Untouched code: reduce as documentation debt

---

## 7. Public-Specific Violations Summary by Area

| Area                      | Runtime leaks | Constructor bloat | SP missing    | PHPDoc gaps |
|---------------------------|---------------|-------------------|---------------|-------------|
| **framework/**            | 40+           | 40+               | N/A           | 5000+       |
| **API**                   | 11            | 3                 | 5 SCAFFOLD    | 500+        |
| **Application/Container** | 38            | 3                 | OK (SCAFFOLD) | 2000+       |
| **Application/Cache**     | 14            | 2                 | OK            | 800+        |
| **DataStack**             | 19            | 15                | 3 SCAFFOLD    | 2000+       |
| **HTTP**                  | 12            | 8                 | 6 SCAFFOLD    | 1000+       |
| **Identity**              | 6             | 5                 | OK            | 500+        |
| **Operations**            | 25            | 20                | OK            | 2000+       |
| **Security**              | 7             | 4                 | OK            | 500+        |
| **DeveloperTools**        | 2             | 3                 | 7 SCAFFOLD    | 500+        |
| **Foundation**            | 4             | 0                 | 1 SCAFFOLD    | 200+        |

---

## 8. What's Missing for Production-Readiness

Based on `how-to-production-readiness.md` §3:

| Criteria                              | Status                            | Details              |
|---------------------------------------|-----------------------------------|----------------------|
| ✅ Taxonomy integrity                  | GREEN                             | Canonical            |
| ✅ Composer autoload                   | GREEN                             | 9327 classes         |
| ✅ PHPUnit tests                       | GREEN                             | 8351 tests           |
| ✅ PHPStan                             | GREEN                             | 0 errors             |
| ✅ PublicSurface integrity             | GREEN                             | PASS                 |
| ✅ Forbidden folders                   | GREEN                             | PASS                 |
| ✅ Service locator                     | GREEN                             | PASS                 |
| ✅ Namespace drift                     | GREEN                             | PASS                 |
| ❌ **Runtime composition leaks**       | **RED — 197 findings**            | Biggest blocker      |
| ❌ **Direct instantiation in runtime** | **RED — 48 findings**             | Constructor defaults |
| 🟡 Constructor bloat (8+)             | YELLOW — 139                      | Needs phased splits  |
| 🟡 ServiceProvider coverage           | YELLOW — 1 missing, 35 SCAFFOLD   | Needs promotion      |
| 🟡 Semantic PHPDoc                    | YELLOW — 17,245 legacy gaps       | Phased adoption      |
| 🟡 Large unit thresholds              | YELLOW — AuthBuilder 1729 BLOCKER | Split needed         |

**Production-ready blockers (RED):**

1. 197 runtime composition leaks — must be fixed or classified
2. 48 direct instantiation in runtime — must use DI

**Production-ready debt (YELLOW):**

3. AuthBuilder 1729 lines — must be split
4. 139 constructor bloat cases — phased reduction
5. 1 missing ServiceProvider (TestSupport)
6. 17,245 PHPDoc gaps — phased adoption
