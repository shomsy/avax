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

- `id`: DEC-004
  `recorded_at`: 2026-04-07 04:05 CEST
  `decision_at`: 2026-04-07 04:05 CEST
  `updated_at`: 2026-04-07 04:05 CEST
  `status`: accepted
  `context`: The component has a thin public surface at the repo root, but the
    user explicitly requires `DependencyInjection/` to remain as the structural
    system root for the runtime implementation.
  `decision`: Keep `DependencyInjection/` as the system root, with
    `Flows/`, `Capabilities/`, `Configuration/`, and `Foundation/` nested
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
  `links`: `DependencyInjection/Capabilities/Resolution/Kernel/KernelFacade.php`, `DependencyInjection/Configuration/ContainerConfig.php`, `DependencyInjection/Capabilities/Resolution/Engine/EngineInterface.php`, `DependencyInjection/Capabilities/Injection/Methods/MethodInjector.php`, `DependencyInjection/Capabilities/Injection/Parameters/ResolveMethodParameters.php`, `docs/architecture.md`

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
  `links`: `Container.php`, `DependencyInjection/`, `docs/architecture.md`, `../../tests/Foundation/Container/Capabilities/`

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
