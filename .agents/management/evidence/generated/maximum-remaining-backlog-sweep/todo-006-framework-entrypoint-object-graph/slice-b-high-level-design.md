# TODO-006 Slice B High-Level Design

## Problem

`BootDsl::create()` is a stable public entrypoint, but it constructs the internal boot engine graph directly:

- `SystemClock`
- `ProjectPath`
- `EnvironmentName`
- `CreateHttpResponse`
- `HandleIncomingHttp`
- `ProviderRegistry`
- `BootDslEngine`

That violates the TODO-006 direction that PublicSurface receives and delegates while Configuration owns assembly.

## Target Design

Introduce a Configuration owner:

- `framework/System/Configuration/BootDsl/BuildBootDslEngine.php`

Responsibilities:

- normalize boot options into framework foundation values
- build the default HTTP response/handler collaborators
- build and configure the provider registry
- return a ready `BootDslEngine`

`BootDsl::create()` keeps public validation and delegates the engine graph to `BuildBootDslEngine`.

## Boundary

PublicSurface:

- stores public DSL choices
- validates required public input
- delegates to Configuration
- returns `App`

Configuration:

- assembles boot engine dependencies
- pins framework provider
- registers user providers
- owns direct construction

Runtime flow:

- unchanged

## Compatibility

No public method name, argument, return type, or exception message should change.

`Avax::dsl()` must still return `BootDsl`, not internal builders.

## Risk

Low to medium:

- `BootDsl::create()` is public API
- graph is boot-time only
- existing tests cover missing project path, app creation, provider lifecycle, and repeated boot safety
