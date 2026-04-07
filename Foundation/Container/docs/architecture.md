# Architecture

## Reading Order

Read the component in this order:

1. [`Container.php`](../Container.php)
2. flow entries under `DependencyInjection/Flows/`
3. dependency owners under `DependencyInjection/Dependencies/`
4. injection owners under `DependencyInjection/Injection/`
5. scope owners under `DependencyInjection/Scopes/`
6. root support areas: `Configuration/`, `Observability/`, `Errors/`, `Foundation/`
7. tests under `tests/`

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

## Flow Entries

Each flow entry owns one obvious user action:

- `CreateContainer`: assemble the runtime
- `RegisterServices`: write registrations
- `ResolveService`: read and build services
- `CallFunction`: execute callables through the container
- `OpenScope`: enter a scope
- `CloseScope`: leave a scope
- `BootProviders`: register then boot providers

## Dependency Areas

`DependencyInjection/Dependencies/Bindings/`

- stores service registrations
- stores target-specific overrides
- stores tags and extenders

`DependencyInjection/Dependencies/Resolution/`

- owns the runtime resolver
- resolves constructor arguments
- decides whether a request is allowed
- owns the runtime storage and build path

`DependencyInjection/Dependencies/Blueprints/`

- creates cached reflection blueprints
- stores constructor and injection metadata

`DependencyInjection/Dependencies/Providers/`

- keeps the provider contract boundary

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

- owns shared and scoped storage
- exposes scope lifecycle control
- defines lifetime names

## Root Support Areas

`Configuration/`

- owns assembly options and runtime settings

`Observability/`

- records metrics
- records timeline events
- exports telemetry

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

There is no Composer or PHPUnit harness in this component root today.
