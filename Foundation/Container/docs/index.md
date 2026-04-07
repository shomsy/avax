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

The component root is also the system root. There is no extra `src/` hallway.

Production code is split into four lanes:

- `Flows/`: what the container system does
- `Capabilities/`: what flows use to do it
- `Configuration/`: how the runtime is assembled
- `Foundation/`: tiny neutral primitives only

Current public/system flow entries:

- `RegisterBindings`
- `BootProviders`
- `ResolveService`
- `InvokeCallable`
- `BeginScope`
- `EndScope`
