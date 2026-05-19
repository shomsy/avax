# Fix-This Finding Inventory

Date: 2026-05-15

Source: fix-this.md + actual gate output from check-runtime-composition-leaks.php

## Gate Output Summary

| Gate                                 | Findings | Status |
|--------------------------------------|---------:|--------|
| check-runtime-composition-leaks.php  |      163 | FAIL   |
| check-component-runtime-assembly.php |        3 | FAIL   |
| check-public-surface.php             |        0 | PASS   |
| check-hollow-public-surfaces.php     |        0 | PASS   |

## Group A: Runtime Composition Leaks — 163 actual findings

Classified by context:

### A1. Framework Runtime — Needs Fix (hot-path)

These are in framework/System/Capabilities/ and represent actual runtime execution paths:

| File                                               | Pattern                | Severity |
|----------------------------------------------------|------------------------|----------|
| framework/.../ReactPhpAdapter.php:28               | class_exists           | HIGH     |
| framework/.../RoadRunnerAdapter.php:40             | class_exists           | HIGH     |
| framework/.../StaticStateScanner.php:35            | class_exists           | HIGH     |
| framework/.../ComponentHealthScanner.php:27,51,71  | class_exists x3        | HIGH     |
| framework/.../StateLeakDetector.php:90             | class_exists           | HIGH     |
| framework/.../RunRecoveryAction.php:37             | class_exists           | HIGH     |
| framework/.../CompileFailurePolicies.php:33        | class_exists           | HIGH     |
| framework/.../CheckFailureBoundaryHealth.php:73,83 | class_exists x2        | HIGH     |
| framework/.../RunFallbackAction.php:31             | class_exists           | HIGH     |
| framework/.../CompiledMethodPolicy.php:111         | class_exists           | HIGH     |
| framework/.../FailureBoundary.php:42               | new Build* + ->build() | HIGH     |

Count: 14 findings

### A2. Container Component — Needs Exception Policy (compile-phase)

These are in components/Application/Container/ and are inherent to container autowiring.
They need Phase B classification, not Phase A fixes:

| File                          | Pattern                         | Count | Classification   |
|-------------------------------|---------------------------------|------:|------------------|
| CompiledRuntime.php           | Lazy singleton                  |     1 | EXCEPTION_POLICY |
| LazyProxy.php                 | Lazy singleton                  |     1 | EXCEPTION_POLICY |
| Lazy.php                      | Lazy singleton                  |     1 | EXCEPTION_POLICY |
| ResolveDependency.php         | class_exists                    |     1 | EXCEPTION_POLICY |
| ResolveDependencies.php       | ?? new                          |     1 | EXCEPTION_POLICY |
| ServiceResolver.php           | class_exists                    |     1 | EXCEPTION_POLICY |
| CreateDependencyBlueprint.php | Lazy + class_exists             |     2 | EXCEPTION_POLICY |
| CreateServiceBlueprint.php    | Lazy + class_exists             |     2 | EXCEPTION_POLICY |
| DependencyRegistry.php        | ?? new + class_exists x3        |     4 | EXCEPTION_POLICY |
| ServiceRegistry.php           | ?? new + class_exists x3        |     4 | EXCEPTION_POLICY |
| AssembleRuntime.php           | Registry + Builder              |     3 | EXCEPTION_POLICY |
| DependencyCompiler.php        | class_exists x2                 |     2 | EXCEPTION_POLICY |
| ServiceCompiler.php           | class_exists x2                 |     2 | EXCEPTION_POLICY |
| CompileContainer.php          | ?? new + class_exists x6        |     7 | EXCEPTION_POLICY |
| ResolutionPolicy.php          | class_exists + interface_exists |     2 | EXCEPTION_POLICY |
| ResolveCallable.php           | class_exists                    |     1 | EXCEPTION_POLICY |
| FunctionCaller.php            | ?? new + class_exists x2        |     3 | EXCEPTION_POLICY |
| ResolutionTimeline.php        | Lazy singleton                  |     1 | EXCEPTION_POLICY |
| CheckCompositionPolicies.php  | class_exists x3                 |     3 | EXCEPTION_POLICY |
| BootProviders.php             | class_exists                    |     1 | EXCEPTION_POLICY |

