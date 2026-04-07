# Resolution Flow

`ResolveService` is the system flow for turning an identifier into a value or object.

## Entry Path

Public entry:

- `Container::get()`
- `Container::make()`

Runtime entry:

- `ContainerRuntimeInterface::resolveContext()`
- `DependencyInjection/Capabilities/Resolution/Kernel/RuntimeContainer.php`

Flow entry:

- `DependencyInjection/Flows/ResolveService/ResolveService.php`

Runtime owner:

- `DependencyInjection/Capabilities/Resolution/Kernel/ContainerKernel.php`
- `DependencyInjection/Capabilities/Resolution/Kernel/KernelFacade.php`

Ordered execution:

- `DependencyInjection/Capabilities/Resolution/Pipeline/ResolutionPipeline.php`

## What Happens

The resolution pipeline runs these concerns in order:

1. check current scope storage
2. guard depth and circular chains
3. enforce policy
4. ensure a definition exists or decide whether autowiring is allowed
5. analyze or load prototype metadata
6. ask the resolution engine for an instance
7. inject properties and methods
8. apply extenders
9. invoke post-construct hooks
10. store the instance according to lifetime

## Shared Machinery

The flow depends on these capability slices:

- `Definitions`
- `Resolution`
- `Prototypes`
- `Injection`
- `Invocation`
- `Scopes`
- `Policies`
- `Observability`

## Why It Is A Flow

`ResolveService` is a real system behavior with one public intent: resolve something.

The pipeline, engine, policies, and scopes are not the flow. They are the shared abilities the flow uses.
