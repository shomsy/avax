# Evidence Log

This is the single running list for feature work, bugs, TODOs, plans, decisions,
and follow-up items.

Update rules:

- keep newest items at the top
- use one line per item
- keep each item short and concrete
- link to the owning doc or code when useful
- if a status changes, update this file in the same work item
- add `actual` when the effort is known and worth recording
- add `owner` only when the closure needs accountability context
- use `TIMELINE.md` format for all new entries so duration and aging can be
  estimated

## Current Ledger

- `2026-04-08 00:50 CEST` | generated compiled runtime | closed `TODO-006` by shipping `Compilation/` plus `Runtime/`, generating a compiled container artifact for hot-path resolution, splitting shared singleton storage into `ServicePool`, adding alias/tagged/decorate/lazy/compile surface, and rerunning Docker lint plus the full smoke suite
- `2026-04-08 00:16 CEST` | production compile cache | closed `TODO-005` by shipping compiled blueprints plus resolve plans, versioned disk artifacts, public warm/flush/rebuild commands, compiled runtime execution paths, and smoke coverage for cache lifecycle plus disk hits
- `2026-04-07 23:45 CEST` | strict convergence review | hardened the scope boundary to raise `ContainerException`, kept the `openScope` / `closeScope` rename, and reran Docker lint plus the full smoke suite with no open findings
- `2026-04-07 23:43 CEST` | strict convergence review | finalized the scope API rename to `openScope` / `closeScope`, reran the full Docker lint sweep and smoke suite, and verified there are no blocking findings left in the container refactor
- `2026-04-07 22:24 CEST` | strict convergence review | refreshed `TODO-004` evidence after adding direct work-area smoke coverage for registrations, resolution, calls, scopes, configuration, providers, and PSR error boundaries, then reran Docker lint plus the full smoke suite with no open findings
- `2026-04-07 16:05 CEST` | strict convergence review | closed `TODO-004` by deleting dead `ResolvePlan` and duplicate alias surface, wiring `Clock` into `ResolutionTimeline`, removing extra repo noise artifacts, extending observability smoke coverage, and rerunning Docker lint plus the full smoke suite with no open findings
- `2026-04-07 15:44 CEST` | architecture convergence | closed `TODO-003` by shipping the final DX-first `DependencyInjection/` tree, fixing PSR error contracts plus scoped storage and callable invocation bugs, rewriting `docs/` to the new vocabulary, and adding Docker-backed smoke tests in `tests/`
- `2026-04-07 04:40 CEST` | defect fix | closed `BUG-002` and `BUG-001` by restoring root public contracts, introducing internal `RuntimeContainer`, decoupling providers to `ContainerInterface`, and removing the broken `basePath()` fallback from `ViewServiceProvider`
- `2026-04-07 04:05 CEST` | architecture refinement | accepted second-pass alignment with `KernelFacade`, `ContainerConfig`, `EngineInterface`, explicit injection `Methods/` + `Parameters/`, and reserved `DependencyInjection/Foundation/Time` + `DependencyInjection/Foundation/Ids`
