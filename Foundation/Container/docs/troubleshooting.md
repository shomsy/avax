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

## Compiled Cache Issues

If compiled artifacts are not being used:

1. set `CreateContainerConfig::$cacheDir` to a writable directory
2. keep `cacheVersion` stable for the deployment you want to serve
3. call `warmCompiled()` after registrations are complete
4. call `rebuildCompiled()` after deploys that change class structure

If artifacts look stale, use `flushCompiled()` or bump `cacheVersion`.

If a compiled service is still resolving dynamically, it may be outside the compiled hot path on purpose. Check `describeService()` and `debugPlan()` before assuming a cache miss.

## Context Views

If `forContext()` does not feed a scalar argument:

1. make sure the parameter name matches the context key exactly
2. make sure the target actually expects a scalar or non-service argument
3. check whether the service is a singleton that was already resolved with an earlier context

Context values are used as named fallbacks, not as a second service registry.

## Lifecycle Reset

If tests or long-running workers need a clean slate:

1. call `flush()` to clear user registrations, pools, scopes, and compiled artifacts
2. call `reset()` when you want the same clean-runtime boundary under a clearer name

Both operations are deterministic and leave the container ready for a fresh bootstrap.

## Validation Commands

- lint: `docker run --rm -v "$PWD:/app" -w /app php:8.3-cli sh -lc "find . -name '*.php' -not -path './.agents/*' -print0 | xargs -0 -n1 php -l"`
- smoke suite: `./tests/run-smoke-tests.sh`
- benchmarks: `./tests/run-benchmarks.sh`
