# TODO

Canonical active implementation queue.

## Rules

- keep newest items first
- keep each item outcome-oriented
- include acceptance criteria
- include owner only when needed
- use timestamp and estimate fields from `TIMELINE.md`
- keep `ACTIVE.md` in sync for non-closed items

## Entry Format

- `id`:
- `created_at`:
- `updated_at`:
- `status`: todo | in_progress | blocked | done
- `estimate`:
- `actual`:
- `outcome`:
- `acceptance`:
- `links`:

## Current Items

- `id`: TODO-006
  `created_at`: 2026-04-08 00:30 CEST
  `updated_at`: 2026-04-08 00:50 CEST
  `status`: done
  `estimate`: large
  `actual`: large
  `outcome`: Ship the generated compiled runtime layer by adding
    `Compilation/` plus `Runtime/` owners, compiling direct service methods
    into a container artifact, routing hot-path resolution through the
    compiled runtime, separating shared singleton storage into `ServicePool`,
    and completing the missing DX surface for `alias()`, `tagged()`,
    `decorate()`, `lazy()`, and `compileContainer()`.
  `acceptance`: Generated compiled runtime artifacts are written and loaded
    from disk; compiled hot-path metrics are emitted; aliases, tags,
    decoration, lazy proxies, and explicit compile commands are exposed on the
    public facade; Docker PHP lint is green; `tests/run-smoke-tests.sh` is
    green; no open strict-review findings remain in scope.
  `links`: `Compilation/CompileContainer.php`, `Compilation/MethodEmitter.php`, `Compilation/ServiceCompiler.php`, `Runtime/HotPathInliner.php`, `Runtime/LazyProxy.php`, `Runtime/ServicePool.php`, `Container.php`, `ContainerInterface.php`, `DependencyInjection/Dependencies/Resolution/ServiceResolver.php`, `tests/DependencyInjection/Flows/CreateContainer/CompiledContainerSmokeTest.php`, `tests/DependencyInjection/Flows/RegisterServices/RegisterServicesSmokeTest.php`, `tests/Runtime/ServicePoolSmokeTest.php`

- `id`: TODO-005
  `created_at`: 2026-04-07 23:50 CEST
  `updated_at`: 2026-04-08 00:16 CEST
  `status`: done
  `estimate`: large
  `actual`: large
  `outcome`: Ship a production-grade compile/cache layer by compiling service
    blueprints plus resolve plans into versioned disk artifacts, moving the
    runtime build and injection path onto compiled metadata, exposing
    warm/flush/rebuild commands, and extending telemetry plus smoke coverage
    for compiled cache hits and lifecycle control.
  `acceptance`: The runtime resolves constructor and injection metadata from
    compiled plans instead of reflection objects; compiled artifacts are
    written under `cacheDir` with `cacheVersion`; public API exposes
    `warmCompiled()`, `flushCompiled()`, and `rebuildCompiled()`; Docker PHP
    lint is green; `tests/run-smoke-tests.sh` is green; no open strict-review
    findings remain in scope.
  `links`: `Configuration/CreateContainerConfig.php`, `Container.php`, `ContainerInterface.php`, `DependencyInjection/Dependencies/Blueprints/BlueprintCache.php`, `DependencyInjection/Dependencies/Blueprints/CreateServiceBlueprint.php`, `DependencyInjection/Dependencies/Resolution/ResolvePlan.php`, `DependencyInjection/Dependencies/Resolution/BuildService.php`, `DependencyInjection/Dependencies/Resolution/ServiceResolver.php`, `tests/DependencyInjection/Flows/CreateContainer/CompiledCacheSmokeTest.php`

