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

- `id`: TODO-014
  `created_at`: 2026-04-08 03:01 CEST
  `updated_at`: 2026-04-08 03:01 CEST
  `status`: done
  `estimate`: medium
  `actual`: medium
  `outcome`: Close the final strict-review production gaps by fixing deferred
    provider compilation on explicit and dependency-driven warmup paths,
    moving benchmark comparison artifacts out of the repository tree,
    stabilizing the benchmark guard with repeated median runs, aligning the
    benchmark harness with `DeferredProviderInterface`, and removing foreign
    tool-specific root files.
  `acceptance`: Explicit compile and warmup can pull in deferred-provider-owned
    dependencies without silently skipping them; benchmark comparison works
    from temporary host artifacts without leaving generated files in the repo;
    benchmark guard is reproducible instead of single-shot noisy; deferred
    provider docs and benchmark fixtures prefer the explicit contract; Docker
    PHP lint is green; `tests/run-smoke-tests.sh` is green;
    `tests/check-benchmarks.sh` is green; `tests/run-benchmarks.sh` is green;
    `git diff --check` is clean.
  `links`: `DependencyInjection/Dependencies/Resolution/ServiceResolver.php`, `tests/DependencyInjection/Flows/BootProviders/BootProvidersSmokeTest.php`, `tests/run-benchmark-comparison.sh`, `tests/benchmarks/run.php`, `docs/Container.md`, `docs/architecture.md`, `docs/troubleshooting.md`

- `id`: TODO-013
  `created_at`: 2026-04-08 02:45 CEST
  `updated_at`: 2026-04-08 02:56 CEST
  `status`: done
  `estimate`: medium
  `actual`: medium
  `outcome`: Close the remaining benchmark-governance and provider-contract gap
    by making deferred providers explicit via a dedicated contract, adding
    benchmark artifact output, and shipping a comparison runner that can
    compare this container against peer benchmark artifacts without dragging
    peer dependencies into the component itself.
  `acceptance`: Deferred providers can implement an explicit contract;
    benchmark runs can emit JSON artifacts to disk; comparison runner can
    compare at least two benchmark artifacts and report normalized ratios;
    docs describe the comparison flow; Docker PHP lint is green;
    `tests/run-smoke-tests.sh` is green; `tests/check-benchmarks.sh` is green;
    `tests/run-benchmarks.sh` is green.
  `links`: `DependencyInjection/Dependencies/Providers/DeferredProviderInterface.php`, `DependencyInjection/Flows/BootProviders.php`, `tests/benchmarks/run.php`, `tests/benchmarks/compare.php`, `tests/run-benchmark-comparison.sh`, `tests/benchmarks/BenchmarkComparisonSmokeTest.php`

- `id`: TODO-012
  `created_at`: 2026-04-08 02:20 CEST
  `updated_at`: 2026-04-08 02:32 CEST
  `status`: done
  `estimate`: medium
  `actual`: medium
  `outcome`: Close the remaining compile/runtime discipline gaps by wiring
    explicit diagnostics modes, automatic incremental compile reuse metadata,
    deferred provider loading, richer service/runtime diagnostics, and a wider
    benchmark matrix for worker, request-lifecycle, and mode-specific paths.
  `acceptance`: `CreateContainerConfig` exposes diagnostics modes; runtime
    timeline honors low-overhead versus detailed mode; deferred providers boot
    on first owned service resolve; compile reports expose invalidation and
    reuse details; benchmark scenarios cover worker, request lifecycle,
    deferred provider, and dev/prod compiled paths; Docker PHP lint is green;
    `tests/run-smoke-tests.sh` is green; `tests/check-benchmarks.sh` is green;
    `tests/run-benchmarks.sh` is green.
  `links`: `Configuration/CreateContainerConfig.php`, `DependencyInjection/Flows/CreateContainer.php`, `DependencyInjection/Flows/BootProviders.php`, `DependencyInjection/Dependencies/Resolution/ServiceResolver.php`, `DependencyInjection/Dependencies/Bindings/ServiceRegistry.php`, `Observability/ResolutionTimeline.php`, `Observability/RuntimeReport.php`, `tests/DependencyInjection/Flows/BootProviders/BootProvidersSmokeTest.php`, `tests/DependencyInjection/Flows/ResolveService/ContextAndDiagnosticsSmokeTest.php`, `tests/benchmarks/run.php`

