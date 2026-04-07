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

- `id`: DEC-003
  `recorded_at`: 2026-04-07 04:05 CEST
  `decision_at`: 2026-04-07 04:05 CEST
  `updated_at`: 2026-04-07 04:05 CEST
  `status`: accepted
  `context`: The first repack established `Flows/`, `Capabilities/`,
    `Configuration/`, and `Foundation/`, but the runtime still lacked a few
    explicit owner units required by the target architecture: `KernelFacade`,
    `ContainerConfig`, the `Methods/` and `Parameters/` injection lanes, and a
    canonical `EngineInterface`.
  `decision`: Refine the repacked architecture by introducing `KernelFacade`
    as the internal kernel boundary, `ContainerConfig` as the assembly options
    root, `EngineInterface` as the engine contract name, and explicit
    `MethodInjector` and `ResolveMethodParameters` units inside the injection
    capability.
  `consequences`: The runtime now reads more directly in the target DSL, build
    options have an owned configuration model, injection has honest internal
    lanes, and the architecture reserves `Foundation/Time` and
    `Foundation/Ids` without inventing fake primitives.
  `links`: `Capabilities/Resolution/Kernel/KernelFacade.php`, `Configuration/ContainerConfig.php`, `Capabilities/Resolution/Engine/EngineInterface.php`, `Capabilities/Injection/Methods/MethodInjector.php`, `Capabilities/Injection/Parameters/ResolveMethodParameters.php`, `docs/architecture.md`

- `id`: DEC-002
  `recorded_at`: 2026-04-07 04:05 CEST
  `decision_at`: 2026-04-07 04:05 CEST
  `updated_at`: 2026-04-07 04:05 CEST
  `status`: accepted
  `context`: The container component had drifted into `Core/`, `Features/`,
    `Guard/`, and `Observe/` buckets that hid ownership and duplicated runtime
    vocabulary.
  `decision`: Treat the component root as the system root and reorganize the
    code into explicit public surface, `Flows/`, `Capabilities/`,
    `Configuration/`, and `Foundation/` lanes; mirror the same story in docs
    and tests.
  `consequences`: Old root buckets are no longer canonical, flow names are the
    primary reading model, and future additions must choose flow/capability
    ownership before adding new folders.
  `links`: `Container.php`, `Flows/`, `Capabilities/`, `Configuration/`, `docs/architecture.md`, `../../tests/Foundation/Container/Capabilities/`

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
