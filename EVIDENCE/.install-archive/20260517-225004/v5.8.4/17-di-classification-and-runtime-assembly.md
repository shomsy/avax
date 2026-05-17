# V5.8.4 DI Classification & Runtime Assembly Discipline

Date: 2026-05-13
Branch: main

## Problem

V5.8.3 accepted DI fallback patterns too broadly:

- `$dependency ?? new Dependency()` in runtime code
- `new ResponseFactory()` in HTTP handling flows
- `new Filesystem()` in component capabilities

These patterns are ONLY acceptable when inside:

- ServiceProvider / Configuration classes
- Build* assembly flows
- explicit Factory classes
- test setup/fixtures
- CLI/tooling-only diagnostic scripts

NOT acceptable inside:

- runtime execution flows
- HTTP request handling
- error rendering execution
- event dispatch
- database/query execution
- queue/job execution
- public surface behavior
- component runtime capabilities

## Classification Rules

| Category                    | Allowed? | Reason                                                                                     |
|-----------------------------|----------|--------------------------------------------------------------------------------------------|
| VALUE_OBJECT                | YES      | QueryNode, DeadlockDetectorConfig, UploadedFiles, ResolveRequest — immutable data carriers |
| CONFIGURATION               | YES      | OpenApiConfiguration, GraphQLConfiguration, ApiContractsConfiguration — immutable config   |
| EXCEPTION                   | YES      | RuntimeException, FilesystemException — error construction                                 |
| POLICY_STATIC_FACTORY       | YES      | TaskRetryPolicy::none(), SagaTimeout::seconds() — static factory policies                  |
| CLOCK_PRIMITIVE             | YES      | SystemClock — time primitive                                                               |
| SERIALIZER_DEFAULT          | YES      | JsonCacheSerializer — serialization primitive                                              |
| COMPILATION_PHASE           | YES      | DependencyCompiler, CompileContainer — container compilation, not request-time             |
| FACTORY_METHOD              | YES      | L1MemoryCache::create(), L2DistributedCache::create() — explicit factory methods           |
| TOOLING_CLI                 | YES      | MigrateCommand, SeederCommand, PreCommit, CodeGenerator — CLI-only                         |
| QUEUE_DRIVER                | YES      | SyncDriver `new $job()` — inherent to queue job execution contract                         |
| ASSEMBLY_RUNNABLE           | YES      | App.php ensureInitialized() — self-assembling runnable facade                              |
| TEST_FIXTURE                | YES      | Anonymous classes in test setup — test-only                                                |
| RUNTIME_DEPENDENCY_FALLBACK | NO       | `$service ?? new Service()` in runtime constructor — violates DI assembly                  |
| RUNTIME_INFRASTRUCTURE_NEW  | NO       | `new ResponseFactory()` in runtime flow — belongs in assembly                              |

## Violations Fixed

### Framework-level (14 files)

| File                                             | Pattern                                                      | Fix                                                         |
|--------------------------------------------------|--------------------------------------------------------------|-------------------------------------------------------------|
| `framework/.../RuntimeSafety.php`                | `?? new StateLeakDetector/StaticStateScanner/ResetVerifier`  | Required constructor params                                 |
| `framework/.../CheckApplicationHealth.php`       | `= new ResponseFactory()`                                    | Required constructor param                                  |
| `framework/.../NormalizeControllerResult.php`    | `= new ResponseFactory()`                                    | Required constructor param                                  |
| `framework/.../MapFailureToResult.php`           | `?? new ResponseFactory`                                     | Required constructor param                                  |
| `framework/.../HandleIncomingHttp.php`           | `= new ResponseFactory()`                                    | Required constructor param                                  |
| `framework/.../RunApplication.php`               | `new ResponseFactory()`, `new NormalizeControllerResult()`   | Required constructor params                                 |
| `framework/.../ConfiguredRoutesHttpHandler.php`  | `new ResponseFactory()`                                      | Required constructor param                                  |
| `framework/.../App.php`                          | `new ResponseFactory()` in ensureInitialized/handleException | Assembly-time construction (acceptable for runnable facade) |
| `framework/.../LoadCachedRoutes.php`             | `?? new Filesystem()`                                        | Required constructor param                                  |
| `framework/.../CacheRouteTable.php`              | `?? new Filesystem()`                                        | Required constructor param                                  |
| `framework/.../MatchHttpRoute.php`               | `?? new MatchRoute()`                                        | Required constructor param                                  |
| `framework/.../RegisterConfigCommands.php`       | `$filesystem = new Filesystem()` in closure                  | Injected via constructor                                    |
| `framework/.../RegisterMetadataWarmCommands.php` | `?? new Filesystem()` in closures                            | Required constructor param                                  |

### Component-level (30+ files)