- `id`: TODO-011
  `created_at`: 2026-04-08 01:50 CEST
  `updated_at`: 2026-04-08 02:05 CEST
  `status`: done
  `estimate`: medium
  `actual`: medium
  `outcome`: Replace implicit compile/runtime array schemas with typed report and
    plan owners so compiled metadata, runtime state, provider ordering, and
    lifetime behavior are explicit, deterministic, and machine-readable.
  `acceptance`: `ArtifactMetadata`, `CompileReport`, `RuntimeReport`,
    `ProviderBootPlan`, and `LifetimePlan` exist and are wired into the public
    facade plus runtime owner; `compileReport()` and `runtimeReport()` expose
    stable machine-readable output; provider boot metrics remain deterministic;
    Docker PHP lint is green; `tests/run-smoke-tests.sh` is green;
    `tests/check-benchmarks.sh` is green.
  `links`: `Compilation/ArtifactMetadata.php`, `Compilation/CompileReport.php`, `Observability/RuntimeReport.php`, `DependencyInjection/Dependencies/Providers/ProviderBootPlan.php`, `DependencyInjection/Dependencies/Resolution/LifetimePlan.php`, `Container.php`, `ContainerInterface.php`, `DependencyInjection/Dependencies/Resolution/ServiceResolver.php`, `DependencyInjection/Flows/BootProviders.php`

- `id`: TODO-007
  `created_at`: 2026-04-08 00:50 CEST
  `updated_at`: 2026-04-08 01:23 CEST
  `status`: done
  `estimate`: medium
  `actual`: medium
  `outcome`: Add explicit lifecycle control with `flush()` / `reset()` so the
    container can clear bindings, pools, scopes, and compiled artifacts safely
    for tests, worker processes, and repeated bootstrap cycles.
  `acceptance`: `Container` exposes `flush()` and `reset()`; flush clears
    bindings, scopes, runtime pools, and compiled artifacts; reset restores a
    clean runtime; repeated bootstrap/resolve cycles remain deterministic;
    docs explain the lifecycle behavior; Docker PHP lint is green;
    `tests/run-smoke-tests.sh` is green.
  `links`: `Container.php`, `ContainerInterface.php`, `DependencyInjection/Dependencies/Bindings/ServiceRegistry.php`, `DependencyInjection/Scopes/ScopeStore.php`, `Runtime/ServicePool.php`, `Compilation/CompileContainer.php`, `DependencyInjection/Dependencies/Resolution/ServiceResolver.php`

- `id`: TODO-008
  `created_at`: 2026-04-08 00:50 CEST
  `updated_at`: 2026-04-08 01:23 CEST
  `status`: done
  `estimate`: large
  `actual`: large
  `outcome`: Add deferred service loading and provider composition so the
    container can defer expensive services, compose provider lifecycles, and
    support env-backed binding and configuration hooks without reintroducing
    shadow architecture.
  `acceptance`: Deferred registration/loading works end-to-end; provider
    composition or extension is explicit and deterministic; env-backed binding
    or configuration hooks exist; lazy/deferred behavior is reflected in the
    compiled runtime path; docs and smoke coverage describe the surface; Docker
    PHP lint is green; `tests/run-smoke-tests.sh` is green.
  `links`: `Container.php`, `ContainerInterface.php`, `Configuration/CreateContainerConfig.php`, `DependencyInjection/Dependencies/Bindings/ServiceRegistry.php`, `DependencyInjection/Dependencies/Providers/ServiceProviderInterface.php`, `DependencyInjection/Flows/BootProviders.php`, `Runtime/LazyProxy.php`

- `id`: TODO-009
  `created_at`: 2026-04-08 00:50 CEST
  `updated_at`: 2026-04-08 01:23 CEST
  `status`: done
  `estimate`: large
  `actual`: large
  `outcome`: Add the context and diagnostics surface so the container can
    explain itself with `validate()`, `describeService()`, and debug helpers
    for services, plans, tags, aliases, and scope state while keeping the
    public API DX-first.
  `acceptance`: The public facade exposes an explicit context entrypoint or
    equivalent, a validation path, and debug helpers for service, plan, tags,
    aliases, and scope state; diagnostics explain compiled versus dynamic
    resolution paths; docs describe the new surface; Docker PHP lint is green;
    `tests/run-smoke-tests.sh` is green.
  `links`: `Container.php`, `ContainerInterface.php`, `DependencyInjection/Dependencies/Resolution/ServiceResolver.php`, `DependencyInjection/Dependencies/Bindings/ServiceRegistry.php`, `docs/Container.md`, `docs/concepts/resolution-flow.md`

- `id`: TODO-010
  `created_at`: 2026-04-08 00:50 CEST
  `updated_at`: 2026-04-08 01:23 CEST
  `status`: done
  `estimate`: large
  `actual`: large
  `outcome`: Build a benchmark and performance gate suite that can measure
    cold, warm, and hot container paths, deep and wide object graphs,
    compiled-runtime memory cost, and worker-safe runtime behavior so the
    "fastest container" claim has a concrete regression harness.
  `acceptance`: Benchmark scripts exist for cold boot, warm boot, cached get,
    uncached resolve, deep graph, wide graph, scoped service, lazy service,
    and compile-time paths; the suite records memory cost and runtime
    behavior; docs explain how to run it; Docker PHP lint remains green;
    `tests/run-smoke-tests.sh` remains green after the related changes.
  `links`: `tests/`, `tests/run-smoke-tests.sh`, `docs/architecture.md`, `docs/concepts/resolution-flow.md`

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
