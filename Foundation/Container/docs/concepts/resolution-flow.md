# Resolution Flow

`ResolveService` is the public flow for turning an id into a value or object.

## Entry Path

Public entry:

- `Container::get()`
- `Container::make()`

Flow entry:

- `DependencyInjection/Flows/ResolveService.php`

Runtime owner:

- `DependencyInjection/Dependencies/Resolution/ServiceResolver.php`

Main collaborators:

- `DependencyInjection/Dependencies/Resolution/ResolveRequest.php`
- `DependencyInjection/Dependencies/Resolution/ResolvePlan.php`
- `DependencyInjection/Dependencies/Resolution/ResolveDependencies.php`
- `DependencyInjection/Dependencies/Resolution/BuildService.php`
- `DependencyInjection/Dependencies/Blueprints/CreateServiceBlueprint.php`
- `DependencyInjection/Dependencies/Blueprints/BlueprintCache.php`
- `DependencyInjection/Dependencies/Bindings/ServiceRegistry.php`
- `DependencyInjection/Scopes/ManageScopes.php`
- `Compilation/CompileContainer.php`
- `Runtime/HotPathInliner.php`
- `Runtime/ServicePool.php`
- `DependencyInjection/Injection/Properties/InjectProperties.php`
- `DependencyInjection/Injection/Methods/InjectMethods.php`
- `DependencyInjection/Dependencies/Resolution/ResolutionPolicy.php`
- `Observability/ResolutionTelemetry.php`

## What Happens

Resolution runs in this order:

1. record resolution start
2. check shared or scoped storage
3. enforce policy
4. detect circular chains
5. try the generated compiled runtime artifact for the requested service id
6. if no compiled hot path applies, find a registration or fall back to autowiring
7. apply target-specific overrides when present
8. load a compiled blueprint and resolve plan from memory or disk
9. build constructor arguments from the compiled plan
10. create the object or invoke a factory
11. inject properties and methods from compiled metadata
12. apply extenders and decorators
13. store the result according to lifetime in the shared pool or active scope
14. record success or failure

## Missing Service Behavior

If the resolver cannot find a registration and cannot autowire the id, it throws `ServiceNotFoundException`.

## Observability

The resolver exports:

- `container_resolve_total`
- `container_resolve_cached_total`
- `container_resolve_failures_total`
- `container_compile_total`
- `container_compiled_container_hits_total`
- `container_compiled_container_misses_total`
- `container_compiled_container_resolve_total`
- `container_blueprint_cache_memory_hits_total`
- `container_blueprint_cache_disk_hits_total`
- `container_blueprint_cache_misses_total`
- `container_blueprint_compiles_total`
