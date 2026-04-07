# Architecture

## Reading Order

Read the component in this order:

1. `Container.php`
2. root flow entries under `DependencyInjection/`
3. internal work areas under `DependencyInjection/*`
4. tests under `tests/DependencyInjection/*`

That order matches the intended mental model:

- file says the action
- folder says the owned work area
- `Container` stays thin

## Public Surface

- `Container.php`: stable facade
- `ContainerInterface.php`: public contract

The facade delegates to small flow owners:

- `RegisterServices`
- `ResolveService`
- `CallFunction`
- `OpenScope`
- `CloseScope`

`BootProviders` and `CreateContainer` stay explicit but outside the facade because they are assembly and lifecycle flows, not always-on facade methods.

## Flow Entries

Every root file under `DependencyInjection/` owns one obvious action:

- `CreateContainer`: assemble the runtime
- `RegisterServices`: write registrations
- `ResolveService`: read and build services
- `CallFunction`: execute callables through the container
- `OpenScope`: enter a scope
- `CloseScope`: leave a scope
- `BootProviders`: register then boot providers

## Internal Work Areas

`DependencyInjection/Registrations/`

- stores service registrations
- stores tags and extenders
- stores target-specific overrides

`DependencyInjection/Resolution/`

- owns the runtime resolver
- builds constructor arguments
- creates and caches blueprints
- decides storage after resolution

`DependencyInjection/Calls/`

- normalizes callables
- resolves callable arguments

`DependencyInjection/Injection/`

- injects properties
- injects methods
- reports injectable targets

`DependencyInjection/Scopes/`

- owns shared and scoped storage
- exposes scope lifecycle control
- defines lifetime names

`DependencyInjection/Providers/`

- keeps only the provider contract

`DependencyInjection/Configuration/`

- keeps assembly options and settings

`DependencyInjection/Observability/`

- records metrics
- records timeline events
- exports telemetry

`DependencyInjection/Policies/`

- decides whether a resolution request is allowed

`DependencyInjection/Errors/`

- owns container-facing exception boundaries

`DependencyInjection/Foundation/`

- keeps tiny neutral primitives only

## Naming Rules In This Repo

- root flow files use verb-first names
- work areas use plain nouns
- method names describe the exact action
- generic buckets such as `Core`, `Features`, `Builder`, and old `Flow/Capability/` halls are not canonical anymore

## Current Quality Boundary

The shipped boundary is defended by:

- Docker PHP lint across all repo PHP files
- Docker smoke tests under `tests/DependencyInjection/*`

There is no Composer or PHPUnit harness in this component root today.
