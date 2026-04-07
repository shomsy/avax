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

## Compiled Cache Issues

If compiled artifacts are not being used:

1. set `CreateContainerConfig::$cacheDir` to a writable directory
2. keep `cacheVersion` stable for the deployment you want to serve
3. call `warmCompiled()` after registrations are complete
4. call `rebuildCompiled()` after deploys that change class structure

If artifacts look stale, use `flushCompiled()` or bump `cacheVersion`.

## Validation Commands

- lint: `docker run --rm -v "$PWD:/app" -w /app php:8.3-cli sh -lc "find . -name '*.php' -not -path './.agents/*' -print0 | xargs -0 -n1 php -l"`
- smoke suite: `./tests/run-smoke-tests.sh`
