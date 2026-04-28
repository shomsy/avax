# DECISIONS

ADR-lite decision log for non-trivial choices.

## Entry Format

- `id`:
- `recorded_at`:
- `decision_at`:
- `updated_at`:
- `status`: proposed | accepted | superseded
- `context`:
- `decision`:
- `consequences`:
- `links`:

## Decisions

- `id`: DEC-008
  `recorded_at`: 2026-04-09 01:28 CEST
  `decision_at`: 2026-04-09 01:28 CEST
  `updated_at`: 2026-04-09 01:28 CEST
  `status`: accepted
  `context`: The package had accumulated a multi-root implementation tree where
  `DependencyInjection/`, `Configuration/`, `Compilation/`, `Runtime/`,
  `Observability/`, and `Errors/` split the engine by technical hallway
  rather than by the intended reading model. The target architecture for this
  component is a `src/` system root that reads public surface first, then
  flows, then shared capability lanes, with `Foundation/` kept tiny and
  honest.
  `decision`: Move the implementation into `src/` with `Container.php`,
  `ContainerInterface.php`, and `ContextContainer.php` at the root public
  surface; make `src/Flows/` the first narrative layer; keep shared engine
  work in `src/Capabilities/Declaration`, `Composition`, `Resolution`,
  `Execution`, `Runtime`, and `Diagnostics`; keep only tiny neutral
  primitives in `src/Foundation/`; and remove the legacy production hallways
  instead of leaving compatibility copies alive.
  `consequences`: The package now reads flow-first at a glance, tests and docs
  must reference `src/` and the new lanes, and future work should extend the
  existing capability lanes instead of reintroducing generic root buckets.
  `CreateContainer`, `ValidateComposition`, `ExplainService`, and
  `ExportGraph` become explicit flow owners, while `ServiceResolver` remains
  a reduced but still important gravity well to keep slimming inside the
  `Resolution` lane rather than by inventing cross-cutting buckets.
  `links`: `AGENTS.md`, `docs/architecture-convergence.md`, `src/Container.php`, `src/ContainerInterface.php`,
  `src/ContextContainer.php`, `src/Flows/`, `src/Capabilities/`, `src/Foundation/`, `tests/Flows/`,
  `tests/Capabilities/`

- `id`: DEC-007
  `recorded_at`: 2026-04-08 20:39 CEST
  `decision_at`: 2026-04-08 20:39 CEST
  `updated_at`: 2026-04-08 20:45 CEST
  `status`: accepted
  `context`: The container needed ownership-scoped resolution without creating a
  separate `SliceContainer` class that would diverge from the existing
  `ContextContainer` pattern.
  `decision`: Implement slice views via `SliceContext` inside the existing context
  system. `forSlice()` on `Container` and `ContextContainer` sets a slice key
  in the context array, and `ServiceResolver` reads it via `SliceContext::from()`
  to filter resolution, diagnostics, and access enforcement.
  `consequences`: Slice views compose with context views by merging context
  arrays. No new container class is needed. The existing `ContextContainer`
  already implements the full `ContainerInterface` including `forSlice()`.
  All diagnostics methods (describe, debug, validate) respect the slice
  context when present.
  `links`: `Container.php`, `ContextContainer.php`, `DependencyInjection/Dependencies/Ownership/SliceContext.php`,
  `DependencyInjection/Dependencies/Resolution/ServiceResolver.php`, `docs/slice-view-contracts.md`,
  `docs/ownership-model.md`

- `id`: DEC-006
  `recorded_at`: 2026-04-08 20:39 CEST
  `decision_at`: 2026-04-08 20:39 CEST
  `updated_at`: 2026-04-09 01:28 CEST
  `status`: superseded
  `context`: The container needed a reusable instance lifetime that is neither
  singleton (one instance forever) nor transient (fresh every time). Workers,
  HTTP pools, and connection adapters need bounded reuse with explicit reset.
  `decision`: Implement pooled lifetime as a bounded bucket inside `ServicePool`
  with checkout/release semantics, scope-anchored usage, and a mandatory
  `ResettableInterface::reset()` contract when reset-before-reuse is
  enabled. Pooled services are built on first resolve, stored in the active
  scope, returned to the idle bucket on scope close, and disposed on overflow
  or unsafe return.
  `consequences`: New `PooledLifetime`, `ResettableInterface`, and pool methods
  on `ServicePool` and `ManageScopes`. Validation covers pooled contract
  violations (POL-007), lifetime capture rules, warm/lazy conflicts, and
  prebuilt instance rejection. Benchmarks include pooled scenarios. Superseded
  by `DEC-008` only for system-root placement; the pooled-lifetime decision
  itself remains active.
  `links`: `src/Capabilities/Runtime/Scopes/Lifetimes/PooledLifetime.php`,
  `src/Capabilities/Runtime/Scopes/ResettableInterface.php`, `src/Capabilities/Runtime/ServicePool.php`,
  `src/Capabilities/Runtime/Scopes/ManageScopes.php`, `src/Capabilities/Resolution/ServiceResolver.php`,
  `docs/pooled-lifetime-contracts.md`, `docs/lifetimes-and-scopes.md`