Count: 43 findings — EXCEPTION_POLICY (Phase B)

### A3. API Components — Needs Fix

| File                                   | Pattern                         | Count | Severity    |
|----------------------------------------|---------------------------------|------:|-------------|
| BuildOpenApiDocument.php               | new Builder()                   |     2 | HIGH        |
| OpenAPI.php                            | new Builder() + ?? new          |     2 | HIGH        |
| ApiContracts.php                       | Registry + ?? new               |     2 | HIGH/MEDIUM |
| HandleJsonApiRequest.php               | new Builder() x2 + ->build() x2 |     4 | HIGH        |
| BuildJsonApiResponse.php               | new Builder() x3 + ->build() x4 |     8 | HIGH        |
| ApiBlueprint.php                       | ->build()                       |     1 | HIGH        |
| ApiSurface.php                         | new Builder()                   |     1 | HIGH        |
| GraphQL.php                            | new Builder() + ?? new x2       |     3 | HIGH        |
| GraphQLSchema.php                      | ?? new x3                       |     3 | HIGH        |
| GraphQLExecutor.php                    | Registry                        |     1 | MEDIUM      |
| ConvertDataObjectShapeToJsonSchema.php | class_exists                    |     1 | HIGH        |

Count: 28 findings

### A4. DataStack Components — Needs Fix

| File                        | Pattern         | Count |
|-----------------------------|-----------------|------:|
| CompileClassAttributes.php  | class_exists    |     1 |
| DataFieldType.php           | class_exists x2 |     2 |
| DataShapeCompiler.php       | class_exists x2 |     2 |
| CreateDataObject.php        | class_exists    |     1 |
| Migrations.php              | Repository      |     1 |
| CheckDatabaseHealth.php     | class_exists    |     1 |
| Repository.php              | class_exists    |     1 |
| Hydrator.php                | Lazy singleton  |     1 |
| AttributeMetadataReader.php | ?? new          |     1 |
| DatabaseRepository.php      | class_exists    |     1 |
| IRBuilder.php               | ?? new          |     1 |
| DeadlockDetector.php        | ?? new          |     1 |
| LazyConnectionPool.php      | ?? new          |     1 |
| DatabaseConnectionPool.php  | new Builder()   |     1 |
| ReadConnection.php          | new Builder()   |     1 |
| ManageEntityPersistence.php | Lazy + Manager  |     2 |
| BuildDataQuery.php          | class_exists    |     1 |
| ExplainDataQuery.php        | ?? new          |     1 |

Count: 20 findings

### A5. Cache Components — Needs Fix

| File                           | Pattern               | Count |
|--------------------------------|-----------------------|------:|
| CompiledCacheFreshness.php     | ?? new                |     1 |
| CacheLockTimeout.php           | Lazy singleton        |     1 |
| CacheLockOwner.php             | Lazy singleton x2     |     2 |
| CacheHealthDetector.php        | ?? new                |     1 |
| ReadCachedValueFromReplica.php | ?? new                |     1 |
| L1MemoryCache.php              | ?? new                |     1 |
| FileCacheStore.php             | ?? new                |     1 |
| StoredCacheRecord.php          | Lazy singleton        |     1 |
| RedisCacheStore.php            | class_exists + ?? new |     2 |
| RememberCachedValue.php        | ?? new x2             |     2 |
| CompileCache.php               | new Builder()         |     1 |
| CompiledCache.php              | new Builder()         |     1 |

Count: 14 findings

### A6. Operations Components — Needs Fix

