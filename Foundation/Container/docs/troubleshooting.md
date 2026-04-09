# Troubleshooting

## Service Not Found

Check these in order:

1. Is the id explicitly registered?
2. Is the target a real class or interface?
3. Is the target class instantiable?
4. Is a target-specific override pointing somewhere unexpected?

Missing services now raise `ServiceNotFoundException`.

## Scoped Service Fails

If a scoped service throws during storage:

1. open a scope first with `openScope()`
2. or run the work inside `scopes()->withinScope(...)`

Scoped services do not fall back to shared storage anymore.

If the service uses `request`, `job`, `tenant`, or `operation` lifetime:

1. open the matching scope kind explicitly
2. inspect the error message for the required scope kind
3. inspect `describeService()['lifetimePlan']` when the active scope is unclear

## Property Or Method Injection Missing

Check:

1. the member is marked with `#[Inject]`
2. the dependency id can be resolved
3. readonly properties are not being targeted

## Callable Invocation Fails

Check:

1. the target is a closure, callable array, `Class@method`, `Class::method`, or invokable class string
2. the target class itself can be resolved
3. required arguments can be resolved or overridden

## Provider Lifecycle Issues

Provider order is deterministic:

1. every provider `register()`
2. then every provider `boot()`

If boot fails, verify that register phase wrote the required services first.

If providers depend on each other, make sure each provider reports the dependency in `dependsOn()`.

If a provider should boot lazily instead of during `bootProviders()`:

1. declare `deferred(): bool` and return `true`
2. implement `DeferredProviderInterface` and declare `provides(): array` with every service id the provider owns
3. resolve one of those services to trigger the deferred provider boot

If deferred or lazy behavior looks inconsistent:

1. check `debugService()` for `deferred`, `lazy`, `cacheState`, and `compiledState`
2. inspect `debugService()['explain']['fallback']` to see why the compiled path was skipped or why dynamic fallback is
   active
3. inspect `debugService()['explain']['dependencyChain']` when a deferred provider owns a transitive dependency
4. rebuild compiled artifacts if the deferred graph changed after warmup

If warm versus lazy shared behavior is surprising:

1. inspect `describeService()['lifetimePlan']['warm']`
2. inspect `describeService()['lifetimePlan']['lazy']`
3. remember that `warmCompiled()` skips lazy shared services on purpose

## Compiled Cache Issues

If compiled artifacts are not being used:

1. set `CreateContainerConfig::$cacheDir` to a writable directory
2. keep `cacheVersion` stable for the deployment you want to serve
3. call `warmCompiled()` after registrations are complete
4. call `rebuildCompiled()` after deploys that change class structure

If artifacts look stale, use `flushCompiled()` or bump `cacheVersion`.

If the compiled artifact is incompatible with the current runtime:

1. inspect `compileReport()->compatible`
2. inspect `compileReport()->compatibilityIssues`
3. inspect `compileReport()->freshnessState`
4. confirm whether `configHash`, `environment`, `compileMode`, `diagnosticsMode`, or `strict` changed between compile
   and load
4. rebuild the artifact instead of assuming the hot path is still valid

If the compiled artifact is corrupt:

1. production-style compile modes fail closed and quarantine the artifact
2. development mode quarantines the artifact and falls back to dynamic resolution
3. quarantined artifacts move under the compiled artifact `quarantine/` directory instead of being silently overwritten
4. inspect `describeService()` or `debugService()` to confirm whether the service is still compiled or running
   dynamically

If a compiled service is still resolving dynamically, it may be outside the compiled hot path on purpose. Check
`describeService()` and `debugPlan()` before assuming a cache miss.

If `debugService()` shows `decision=dynamic`, inspect:

1. `debugService()['compiledState']['reason']`
2. `debugService()['explain']['fallback']`
3. `debugService()['compiledArtifact']['compatibilityIssues']`
4. `debugService()['compiledArtifact']['warnings']`

If a compile report shows invalidated services:

