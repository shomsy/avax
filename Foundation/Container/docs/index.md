# Container Docs

This component is organized as a screaming runtime architecture.

- Public surface: [`Container`](./Container.md)
- Structural overview: [`architecture.md`](./architecture.md)
- Concepts:
  - [`resolution-flow.md`](./concepts/resolution-flow.md)
  - [`injection-and-instantiation.md`](./concepts/injection-and-instantiation.md)
  - [`scopes.md`](./concepts/scopes.md)
  - [`lifetimes.md`](./concepts/lifetimes.md)
  - [`policies-and-guards.md`](./concepts/policies-and-guards.md)
- Reference terms: [`glossary.md`](./glossary.md)
- Failure handling: [`troubleshooting.md`](./troubleshooting.md)

The component root is the operational repo root for this library component.

The system root is `DependencyInjection/`.

Production code is split into four lanes:

- `DependencyInjection/Flows/`: what the container system does
- `DependencyInjection/Capabilities/`: what flows use to do it
- `DependencyInjection/Configuration/`: how the runtime is assembled
- `DependencyInjection/Foundation/`: tiny neutral primitives only

Current public/system flow entries:

- `RegisterBindings`
- `BootProviders`
- `ResolveService`
- `InvokeCallable`
- `BeginScope`
- `EndScope`