| File                              | Pattern                   | Count |
|-----------------------------------|---------------------------|------:|
| Supervisor.php                    | Registry                  |     1 |
| RuntimeSupervision.php            | Registry                  |     1 |
| SymfonyProcessParallelRuntime.php | ?? new                    |     1 |
| Parallel.php                      | new Builder() + ->build() |     2 |
| ObservabilityHealthCheck.php      | Collector + class_exists  |     2 |
| BackgroundProcesses.php           | Registry                  |     1 |
| Queue.php                         | Lazy singleton            |     1 |
| RedisQueue.php                    | class_exists              |     1 |
| QueueState.php                    | Lazy singleton            |     1 |
| TaskDispatch.php                  | Resolver + Dispatcher x3  |     4 |
| DeferredDispatcher.php            | Dispatcher                |     1 |
| SyncDriver.php                    | class_exists              |     1 |
| QueueWorker.php                   | class_exists              |     1 |
| CheckLoggingHealth.php            | class_exists              |     1 |
| Delivery.php                      | new Builder()             |     1 |
| Fallback.php                      | ?? new                    |     1 |
| RedisRateLimiter.php              | class_exists              |     1 |
| ExecuteWithReliability.php        | ?? new                    |     1 |
| Tasks.php                         | ?? new                    |     1 |
| CheckEventsHealth.php             | Registry x2               |     2 |
| CompileEventListeners.php         | Registry                  |     1 |
| Events.php                        | Registry + Dispatcher     |     2 |

Count: 26 findings

### A7. HTTP Components — Needs Fix

| File                         | Pattern                                                        | Count |
|------------------------------|----------------------------------------------------------------|------:|
| CheckRouterHealth.php        | class_exists                                                   |     1 |
| UriBuilder.php               | ->build()                                                      |     1 |
| AppKernel.php                | class_exists x2 + new Builder() + new Middleware() + ->build() |     6 |
| RedisSessionStore.php        | class_exists                                                   |     1 |
| Session.php                  | ?? new                                                         |     1 |
| VersionResolver.php          | Lazy + Registry                                                |     2 |
| ApiVersion.php               | Registry                                                       |     1 |
| HttpContext.php              | Provider                                                       |     1 |
| CreateRequestFromRuntime.php | ?? new                                                         |     1 |
| CurlTransport.php            | ?? new                                                         |     1 |
| CheckFilesystemHealth.php    | ?? new                                                         |     1 |
| Pipeline.php                 | Registry                                                       |     1 |

Count: 17 findings

### A8. Identity Components — Needs Fix

| File                             | Pattern           | Count |
|----------------------------------|-------------------|------:|
| SessionIdentity.php              | Lazy singleton x4 |     4 |
| TokenStore.php                   | Lazy singleton    |     1 |
| RollbackTenantSecurityChange.php | ?? new            |     1 |

Count: 6 findings

### A9. Security Components — Needs Fix

| File                        | Pattern         | Count |
|-----------------------------|-----------------|------:|
| CheckCryptographyHealth.php | class_exists x2 |     2 |
| PolicyEngine.php            | Engine          |     1 |
| CheckRedactionHealth.php    | Engine          |     1 |
| RedactLogData.php           | Engine          |     1 |
| ApplyRedactionPolicy.php    | Engine          |     1 |
| Redaction.php               | Engine x2       |     2 |

Count: 8 findings

### A10. Other Components — Needs Fix

| File                      | Pattern                         | Count |
|---------------------------|---------------------------------|------:|
| StoreObjectsOnS3.php      | Middleware                      |     1 |
| CallableSerialization.php | new Builder() x3 + ->build()    |     4 |
| ContractVerifier.php      | class_exists + interface_exists |     2 |
| shortcuts.php             | class_exists x2                 |     2 |

Count: 9 findings

## Group B: Direct Runtime Instantiation — 48 findings

Constructor default `= new Dependency` patterns in framework/System/Flows/ and Capabilities/.
Overlap with Group A findings but different pattern.

Count: 48 findings (from fix-this.md)

## Group C: Constructor Bloat — 139 classes with 8+ dependencies