1. inspect `compileReport()->invalidatedServices`
2. inspect `compileReport()->invalidationReasons`
3. confirm whether the change was a service signature change, dependency graph change, or missing previous compiled
   source

## Context Views

If `forContext()` does not feed a scalar argument:

1. make sure the parameter name matches the context key exactly
2. make sure the target actually expects a scalar or non-service argument
3. check whether the service is a singleton that was already resolved with an earlier context

Context values are used as named fallbacks, not as a second service registry.

If a runtime input is missing:

1. inspect the error message for the missing input name
2. pass the value through `make(..., ['name' => ...])`
3. or create a `forContext([...])` view
4. or add a default value when the input is optional

## Lifecycle Reset

If tests or long-running workers need a clean slate:

1. call `reset()` to clear pools, scopes, lazy markers, telemetry state, and other disposable runtime caches while
   keeping registrations and compiled artifacts
2. call `flush()` when you also want compiled artifacts and derived caches cleared
3. authored registrations stay intact across both operations

Both operations are deterministic and leave the container ready for another resolve cycle.

If worker or request state appears to leak:

1. confirm each request opens and closes its own scope
2. call `reset()` between jobs in a long-running worker
3. inspect `runtimeReport()->sharedServiceCount` and `runtimeReport()->scopedServiceCount`
4. inspect `debugScope()` to confirm there is no leftover scoped frame

## Ownership And Slice Violations

If a service fails with an ownership or slice error:

1. inspect `describeService()['ownership']`
2. inspect `debugGraph()` for slice manifests, dependents, and policy warnings
3. make sure shared cross-slice dependencies are both `export()`-ed and `import()`-ed
4. keep `private` and `internal` services inside the owning slice
5. if the service is profile-bound, confirm `profiles([...])` matches the active environment

Common failure patterns:

1. a flow depends on a capability internal instead of its exported shared surface
2. a shared dependency is not explicitly exported
3. a consumer slice forgot to declare its import
4. a shared service captures a scoped dependency and fails validation
5. two slices declare the same concept name and trigger duplicate-concept diagnostics

If composition conditions are involved:

1. inspect `describeService()['conditions']`
2. inspect `debugGraph()['conditions']`
3. verify `app_env`, `composition.flags`, `composition.tenant`, `composition.region`, and `composition.mode`
4. inspect `describeService()['overrides']` when a rebound abstract changed ownership or visibility posture

If override diagnostics appear:

1. inspect `debugGraph()['overrides']`
2. make the override explicit with `overrideSource()`
3. avoid overlapping ownership posture changes under the same composition conditions

## Status Helpers

If you need to know what the container thinks right now:

1. `hasAlias()` checks alias presence without resolving anything
2. `isDeferred()` reports deferred registration state
3. `isLazy()` reports whether a lazy proxy has been requested in this runtime
4. `isCompiled()` reports whether one service id is present in the compiled artifact
5. `isWarmedUp()` reports whether a compiled artifact is available for this runtime
6. `runtimeReport()` exposes whether diagnostics mode is `minimal`, `detailed`, or `ci`
7. `runtimeReport()` also exposes whether the timeline is enabled plus shared/scoped counts and the current hot-path
   summary

## Policy And Structure Diff

If the graph looks wrong even though resolution still works:

1. inspect `debugGraph()['policyFindings']`
2. inspect `debugGraph()['structureDiff']`
3. compile once, change the authored graph, then use structure diff to see what drifted from the compiled artifact

## Validation Commands

- lint:
  `docker run --rm -v "$PWD:/app" -w /app php:8.3-cli sh -lc "find . -name '*.php' -not -path './.agents/*' -print0 | xargs -0 -n1 php -l"`
- smoke suite: `./tests/run-smoke-tests.sh`
- benchmarks: `./tests/run-benchmarks.sh`
- benchmark guard: `./tests/check-benchmarks.sh`
- benchmark comparison: `./tests/run-benchmark-comparison.sh peer=/absolute/path/to/peer-report.json`
- benchmark comparison writes its temporary artifacts outside the repo and cleans them up automatically
