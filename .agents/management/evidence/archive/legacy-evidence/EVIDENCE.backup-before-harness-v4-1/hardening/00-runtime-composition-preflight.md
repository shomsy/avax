# Pass 1 Preflight: Runtime Composition Leak Closure

Date: 2026-05-15
Branch: main
Commit: b4f1e4ee

## Runtime Composition Rule Summary

Runtime code MUST NOT assemble dependencies. Runtime executes. Configuration assembles.

Forbidden in runtime paths (Flows, Capabilities, PublicSurface):

- class_exists() for wiring decisions
- new Build*, new *Middleware, new *Handler, new *Dispatcher, new *Resolver, new *Factory
- builder->build()
- $middleware[] = new ...
- ?? new fallback
- constructor default = new Dependency

Allowed contexts: ServiceProvider, Configuration, Configuration/Builders, explicit factories (result objects only),
tests, tooling, Foundation VOs, DTOs, events, exceptions.

## Known Suspicious Patterns (from exploration + prior evidence)

| #  | File                                                                                               | Pattern                                                                                              | Severity                                         |
|----|----------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------|--------------------------------------------------|
| 1  | framework/System/PublicSurface/App.php:339-348                                                     | new CreateRequestFromGlobals + 7 dependencies per request                                            | BLOCKER — hot path                               |
| 2  | framework/System/PublicSurface/App.php:214,218                                                     | new OpenHttpRequestScope, new CloseHttpRequestScope per handle()                                     | HIGH — per request                               |
| 3  | framework/System/PublicSurface/App.php:278-283                                                     | new RunConsoleCommand, new PreCommit, new PreCommitConfig in asConsoleKernel()                       | MEDIUM — boot-time compatibility                 |
| 4  | framework/System/Flows/HandleIncomingHttp/HandleIncomingHttp.php:34,38                             | new OpenHttpRequestScope, new CloseHttpRequestScope per handle()                                     | HIGH — per request                               |
| 5  | framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php:29                                    | new RouteCollection() + rebuild from RegisteredHttpRoutes per match()                                | HIGH — per request                               |
| 6  | framework/System/PublicSurface/Avax.php:63-74                                                      | new ResponseFactory, new SystemClock, new ProjectPath, new ComponentRegistry, etc. in Avax::create() | MEDIUM — composition root, but in PublicSurface  |
| 7  | framework/System/PublicSurface/Avax.php:90-121                                                     | new ResponseFactory, new HandleIncomingHttp, new BuildApplicationState, etc. in bootInternal()       | MEDIUM — boot-time composition in PublicSurface  |
| 8  | framework/System/Flows/RunApplication/RunApplication.php:61-74                                     | new RouteFacadeContainer, new ResolveCallable, new ControllerResolver, etc. in static factory        | CONFIGURATION_ALLOWED — boot-time factory method |
| 9  | components/Operations/Events/System/Foundation/EventEmitter.php:27-29                              | constructor defaults = new ResolveEventListeners(), = new InvokeEventListener()                      | HIGH — constructor default                       |
| 10 | components/Operations/Events/System/Foundation/GlobalEventListenerState.php:29                     | self::$registry ??= new ListenerRegistry()                                                           | HIGH — ?? new fallback                           |
| 11 | components/Operations/Events/System/Foundation/GlobalEventListenerState.php:56-63                  | new CompiledListenerRegistry, new ResolveEventListeners, new InvokeEventListener in emitter()        | HIGH — runtime lazy composition                  |
| 12 | components/Operations/Concurrency/System/PublicSurface/Concurrency.php:24                          | self::$runtime ??= (new BuildConcurrencyRuntime())->build()                                          | BLOCKER — ?? new Build + ->build()               |
| 13 | components/Operations/Concurrency/System/PublicSurface/Concurrency.php:52                          | new RunConcurrentTasks per call                                                                      | MEDIUM — facade pattern, uses self::runtime()    |
| 14 | components/Operations/Concurrency/System/Flows/RaceTasks/RaceTasks.php:13                          | constructor default = new FiberTaskRuntime()                                                         | HIGH — constructor default                       |
| 15 | components/DataStack/Database/System/Foundation/Lifecycle/GlobalDatabaseLifecycleState.php:43      | new CompiledDatabaseLifecycleRegistry fallback                                                       | MEDIUM — static state fallback                   |
| 16 | components/Application/Container/System/Capabilities/ResolveCallable/ResolveCallable.php:198,209   | class_exists() + new $class()                                                                        | MEDIUM — central resolver, intentional design    |
| 17 | framework/System/Capabilities/PreCommit/PreCommit.php:240,243                                      | new $checkClass()                                                                                    | MEDIUM — dynamic check instantiation             |
| 18 | components/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsOnS3.php:120,139 | ?? new PresignUrlMiddleware, ?? new S3Client                                                         | MEDIUM — lazy infrastructure init                |

## Files Planned for Inspection

### Primary targets (will change):

- framework/System/PublicSurface/App.php
- framework/System/PublicSurface/Avax.php
- framework/System/Flows/HandleIncomingHttp/HandleIncomingHttp.php
- framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php
- framework/System/Flows/RunApplication/RunApplication.php
- components/Operations/Events/System/Foundation/EventEmitter.php
- components/Operations/Events/System/Foundation/GlobalEventListenerState.php
- components/Operations/Concurrency/System/PublicSurface/Concurrency.php
- components/Operations/Concurrency/System/Flows/RaceTasks/RaceTasks.php

### Configuration files (may need updates):

- framework/System/Configuration/FrameworkServiceProvider.php
- framework/System/Configuration/BuildApplication/Builders/ApplicationBuilder.php
- framework/System/Capabilities/FailureBoundary/Configuration/FailureBoundaryServiceProvider.php

### Gate file (will strengthen):

- tooling/refactor/check-runtime-composition-leaks.php

## Gates Planned for Update

1. tooling/refactor/check-runtime-composition-leaks.php — Strengthen: context-aware, no broad allowlists, fail on zero
   files
2. tooling/components/check-component-runtime-assembly.php — Run as validation
3. tooling/governance/check-truth-consistency.php — Run as validation

## Validation Commands

```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests --memory-limit=1G
php tooling/refactor/check-runtime-composition-leaks.php
php tooling/components/check-component-runtime-assembly.php
php tooling/governance/check-truth-consistency.php
```

## Scope Boundaries

### IN SCOPE:

- Hot-path runtime composition leaks
- class_exists() in runtime execution
- new Build* in runtime execution
- new *Middleware in runtime execution
- builder->build() in runtime execution
- ?? new fallback in runtime execution
- constructor default = new Dependency in runtime execution
- Gate hardening

### OUT OF SCOPE (later passes):

- Response layer convergence
- Builder responsibility closure (AuthBuilder, SessionBuilder, etc.)
- Full Router PublicSurface extraction
- ServiceProvider coverage cleanup
- Static State reset proof
- labs/SystemDesignKit cleanup
- GraphQLSchema refactoring
- Workflow refactoring