| Class                            | Dependencies | Severity      |
|----------------------------------|-------------:|---------------|
| BenchmarkResult.php              |           18 | WARNING       |
| Runtime.php                      |           13 | WARNING       |
| FailurePolicy.php                |           12 | WARNING       |
| CreateApplication.php            |           10 | WARNING       |
| RuntimeConfiguration.php         |            9 | WARNING       |
| ApplicationBuilder.php           |            9 | WARNING       |
| RunFailurePipeline.php           |            9 | WARNING       |
| FailureBoundaryConfiguration.php |            8 | WARNING       |
| CompiledMethodPolicy.php         |            8 | WARNING       |
| RunApplication.php               |            8 | WARNING       |
| + 129 more                       |          5-8 | CHECK/WARNING |

Count: 139 findings

## Group D: Large Units — 1 BLOCKER + 365 REVIEW

| File                    | Lines | Type    | Severity |
|-------------------------|------:|---------|----------|
| AuthBuilder.php         |  1729 | Builder | BLOCKER  |
| CompileContainer.php    |   681 | Class   | REVIEW   |
| Repository.php          |   578 | Class   | REVIEW   |
| CacheHealthDetector.php |   512 | Class   | REVIEW   |
| DataShapeCompiler.php   |   483 | Class   | REVIEW   |
| DependencyRegistry.php  |   478 | Class   | REVIEW   |
| + 359 more              |  >300 | Class   | REVIEW   |

Count: 366 findings (1 BLOCKER + 365 REVIEW)

## Group E: AuthBuilder BLOCKER

| Responsibility         | Target Owner             |
|------------------------|--------------------------|
| Default auth bindings  | RegisterAuthDefaults     |
| Runtime graph assembly | BuildAuthRuntime         |
| Capability wiring      | AssembleAuthCapabilities |
| Configuration          | AuthConfiguration        |
| Provider registration  | AuthServiceProvider      |

Current: 1729 lines in single file
Target: 5-7 files, each under 300 lines

Count: 1 BLOCKER

## Group F: ServiceProvider Coverage

| Component                  | Status              | Needs Provider? |
|----------------------------|---------------------|-----------------|
| DeveloperTools/TestSupport | ACTIVE, no provider | YES             |
| 35 SCAFFOLD components     | SCAFFOLD            | NO (exempt)     |

Count: 1 real finding + 35 exempt

## Group G: Semantic PHPDoc Debt

17,245 files with missing semantic PHPDoc.
Strategy: ratchet, touched files only.

Count: 17,245 (legacy, YELLOW with ratchet)

## V5.9 Blocker Summary

| Group                           |      Count | V5.9 Blocker?     | Fix Phase                       |
|---------------------------------|-----------:|-------------------|---------------------------------|
| A. Runtime Composition Leaks    | 163 actual | YES               | Phase A (120) + Phase B (43)    |
| B. Direct Runtime Instantiation |         48 | YES               | Phase C                         |
| C. Constructor Bloat            |        139 | CONDITIONAL       | Phase G (top 10)                |
| D. Large Units                  |        366 | YES (AuthBuilder) | Phase D (AuthBuilder) + Phase G |
| E. AuthBuilder BLOCKER          |          1 | YES               | Phase D                         |
| F. ServiceProvider Coverage     |          1 | YES               | Phase E                         |
| G. Semantic PHPDoc              |     17,245 | NO (ratchet)      | Phase F                         |
| H. Security/Performance         |        TBD | TBD               | Phase H                         |
| I. Gate Proof                   |     2 FAIL | YES               | Phase I                         |

## Inventory Totals

- Actual runtime composition findings: 163
- Container exception policy findings: 43 (subset of 163, Phase B)
- Non-container runtime leaks to fix: 120
- Direct instantiation findings: 48
- AuthBuilder BLOCKER: 1
- ServiceProvider gaps: 1
- Constructor bloat: 139 (review)
- Large units: 366 (1 BLOCKER + 365 REVIEW)
- PHPDoc debt: 17,245 (legacy ratchet)