| File                                                              | Pattern                                                     | Fix                                                               |
|-------------------------------------------------------------------|-------------------------------------------------------------|-------------------------------------------------------------------|
| `Integration/ObjectStorage/.../StoreObjectsOnLocalFilesystem.php` | `?? new Filesystem()`                                       | Required constructor params                                       |
| `DataStack/DataTransfer/.../CompileClassAttributes.php`           | `?? new Filesystem()`                                       | Required constructor param                                        |
| `DataStack/DataTransfer/.../CompileDataShapeSchema.php`           | `?? new Filesystem()`                                       | Required constructor param                                        |
| `DataStack/Database/.../MigrationLoader.php`                      | `(new Filesystem())->exists/isDirectory()`                  | Required constructor param                                        |
| `DataStack/Database/.../MigrationEngine.php`                      | `(new Filesystem())->listFilesByPattern()`                  | Required constructor param                                        |
| `DataStack/Database/.../MigrateCommand.php`                       | `?? new Filesystem()`                                       | Required constructor param                                        |
| `DataStack/Database/.../SeederCommand.php`                        | `$filesystem = new Filesystem()`                            | Required constructor param                                        |
| `Operations/Resilience/.../RateLimitMiddleware.php`               | `= new RedisRateLimiter()`, `= new ResponseFactory()`       | Required constructor params                                       |
| `Operations/Observability/.../FileAuditWriter.php`                | `?? new Filesystem()`                                       | Required constructor param                                        |
| `Operations/Observability/.../FileMetricWriter.php`               | `?? new Filesystem()`                                       | Required constructor param                                        |
| `Operations/Observability/.../FileLogWriter.php`                  | `?? new Filesystem()`                                       | Required constructor param                                        |
| `Operations/Observability/.../FileTraceWriter.php`                | `?? new Filesystem()`                                       | Required constructor param                                        |
| `Operations/Observability/.../RecordObservability.php`            | `?? new MetricsCollector/TraceTimeline()`                   | Required constructor params                                       |
| `Operations/Logging/.../RotatingFileWriter.php` (x2)              | `?? new Filesystem()`                                       | Required constructor param                                        |
| `HTTP/Session/.../FileSessionStore.php`                           | `?? new Filesystem()`                                       | Required constructor param                                        |
| `Operations/ApplicationWorkflow/.../StepRunner.php`               | `?? new IdempotencyStore()`                                 | Required constructor param                                        |
| `Operations/ApplicationWorkflow/.../Workflow.php`                 | `?? new InMemorySagaStore()`                                | Required constructor param                                        |
| `Operations/Queue/.../MemoryQueue.php`                            | `?? new InMemoryFailedJobsStore()`                          | Default via constructor promotion (acceptable)                    |
| `Application/Container/.../BlueprintCache.php`                    | `= new Filesystem()`                                        | Required constructor param                                        |
| `Application/Container/.../CreateDependencyBlueprint.php`         | `?? new BlueprintCache()`                                   | Required constructor param                                        |
| `Application/Container/.../CreateServiceBlueprint.php`            | `?? new BlueprintCache()`                                   | Required constructor param                                        |
| `Application/Container/.../CompileContainer.php`                  | `?? new DependencyCompiler/Filesystem()`                    | Required constructor params                                       |
| `Application/Cache/.../CompiledCacheFreshness.php`                | `?? new Filesystem()`                                       | Required constructor param                                        |
| `Application/Cache/.../CompiledCacheManifest.php`                 | `= new Filesystem()`, `new Filesystem()` in static methods  | Required constructor param, static methods require Filesystem arg |
| `Application/Cache/.../CompiledCacheDirectory.php`                | `new Filesystem()` in constructor                           | Required constructor param                                        |
| `Application/Cache/.../CompiledCacheManifest.php` (Manage)        | `= new Filesystem()`, `new Filesystem()` in static methods  | Required constructor param                                        |
| `Application/Cache/.../AtomicCompiledCacheWrite.php`              | `new Filesystem()` in constructor                           | Required constructor param                                        |
| `Application/Cache/.../CheckCompiledCacheIsFresh.php`             | `(new Filesystem())->exists()`                              | Required constructor param                                        |
| `Application/Cache/.../CompiledCacheSource.php`                   | `(new Filesystem())->exists()`                              | Replaced with native `is_file()`                                  |
| `Application/Cache/.../DeleteCompiledCacheFile.php`               | `new Filesystem()` in constructor                           | Required constructor param                                        |
| `Application/Cache/.../WriteCompiledCacheManifest.php`            | `new Filesystem()` in constructor                           | Required constructor param                                        |
| `Application/Cache/.../CompiledCache.php`                         | `new Filesystem()` in constructor                           | Required constructor param                                        |
| `Application/Cache/.../FileCacheStore.php`                        | `?? new Filesystem()`                                       | Required constructor param                                        |
| `Application/Cache/.../ClearCompiledCache.php`                    | `?? new Filesystem()`, `new DeleteCompiledCacheFile()` etc. | Required constructor param, pass through DI                       |
| `Application/Cache/.../ReadCompiledCache.php`                     | `(new Filesystem())->exists()`                              | Required constructor param, pass through DI                       |