- `id`: DEC-005
  `recorded_at`: 2026-04-07 04:40 CEST
  `decision_at`: 2026-04-07 04:40 CEST
  `updated_at`: 2026-04-07 04:40 CEST
  `status`: accepted
  `context`: The component keeps `DependencyInjection/` as the system root, but
  the public facade and provider SPI cannot leak runtime-only types or force
  consumers onto the concrete `Container`.
  `decision`: Keep the public surface rooted in `Avax\Container\...` via stable
  root contracts and DTOs, and introduce an internal `RuntimeContainer`
  adapter for nested resolution chains instead of making `Container` itself
  the runtime contract.
  `consequences`: Public API types stay stable and banal, provider ports depend
  on `ContainerInterface`, and internal resolution/injection machinery keeps
  `resolveContext()` behind the runtime boundary.
  `links`: `Container.php`, `ContainerInterface.php`, `BindingBuilderInterface.php`, `ContextBuilderInterface.php`,
  `RegistryInterface.php`, `ScopeManagerInterface.php`, `InjectionReport.php`,
  `DependencyInjection/Capability/Resolution/Kernel/RuntimeContainer.php`

- `id`: DEC-004
  `recorded_at`: 2026-04-07 04:05 CEST
  `decision_at`: 2026-04-07 04:05 CEST
  `updated_at`: 2026-04-07 04:05 CEST
  `status`: accepted
  `context`: The component has a thin public surface at the repo root, but the
  user explicitly requires `DependencyInjection/` to remain as the structural
  system root for the runtime implementation.
  `decision`: Keep `DependencyInjection/` as the system root, with
  `Flow/`, `Capability/`, `Configuration/`, and `Foundation/` nested
  inside it; keep `Container.php` and `ContainerInterface.php` at the
  component root as the public surface.
  `consequences`: The repo root remains operational, the system root remains
  explicit, and docs, tests, and agent metadata must reference
  `DependencyInjection/` as the canonical implementation tree.
  `links`: `Container.php`, `ContainerInterface.php`, `DependencyInjection/`, `docs/architecture.md`

- `id`: DEC-003
  `recorded_at`: 2026-04-07 04:05 CEST
  `decision_at`: 2026-04-07 04:05 CEST
  `updated_at`: 2026-04-07 04:05 CEST
  `status`: accepted
  `context`: The repack established explicit flow/capability/configuration/
  foundation lanes inside the `DependencyInjection/` system root, but the
  runtime still lacked a few explicit owner units required by the target
  architecture: `KernelFacade`, `ContainerConfig`, the `Methods/` and
  `Parameters/` injection lanes, and a canonical `EngineInterface`.
  `decision`: Refine the repacked architecture by introducing `KernelFacade`
  as the internal kernel boundary, `ContainerConfig` as the assembly options
  root, `EngineInterface` as the engine contract name, and explicit
  `MethodInjector` and `ResolveMethodParameters` units inside the injection
  capability.
  `consequences`: The runtime now reads more directly in the target DSL, build
  options have an owned configuration model, injection has honest internal
  lanes, and the architecture reserves `DependencyInjection/Foundation/Time`
  and `DependencyInjection/Foundation/Ids` without inventing fake primitives.
  `links`: `DependencyInjection/Capability/Resolution/Kernel/KernelFacade.php`,
  `DependencyInjection/Configuration/ContainerConfig.php`,
  `DependencyInjection/Capability/Resolution/Engine/EngineInterface.php`,
  `DependencyInjection/Capability/Injection/Methods/MethodInjector.php`,
  `DependencyInjection/Capability/Injection/Parameters/ResolveMethodParameters.php`, `docs/architecture.md`

- `id`: DEC-002
  `recorded_at`: 2026-04-07 04:05 CEST
  `decision_at`: 2026-04-07 04:05 CEST
  `updated_at`: 2026-04-07 04:05 CEST
  `status`: superseded
  `context`: The container component had drifted into `Core/`, `Features/`,
  `Guard/`, and `Observe/` buckets that hid ownership and duplicated runtime
  vocabulary.
  `decision`: Reorganize the implementation into explicit flow, capability,
  configuration, and foundation lanes.
  `consequences`: Superseded by `DEC-004`, which keeps `DependencyInjection/`
  as the system root while preserving the same ownership model.
  `links`: `Container.php`, `DependencyInjection/`, `docs/architecture.md`,
  `../../tests/Foundation/Container/Capability/`

- `id`: DEC-001
  `recorded_at`: 2026-04-07 02:11 CEST
  `decision_at`: 2026-04-07 02:11 CEST
  `updated_at`: 2026-04-07 02:11 CEST
  `status`: accepted
  `context`: Install the reusable Agent Harness into `Foundation/Container`.
  `decision`: Use the harness with the PHP language profile and treat this
  component as a library delivery kind.
  `consequences`: Future agent work will route through the harness contracts,
  and new documentation should stay under `docs/`.
  `links`: `AGENTS.md`, `.agents/README.md`
