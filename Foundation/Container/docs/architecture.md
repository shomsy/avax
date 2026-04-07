# Architecture

## Reading Order

Read this component in this order:

1. `Container.php`
2. `DependencyInjection/Flows/`
3. `DependencyInjection/Capabilities/`
4. `DependencyInjection/Configuration/`
5. `DependencyInjection/Foundation/`

That is the canonical mental model:

- folder says flow or capability
- unit says responsibility
- function says exact action

## Repo Root vs System Root

The component root is the operational repo root.

The system root is `DependencyInjection/`.

Repo-root concerns such as docs, agents, root compatibility guides, and the public
surface stay outside the structural story. Inside `DependencyInjection/`, the
architecture must scream.

## Public Surface

- `Container.php`: stable facade
- `ContainerInterface.php`: public contract

`Container` is intentionally thin. It delegates to explicit flow entry units and does not own runtime machinery.

## Flows

Flows are system behaviors, not business use cases.

- `DependencyInjection/Flows/RegisterBindings`: write-side registration flow
- `DependencyInjection/Flows/BootProviders`: deterministic register-then-boot provider lifecycle
- `DependencyInjection/Flows/ResolveService`: service lookup and build flow
- `DependencyInjection/Flows/InvokeCallable`: callable execution flow
- `DependencyInjection/Flows/BeginScope`: scope entry flow
- `DependencyInjection/Flows/EndScope`: scope exit flow

## Capabilities

Capabilities are shared runtime abilities used by more than one flow.

- `DependencyInjection/Capabilities/Definitions`: bindings, contextual rules, extenders, definition storage
- `DependencyInjection/Capabilities/Providers`: provider contracts and built-in provider implementations
- `DependencyInjection/Capabilities/Resolution`: kernel runtime, kernel facade, engine, pipeline, resolution errors
- `DependencyInjection/Capabilities/Injection`: property, method, and parameter injection support
- `DependencyInjection/Capabilities/Invocation`: callable normalization and execution
- `DependencyInjection/Capabilities/Scopes`: scope storage and lifetime strategies
- `DependencyInjection/Capabilities/Prototypes`: reflection analysis, prototype cache, prototype models, factories
- `DependencyInjection/Capabilities/Policies`: resolution policy decisions
- `DependencyInjection/Capabilities/Observability`: metrics, trace, timeline, telemetry

## Configuration

`DependencyInjection/Configuration/` assembles the runtime. It owns:

- `ContainerBuilder`
- `ContainerConfig`
- `KernelConfig`
- `KernelConfigFactory`
- `AppFactory`
- `Settings`

Configuration wires collaborators. It must not absorb business or runtime behavior that belongs to flows/capabilities.

## Foundation

`DependencyInjection/Foundation/` is reserved for tiny neutral primitives only.

The current placeholders are:

- `DependencyInjection/Foundation/Time`
- `DependencyInjection/Foundation/Ids`

They are intentionally quiet until real primitives exist. The point is to reserve honest homes without inventing fake
helpers.

## Root Owner Patterns

- `Container`: facade
- `RegisterBindings`: coordinator
- `BootProviders`: coordinator
- `ResolveService`: flow entry over the resolution capability
- `ContainerKernel`: kernel owner
- `KernelFacade`: internal kernel facade over runtime/state/stores
- `ResolutionPipeline`: pipeline
- provider interfaces: ports
- most capability units: explicit owner units, not forced facades

## Removed Legacy Shape

These roots are no longer canonical:

- `Core/`
- `Features/`
- `Guard/`
- `Observe/`
- `Http/`
- `Providers/` as a top-level story
- `Tools/`

Those names described technical buckets. The new tree describes ownership.

The `DependencyInjection/` root stays intentionally. It is the system root, not
a compatibility layer.
