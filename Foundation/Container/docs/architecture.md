# Architecture

## Reading Order

Read this component in this order:

1. `Container.php`
2. `Flows/`
3. `Capabilities/`
4. `Configuration/`
5. `Foundation/`

That is the canonical mental model:

- folder says flow or capability
- unit says responsibility
- function says exact action

## System Root

The component root is the system root.

Repo root concerns such as tests, docs, agents, and tooling stay outside the structural story. Inside the component
root, the architecture must scream.

## Public Surface

- `Container.php`: stable facade
- `ContainerInterface.php`: public contract

`Container` is intentionally thin. It delegates to explicit flow entry units and does not own runtime machinery.

## Flows

Flows are system behaviors, not business use cases.

- `Flows/RegisterBindings`: write-side registration flow
- `Flows/BootProviders`: deterministic register-then-boot provider lifecycle
- `Flows/ResolveService`: service lookup and build flow
- `Flows/InvokeCallable`: callable execution flow
- `Flows/BeginScope`: scope entry flow
- `Flows/EndScope`: scope exit flow

## Capabilities

Capabilities are shared runtime abilities used by more than one flow.

- `Capabilities/Definitions`: bindings, contextual rules, extenders, definition storage
- `Capabilities/Providers`: provider contracts and built-in provider implementations
- `Capabilities/Resolution`: kernel runtime, kernel facade, engine, pipeline, resolution errors
- `Capabilities/Injection`: property, method, and parameter injection support
- `Capabilities/Invocation`: callable normalization and execution
- `Capabilities/Scopes`: scope storage and lifetime strategies
- `Capabilities/Prototypes`: reflection analysis, prototype cache, prototype models, factories
- `Capabilities/Policies`: resolution policy decisions
- `Capabilities/Observability`: metrics, trace, timeline, telemetry

## Configuration

`Configuration/` assembles the runtime. It owns:

- `ContainerBuilder`
- `ContainerConfig`
- `KernelConfig`
- `KernelConfigFactory`
- `AppFactory`
- `Settings`

Configuration wires collaborators. It must not absorb business or runtime behavior that belongs to flows/capabilities.

## Foundation

`Foundation/` is reserved for tiny neutral primitives only.

The current placeholders are:

- `Foundation/Time`
- `Foundation/Ids`

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
