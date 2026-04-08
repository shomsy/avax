# Architecture

## Reading Order

Read the component in this order:

1. [`Container.php`](../Container.php)
2. flow entries under `DependencyInjection/Flows/`
3. dependency owners under `DependencyInjection/Dependencies/`
4. injection owners under `DependencyInjection/Injection/`
5. scope owners under `DependencyInjection/Scopes/`
6. compiled runtime owners under `Compilation/` and `Runtime/`
7. root support areas: `Configuration/`, `Observability/`, `Errors/`, `Foundation/`
8. tests under `tests/`
9. benchmark harnesses under `tests/benchmarks/`

That order matches the intended mental model:

- file says the action
- folder says the owned work area
- `Container` stays thin

## Public Surface

- `Container.php`: stable facade
- `ContainerInterface.php`: public contract

The facade delegates to the explicit flow owners:

- `CreateContainer`
- `RegisterServices`
- `ResolveService`
- `CallFunction`
- `OpenScope`
- `CloseScope`
- `BootProviders`

The facade also exposes lifecycle, context, and diagnostics helpers:

- `flush()` / `reset()`
- `bootProviders()`
- `forContext()`
- `validate()`
- `describeService()` and the `debug*()` helpers
- `compileReport()` and `runtimeReport()`
- `hasAlias()` / `isDeferred()` / `isLazy()` / `isCompiled()` / `isWarmedUp()`
- `env()`

`CreateContainer` stays the only public assembly flow, while internal collaborator wiring is split under `Configuration/Assembly/`.

## Flow Entries

Each flow entry owns one obvious user action:

- `CreateContainer`: assemble the runtime
- `RegisterServices`: write registrations
- `ResolveService`: read and build services
- `CallFunction`: execute callables through the container
- `OpenScope`: enter a scope
- `CloseScope`: leave a scope
- `BootProviders`: register then boot providers
- `RegisterServices` also carries `defer()` for lazy compilation/loading

## Dependency Areas

`DependencyInjection/Dependencies/Bindings/`

- stores service registrations
- stores target-specific overrides
- stores tags and extenders

`DependencyInjection/Dependencies/Resolution/`

- owns the runtime resolver
- resolves constructor arguments
- decides whether a request is allowed
- delegates compiled hot-path attachment to `CompiledRuntime`
- delegates deferred provider ownership and lazy boot to `DeferredProviderRegistry`
- coordinates runtime storage and build path
- keeps the compiled runtime attached when it is still valid for the current revision
- falls back to dynamic resolution for services that stay outside the compiled hot path

`DependencyInjection/Dependencies/Blueprints/`

- reflects classes once, then writes compiled blueprints to disk
- stores constructor and injection metadata plus compiled resolve plans
- serves memory hits first, then versioned disk artifacts

`Compilation/`

- writes and reads the generated compiled runtime artifact
- writes sidecar metadata with checksum, config hash, environment, lifetime plans, and per-service signatures
- enforces artifact compatibility across cache version, config hash, environment, compile mode, and strictness
- records reused and invalidated services plus invalidation reasons for incremental recompilation
- quarantines corrupt artifacts before they can silently drift into the hot path
- turns compiled blueprints into direct service methods
- owns compile-time fingerprinting for the hot path
- exposes typed artifact metadata and compile reports instead of implicit array schemas

`Runtime/`

- owns shared singleton storage
- owns lazy proxy behavior
- owns compiled method call caching

`Configuration/Assembly/`

- keeps assembly internals out of `CreateContainer`
- wires observability, runtime, and seeded system services into one container instance

`DependencyInjection/Dependencies/Providers/`

- keeps the provider contract boundary
- owns the deterministic provider boot plan
- supports deferred provider loading when a provider implements `DeferredProviderInterface`

## Injection Area

`DependencyInjection/Injection/Properties/`

- injects public and non-public properties

`DependencyInjection/Injection/Methods/`

- injects marked methods after construction

`DependencyInjection/Injection/Reports/`

- describes what can be injected

`DependencyInjection/Injection/Invocation/`

- resolves callable arguments
- normalizes call targets
- invokes callables through the container

`DependencyInjection/Injection/Attributes/`

- keeps opt-in injection markers

## Scopes

`DependencyInjection/Scopes/`

- owns scoped storage and scope lifecycle
- exposes scope lifecycle control
- defines lifetime names

## Root Support Areas

`Configuration/`

- owns assembly options, cache versioning, and runtime settings
- includes env-backed lookups for container-owned configuration hooks

`Observability/`

- records metrics
- records timeline events
- exports telemetry
- exposes a machine-readable runtime report
- supports `minimal` and `detailed` diagnostics modes so production can stay low-overhead while CI/debug runs keep richer traces

`Errors/`

- owns container-facing exception boundaries

`Foundation/`

- keeps tiny neutral primitives only

## Naming Rules In This Repo

- flow files use verb-first names
- work areas use plain nouns
- method names describe the exact action
- generic buckets are not canonical anymore

## Quality Boundary

The shipped boundary is defended by:

- Docker PHP lint across all repo PHP files
- Docker smoke tests under `tests/`
- benchmark harnesses under `tests/benchmarks/`
- benchmark regression guard under `tests/check-benchmarks.sh`
- worker and request-lifecycle benchmark scenarios inside the benchmark harness
- benchmark artifact comparison under `tests/run-benchmark-comparison.sh`
- benchmark guard uses repeated runs with median timing so CI thresholds are reproducible instead of single-shot noisy

There is no Composer or PHPUnit harness in this component root today.

## Performance Model

The runtime is designed around one rule:

- reflect once
- compile once
- run many times from memory or versioned disk artifacts

Deferred services stay out of the default compile warmup unless they are explicitly requested or needed by a compiled dependency.

Compile-time discipline is mode-aware:

- `production`: fastest path, checksum-validated artifacts, fail closed on corruption
- `dev`: checksum-validated artifacts with dynamic fallback on corruption, staleness, or compatibility mismatch
- `ci` / `warmup`: validate before compile and reject invalid graphs before a compiled artifact is accepted