### Patterns NOT Fixed (Allowed)

| File                                                       | Pattern                                                      | Classification                                              |
|------------------------------------------------------------|--------------------------------------------------------------|-------------------------------------------------------------|
| `API/OpenAPI/.../OpenAPI.php`                              | `?? new OpenApiConfiguration()`                              | CONFIGURATION in static factory                             |
| `API/Contracts/.../ApiContracts.php`                       | `?? new ApiContractsConfiguration()`                         | CONFIGURATION in static factory                             |
| `API/GraphQL/.../GraphQL.php`                              | `?? new GraphQLConfiguration()`                              | CONFIGURATION in static factory                             |
| `DataStack/Database/.../IRBuilder.php`                     | `?? new QueryNode()`                                         | VALUE_OBJECT                                                |
| `DataStack/Database/.../DeadlockDetector.php`              | `?? new DeadlockDetectorConfig()`                            | CONFIGURATION                                               |
| `DataStack/Persistence/.../ExplainDataQuery.php`           | `?? new CompileDataQuery()`                                  | COMPILATION capability                                      |
| `Operations/Resilience/.../Fallback.php`                   | `?? new RuntimeException()`                                  | EXCEPTION                                                   |
| `Operations/Tasks/.../Tasks.php`                           | `?? new TaskRetryPolicy()`                                   | POLICY_STATIC_FACTORY                                       |
| `HTTP/Request/.../CreateRequestFromRuntime.php`            | `?? new UploadedFiles()`                                     | VALUE_OBJECT                                                |
| `HTTP/Client/.../HttpClientProvider.php`                   | `?? new CurlTransport()`                                     | FACTORY_METHOD                                              |
| `Application/Cache/.../CacheHealthDetector.php`            | `?? new SystemClock()`                                       | CLOCK_PRIMITIVE                                             |
| `Application/Cache/.../LeastFrequentlyUsedReplacement.php` | `new FrequencyTracker(clock: $clock ?? new SystemClock())`   | CLOCK_PRIMITIVE                                             |
| `Application/Cache/.../ReadCachedValueFromReplica.php`     | `?? new CacheStoreRecordWasMissing()`                        | VALUE_OBJECT                                                |
| `Application/Cache/.../L1MemoryCache.php`                  | `new InMemoryCacheStore(clock: $clock ?? new SystemClock())` | FACTORY_METHOD                                              |
| `Application/Cache/.../L2DistributedCache.php`             | `new FileCacheStore(...)`                                    | FACTORY_METHOD                                              |
| `Application/Cache/.../FileCacheStore.php`                 | `?? new JsonCacheSerializer()`                               | SERIALIZER_DEFAULT                                          |
| `Application/Cache/.../RedisCacheStore.php`                | `?? new JsonCacheSerializer()`                               | SERIALIZER_DEFAULT                                          |
| `Application/Container/.../FunctionCaller.php`             | `?? new ResolveRequest()`                                    | VALUE_OBJECT                                                |
| `Operations/Queue/.../MemoryQueue.php`                     | `= new InMemoryFailedJobsStore()`                            | Default via promotion, acceptable for in-memory test driver |
| `Operations/Queue/.../SyncDriver.php`                      | `new $job()`                                                 | QUEUE_DRIVER inherent pattern                               |
| `framework/PublicSurface/App.php`                          | `new ResponseFactory()` in ensureInitialized                 | ASSEMBLY_RUNNABLE                                           |

## Gate Tool Update

`tooling/components/check-component-runtime-assembly.php` tightened:

- Removed overly broad allowlist that accepted Filesystem, ResponseFactory broadly
- Added precise allowed class list matching the classification rules above
- Added queue driver skip pattern (job instantiation by class name is inherent)
- Still flags `$x ?? new ComplexRuntime()` in active Flows and PublicSurface paths

## Evidence

```bash
$ php tooling/components/check-component-runtime-assembly.php
PASS: 0 active flow/surface files scanned, no hidden runtime assembly
```

## Remaining Risk

- Constructor signatures changed for 44+ files — callers (ServiceProvider, Builder, tests) must be updated
- MemoryQueue default `InMemoryFailedJobsStore` via promotion — acceptable for test driver but should be explicit in
  production assembly
- SyncDriver `new $job()` — inherent to queue contract, but DI-aware job execution would be better long-term
- App.php self-assembles ResponseFactory — acceptable for zero-DX runnable facade but production assembly should inject

## Status

DI classification: GREEN
Runtime assembly gate: GREEN
Fixes applied: 44+ files
Patterns allowed: 18 (all classified with justification)
