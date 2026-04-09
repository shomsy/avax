# Resolution Flow

`ResolveService` is the public flow for turning an id into a value or object.

## Entry Path

Public entry:

- `Container::get()`
- `Container::make()`
- `Container::forContext()`

Flow entry:

- `src/Flows/ResolveService/ResolveService.php`

Runtime owner:

- `src/Capabilities/Resolution/ServiceResolver.php`

Main collaborators:

- `src/Capabilities/Resolution/ResolveRequest.php`
- `src/Capabilities/Resolution/ResolvePlan.php`
- `src/Capabilities/Resolution/ResolveDependencies.php`
- `src/Capabilities/Execution/BuildService.php`
- `src/Capabilities/Declaration/Blueprints/CreateServiceBlueprint.php`
- `src/Capabilities/Declaration/Blueprints/BlueprintCache.php`
- `src/Capabilities/Declaration/Bindings/ServiceRegistry.php`
- `src/Capabilities/Runtime/Scopes/ManageScopes.php`
- `src/Capabilities/Composition/Compilation/CompileContainer.php`
- `src/Capabilities/Composition/Compilation/ArtifactMetadata.php`
- `src/Capabilities/Composition/Compilation/CompileReport.php`
- `src/Capabilities/Runtime/CompiledRuntime.php`
- `src/Capabilities/Declaration/Providers/DeferredProviderRegistry.php`
- `src/Capabilities/Runtime/HotPathInliner.php`
- `src/Capabilities/Runtime/ServicePool.php`
- `src/Capabilities/Execution/Injection/Properties/InjectProperties.php`
- `src/Capabilities/Execution/Injection/Methods/InjectMethods.php`
- `src/Capabilities/Resolution/ResolutionPolicy.php`
- `src/Capabilities/Diagnostics/Observability/ResolutionTelemetry.php`

## What Happens

Resolution runs in this order:

1. record resolution start
2. check shared or scoped storage
3. enforce policy
4. detect circular chains
5. try the generated compiled runtime artifact for the requested service id
6. if no compiled hot path applies, find a registration or fall back to autowiring
7. apply target-specific overrides when present
8. read context values for matching scalar arguments when a `forContext()` view is in play
9. load a compiled blueprint and resolve plan from memory or disk
10. build constructor arguments from the compiled plan
11. create the object or invoke a factory
12. inject properties and methods from compiled metadata
13. apply extenders and decorators
14. store the result according to lifetime in the shared pool or active scope
15. record success or failure

Compiled artifact handling is mode-aware:

- checksum or metadata corruption quarantines the artifact
- config, environment, compile-mode, and strictness mismatches make the artifact incompatible instead of available
- development mode can fall back to dynamic resolution
- production-style modes fail closed on corrupt compiled artifacts
- artifact freshness can be revalidated against per-service signatures before the hot path is reused
- `compileReport()` exposes the current artifact fingerprint, changed service ids, invalidated service ids, invalidation reasons, validation issues, and typed lifetime plans
- `runtimeReport()` exposes the current runtime revisions, diagnostics mode, deferred provider ownership, scope snapshot, lazy service set, metrics, and timeline

## Missing Service Behavior

If the resolver cannot find a registration and cannot autowire the id, it throws `ServiceNotFoundException`.

## Observability

The resolver exports:

- `container_resolve_total`
- `container_resolve_cached_total`
- `container_resolve_failures_total`
- `container_compile_total`
- `container_compiled_warmups_total`
- `container_compiled_flushes_total`
- `container_compiled_rebuilds_total`
- `container_compiled_container_hits_total`
- `container_compiled_container_misses_total`
- `container_compiled_container_stale_total`
- `container_compiled_container_corrupt_total`
- `container_compiled_container_quarantines_total`
- `container_compiled_container_reuses_total`
- `container_compiled_container_resolve_total`
- `container_calls_total`
- `container_injections_total`
- `container_lazy_proxy_requests_total`
- `container_blueprint_cache_memory_hits_total`
- `container_blueprint_cache_disk_hits_total`
- `container_blueprint_cache_misses_total`
- `container_blueprint_compiles_total`

## Deferred Services

`defer()` keeps a registration out of the default compile warmup unless it is explicitly requested or pulled in by a compiled dependency.

Deferred services still resolve on demand. They just do not get promoted into the hot path unless the runtime actually needs them.

Providers can also be deferred. When a provider implements `DeferredProviderInterface`, it stays out of eager boot and registers or boots only when one of its declared service ids is resolved for the first time.

## Context Views

`forContext()` returns a view over the same container that can feed scalar constructor, callable, and injection arguments by name.

The same context values work in both the dynamic resolver and the compiled runtime path.
