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

- `2026-04-08 03:28 CEST` | strict coding-standards pass | aligned production code to `how-to-coding-standards.md` and `amin.md` by removing implicit provider-constructor coupling in `BootProviders`, replacing hidden decorator and deferred-provider fallback behavior with explicit contracts, tightening compile safety so non-deterministic registration arguments fall back to dynamic resolution, adding concise public intent and `@throws` contracts across core runtime/compile/foundation owners, adding a compiled-runtime regression test for object-argument fallback, and rerunning Docker PHP lint, the full smoke suite, and the benchmark guard
- `2026-04-08 03:01 CEST` | final strict-review closure | closed `TODO-014` by fixing deferred-provider compile warmup for explicit and dependency-driven service graphs, moving benchmark comparison artifacts to temp space, stabilizing the benchmark guard with repeated median runs, aligning benchmark fixtures and docs to `DeferredProviderInterface`, deleting foreign tool-specific root files, and rerunning Docker lint, the full smoke suite, the benchmark guard, the benchmark harness, the comparison runner, and `git diff --check`
- `2026-04-08 02:56 CEST` | comparison harness and explicit deferred-provider contract | closed `TODO-013` by adding `DeferredProviderInterface`, moving lazy provider ownership off duck-typing, teaching the benchmark runner to emit JSON artifacts, shipping `tests/benchmarks/compare.php` plus `tests/run-benchmark-comparison.sh`, adding smoke coverage for artifact comparison, and rerunning Docker lint, the full smoke suite, the benchmark harness, and the benchmark guard
- `2026-04-08 02:32 CEST` | diagnostics and deferred-provider convergence | closed `TODO-012` by wiring explicit diagnostics modes into assembly and runtime reports, adding deferred provider loading on first owned service resolve, recording alias/decorate/cache/compiled diagnostics on service descriptions, extending incremental compile metadata with invalidation reasons and reuse stats, widening benchmark scenarios for worker/request/dev-prod paths, and rerunning Docker lint, the full smoke suite, the benchmark harness, and the benchmark guard
- `2026-04-08 02:05 CEST` | typed compile/runtime contracts | closed `TODO-011` by adding `ArtifactMetadata`, `CompileReport`, `RuntimeReport`, `ProviderBootPlan`, and `LifetimePlan`, wiring `compileReport()` plus `runtimeReport()` through the public facade, hardening runtime report JSON to summarize live scope instances safely, and rerunning Docker PHP lint, the full smoke suite, the benchmark harness, and the benchmark regression gate
- `2026-04-08 01:47 CEST` | compiler hardening and diagnostics closure | removed `eval` from compiled runtime materialization, added compiled metadata plus checksum/config/environment signatures, quarantined corrupt artifacts, added public status helpers and `debugService()`, extended benchmark coverage plus regression guard, and reran Docker lint, smoke tests, benchmark harness, and benchmark guard
- `2026-04-08 01:23 CEST` | TODO sweep complete | closed `TODO-007` through `TODO-010` by shipping lifecycle `flush()`/`reset()`, deferred services plus provider composition, context/diagnostics helpers, and benchmark scripts; reran Docker lint, the full smoke suite, and the benchmark harness
- `2026-04-08 00:50 CEST` | generated compiled runtime | closed `TODO-006` by shipping `Compilation/` plus `Runtime/`, generating a compiled container artifact for hot-path resolution, splitting shared singleton storage into `ServicePool`, adding alias/tagged/decorate/lazy/compile surface, and rerunning Docker lint plus the full smoke suite
- `2026-04-08 00:16 CEST` | production compile cache | closed `TODO-005` by shipping compiled blueprints plus resolve plans, versioned disk artifacts, public warm/flush/rebuild commands, compiled runtime execution paths, and smoke coverage for cache lifecycle plus disk hits
- `2026-04-07 23:45 CEST` | strict convergence review | hardened the scope boundary to raise `ContainerException`, kept the `openScope` / `closeScope` rename, and reran Docker lint plus the full smoke suite with no open findings
- `2026-04-07 23:43 CEST` | strict convergence review | finalized the scope API rename to `openScope` / `closeScope`, reran the full Docker lint sweep and smoke suite, and verified there are no blocking findings left in the container refactor
- `2026-04-07 22:24 CEST` | strict convergence review | refreshed `TODO-004` evidence after adding direct work-area smoke coverage for registrations, resolution, calls, scopes, configuration, providers, and PSR error boundaries, then reran Docker lint plus the full smoke suite with no open findings
- `2026-04-07 16:05 CEST` | strict convergence review | closed `TODO-004` by deleting dead `ResolvePlan` and duplicate alias surface, wiring `Clock` into `ResolutionTimeline`, removing extra repo noise artifacts, extending observability smoke coverage, and rerunning Docker lint plus the full smoke suite with no open findings
- `2026-04-07 15:44 CEST` | architecture convergence | closed `TODO-003` by shipping the final DX-first `DependencyInjection/` tree, fixing PSR error contracts plus scoped storage and callable invocation bugs, rewriting `docs/` to the new vocabulary, and adding Docker-backed smoke tests in `tests/`
- `2026-04-07 04:40 CEST` | defect fix | closed `BUG-002` and `BUG-001` by restoring root public contracts, introducing internal `RuntimeContainer`, decoupling providers to `ContainerInterface`, and removing the broken `basePath()` fallback from `ViewServiceProvider`
- `2026-04-07 04:05 CEST` | architecture refinement | accepted second-pass alignment with `KernelFacade`, `ContainerConfig`, `EngineInterface`, explicit injection `Methods/` + `Parameters/`, and reserved `DependencyInjection/Foundation/Time` + `DependencyInjection/Foundation/Ids`