- `id`: TODO-004
  `created_at`: 2026-04-07 15:20 CEST
  `updated_at`: 2026-04-07 23:45 CEST
  `status`: done
  `estimate`: medium
  `actual`: medium
  `outcome`: Collapse the last fake abstractions and repo noise after the main
    convergence pass by deleting dead `ResolvePlan` and duplicate aliases,
    tightening scope naming to `openScope` / `closeScope`, wiring `Clock` into
    timeline recording, hardening scope errors to the explicit
    `ContainerException` boundary, trimming redundant DTO/config/store surface,
    extending direct work-area smoke coverage, and rerunning a strict review
    plus validation loop.
  `acceptance`: No dead owner types remain in the shipped runtime path; the
    timeline uses the foundation clock; duplicate alias methods are removed;
    scope APIs use `openScope` / `closeScope`; direct work-area smoke tests
    cover registrations, resolution, calls, scopes, configuration, provider
    contract, and error boundaries; Docker PHP lint stays green;
    `tests/run-smoke-tests.sh` stays green; no open strict-review findings
    remain in scope.
  `links`: `DependencyInjection/Flows/CreateContainer.php`, `DependencyInjection/Dependencies/Resolution/ServiceResolver.php`, `DependencyInjection/Observability/ResolutionTimeline.php`, `tests/DependencyInjection/Flows/CreateContainer/CreateContainerSmokeTest.php`, `tests/DependencyInjection/Dependencies/Bindings/ServiceRegistrySmokeTest.php`, `tests/DependencyInjection/Dependencies/Blueprints/CreateServiceBlueprintSmokeTest.php`, `tests/DependencyInjection/Injection/Invocation/ResolveCallArgumentsSmokeTest.php`, `tests/DependencyInjection/Scopes/ScopeStoreSmokeTest.php`

- `id`: TODO-003
  `created_at`: 2026-04-07 13:30 CEST
  `updated_at`: 2026-04-07 15:44 CEST
  `status`: done
  `estimate`: large
  `actual`: large
  `outcome`: Converge `Foundation/Container` to the final DX-first
    `DependencyInjection/` architecture, remove the legacy
    `Flow/` + `Capability/` tree, harden runtime behavior, rewrite shipped
    docs to the new vocabulary, and add Docker-backed smoke coverage for the
    final flow entries and work areas.
  `acceptance`: The public facade stays thin; only the final canonical tree and
    namespace remain; no legacy architectural vocabulary survives in `docs/`;
    Docker PHP lint is green; `tests/run-smoke-tests.sh` passes across the
    shipped flow entries and internal work areas.
  `links`: `Container.php`, `ContainerInterface.php`, `DependencyInjection/`, `docs/architecture.md`, `docs/Container.md`, `tests/run-smoke-tests.sh`

- `id`: TODO-002
  `created_at`: 2026-04-07 02:21 CEST
  `updated_at`: 2026-04-07 04:05 CEST
  `status`: done
  `estimate`: large
  `actual`: large
  `outcome`: Repackage `Foundation/Container` into explicit
    `DependencyInjection/Configuration/`, `DependencyInjection/Flow/`,
    `DependencyInjection/Capability/`, and
    `DependencyInjection/Foundation/` lanes, removing `Core/` and
    `Features/` as canonical roots.
  `acceptance`: `Container.php` stays the public facade; runtime resolution
    lives under `DependencyInjection/Capability/Resolution/Kernel`;
    assembly lives under `DependencyInjection/Configuration/`; system flows are
    named explicitly; old generic buckets are no longer the canonical
    architecture roots.
  `links`: `Container.php`, `DependencyInjection/Flow/`, `DependencyInjection/Capability/`, `DependencyInjection/Configuration/`, `docs/architecture.md`, `docs/concepts/injection-and-instantiation.md`

- `id`: TODO-001
  `created_at`: 2026-04-07 02:11 CEST
  `updated_at`: 2026-04-07 12:47 CEST
  `status`: done
  `estimate`: small
  `actual`: small
  `outcome`: Mark the legacy root `how-to-*.md` documentation as compatibility
    references and point contributors to the governed standards in
    `.agents/.rules` and `docs/`.
  `acceptance`: The root compatibility docs are explicitly legacy in place,
    and authoritative guidance is under `.agents/.rules` plus `docs/`.
  `links`: `AGENTS.md`, `how-to-coding-standards.md`, `how-to-code-review.md`, `how-to-document.md`, `docs/`
