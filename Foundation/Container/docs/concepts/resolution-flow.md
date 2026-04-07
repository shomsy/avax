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
- `DependencyInjection/Dependencies/Resolution/ResolveDependencies.php`
- `DependencyInjection/Dependencies/Resolution/BuildService.php`
- `DependencyInjection/Dependencies/Blueprints/CreateServiceBlueprint.php`
- `DependencyInjection/Dependencies/Blueprints/BlueprintCache.php`
- `DependencyInjection/Dependencies/Bindings/ServiceRegistry.php`
- `DependencyInjection/Scopes/ManageScopes.php`
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
5. find a registration or fall back to autowiring
6. apply target-specific overrides when present
7. build constructor arguments
8. create the object or invoke a factory
9. inject properties and methods
10. apply extenders
11. store the result according to lifetime
12. record success or failure

## Missing Service Behavior

If the resolver cannot find a registration and cannot autowire the id, it throws `ServiceNotFoundException`.

## Observability

The resolver exports:

- `container_resolve_total`
- `container_resolve_cached_total`
- `container_resolve_failures_total`
