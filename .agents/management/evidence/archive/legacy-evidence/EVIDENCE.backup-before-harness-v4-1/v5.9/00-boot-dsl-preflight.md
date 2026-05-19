# EVIDENCE/v5.9/00-boot-dsl-preflight.md

## V5.9 Boot DSL — Preflight Evidence

**Date:** 2026-05-16
**Branch:** main
**Current Commit:** ec6a0c076 (repo-truth: reconcile ApiVersioning scope and stale duplicates before V5.9)
**Status:** PREFLIGHT

---

## 1. Worktree Status

| Check                             | Result                                                                    |
|-----------------------------------|---------------------------------------------------------------------------|
| Branch                            | `main`                                                                    |
| HEAD                              | `ec6a0c076`                                                               |
| Clean working tree for V5.9 scope | YES — dirty files are pre-existing and unrelated                          |
| Pre-existing dirty files          | `.agents/how-to/how-to.txt` (M), `avax.txt` (D), `merge-files` (M)        |
| Pre-existing untracked files      | `avax.part-{1-4}-of-4.txt` (local snapshots)                              |
| V5.9 evidence directory           | `EVIDENCE/v5.9/` — new, created this session                              |
| Worktrees                         | 3 found in `.qoder/worktrees/` — isolated agent contexts, not active HEAD |

---

## 2. V5.9 Readiness Source

Readiness to begin V5.9 is established by:

1. **CURRENT_TRUTH.md**: V5.8.x at `FULL_GREEN_REPO_WIDE_TRUTH_RECONCILED_AND_V5_9_READY` — 8413 tests GREEN, PHPStan 0
   errors, all gates PASS.
2. **EVIDENCE/EXECUTION.md**: Stage lock rules confirm V5.9 is the next allowed stage.
3. **.agents/management/TODO.md**: All items done or blocked; V5.9 listed as next.
4. **.agents/management/ACTIVE.md**: Board shows V5.9 was BLOCKED until cleanup GREEN — cleanup is now GREEN per
   CURRENT_TRUTH.md.
5. **Recent commits**: `ec6a0c076` explicitly says "before V5.9" — confirms V5.8 reconciliation is complete.

**Conclusion: V5.9 Boot DSL may begin.**

---

## 3. Existing Boot Entrypoints

| Entrypoint       | Path                                      | Current behavior                                                                                           |
|------------------|-------------------------------------------|------------------------------------------------------------------------------------------------------------|
| `Avax::create()` | `framework/System/PublicSurface/Avax.php` | Zero-config factory — manually constructs all services with `new`                                          |
| `Avax::boot()`   | `framework/System/PublicSurface/Avax.php` | Accepts `ApplicationBuilder` — delegates to `bootInternal()` which manually constructs kernels and runtime |
| `App`            | `framework/System/PublicSurface/App.php`  | V4 runnable app — receives ready Runtime, builds RunApplication lazily                                     |

---

## 4. Existing Builder Classes

| Builder              | Path                                                                              | Responsibility                                                                           | Problem                                                                                                                      |
|----------------------|-----------------------------------------------------------------------------------|------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------|
| `ApplicationBuilder` | `framework/System/Configuration/BuildApplication/Builders/ApplicationBuilder.php` | Fluent builder collecting 8+ params, component providers, console commands, HTTP handler | No container integration. No provider scanning. No compile/verify/freeze. Has `new` in constructor for command registration. |

---

## 5. Existing Provider Boot Path

| Provider                       | Path                                                                                 | Boot behavior                                                                                                        |
|--------------------------------|--------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------|
| `FrameworkServiceProvider`     | `framework/System/Configuration/FrameworkServiceProvider.php`                        | `register()` only — registers runtime safety, config repository. `boot()` is empty.                                  |
| `ContainerServiceProvider`     | `components/Application/Container/System/Configuration/ContainerServiceProvider.php` | `register()` registers container as self, ResolveCallable, health check. `boot()` calls `Container::setContainer()`. |
| 55+ Component ServiceProviders | `components/*/System/Configuration/*ServiceProvider.php`                             | Each registers component bindings. No central discovery. No boot ordering.                                           |

**Problem**: Nothing orchestrates provider lifecycle. No scanning. No compile/verify/freeze. No ordered boot.

---

## 6. Existing Root Container Ownership

| Item                    | Current owner                                                                  | Status                     |
|-------------------------|--------------------------------------------------------------------------------|----------------------------|
| Container creation      | Implicit — created by whoever calls `new Container()`                          | No central ownership       |
| Container lifecycle     | None — no compile/verify/freeze phases exist                                   | ABSENT                     |
| Container facade wiring | `ContainerServiceProvider::boot()`                                             | Works but not orchestrated |
| ContainerInterface      | `components/Application/Container/System/PublicSurface/ContainerInterface.php` | Exists, stable API         |

---

## 7. Current Pain Points

1. **DI law violation in Avax facade**: `Avax::create()` and `Avax::bootInternal()` manually `new` services — a
   PublicSurface class should not construct dependencies.
2. **No container lifecycle**: Container exists but nothing owns compile → verify → freeze → boot → run.
3. **No provider scanning**: 55+ ServiceProviders exist but nothing discovers and boots them together.
4. **Manual construction everywhere**: `ApplicationBuilder` requires 8 constructor params, all `new`'d at call site.
5. **Runtime-adjacent code assembles**: `Avax` (a facade) creates services — violates DI governance.
6. **Container compile/verify/freeze absent**: Dependencies fail at runtime, not at boot.
7. **Golden path examples teach manual assembly**: Examples show `new ApplicationBuilder(...)` with many params.

---

## 8. Allowed Scope for This Slice

The first vertical slice MAY:

1. Create `framework/System/Configuration/BootDsl/` with:
    - `BootDslBuilder.php` — public fluent DSL builder
    - `BootDslEngine.php` — internal engine: container lifecycle
    - `ProviderRegistry.php` — provider collection + ordering
    - `BootPhase.php` — enum: Create, Register, Compile, Verify, Freeze, Boot, Run

2. Create `framework/System/Flows/BootApplication/BootWithDsl.php` — flow: execute Boot DSL lifecycle.

3. Update `framework/System/PublicSurface/Avax.php` — delegate `Avax::boot()` to Boot DSL builder (keep backward compat
   with `Avax::boot(ApplicationBuilder)`).

4. Create tests proving the full path works.

5. Create evidence documents.

---

## 9. Forbidden Scope for This Slice

The first vertical slice MUST NOT:

1. Modify Runtime execution path (App, Runtime, HttpKernel, ConsoleKernel).
2. Modify individual ServiceProviders.
3. Modify ContainerInterface API.
4. Modify ComponentRegistry behavior.
5. Implement auto-scanning of component directories.
6. Implement config file loading (YAML/PHP parsing).
7. Implement container compile optimization (warm compilation, caching).
8. Implement provider priority/sorting beyond declaration order.
9. Implement child container / request scope containers.
10. Implement runtime adapter integration (ReactPHP, Swoole, RoadRunner, FrankenPHP).
11. Split AuthBuilder.
12. Refactor Response layer.
13. Perform any Fix-This cleanup.
14. Create fake DSL shells without real container lifecycle.
15. Bypass stage lock.

---

## 10. Validation Plan

Canonical validation set to run after implementation:

```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
php tooling/audit_broken_refs.php
php avax runtime:doctor
```

Plus governance validation where available:

```bash
php tooling/governance/check-governance-index-current.php
php tooling/governance/check-stage-lock.php
php tooling/refactor/check-component-canonical-shape.php
php tooling/refactor/check-advanced-pattern-folder-violations.php
php tooling/security/check-security-governance.php
php tooling/performance/check-performance-governance.php
```

Focused validation acceptable if scope is narrow (only Boot DSL files changed).

---

## 11. Evidence Plan

Evidence files to create:

| File                                               | Purpose                                  | Status       |
|----------------------------------------------------|------------------------------------------|--------------|
| `EVIDENCE/v5.9/00-boot-dsl-preflight.md`           | This preflight document                  | CREATING NOW |
| `EVIDENCE/v5.9/01-worktree-baseline.md`            | Worktree classification                  | DONE         |
| `EVIDENCE/v5.9/02-existing-boot-flow-inventory.md` | Inventory of existing boot units         | DONE         |
| `EVIDENCE/v5.9/03-boot-dsl-design-lock.md`         | Design decisions lock                    | DONE         |
| `EVIDENCE/v5.9/04-implementation-evidence.md`      | Implementation proof + validation output | PENDING      |
| `EVIDENCE/v5.9/05-recursive-review.md`             | Governance review findings               | PENDING      |
| `EVIDENCE/v5.9/06-final-evidence.md`               | Final proof before commit                | PENDING      |

---

## 12. Commit Policy

- Do NOT stage pre-existing dirty files: `.agents/how-to/how-to.txt`, `avax.txt`, `merge-files`, `avax.part-*.txt`.
- Do NOT stage `.qoder/worktrees/**`, `.phpunit.cache/**`, `vendor/**`.
- V5.9 commit message format: `v5.9: brief description of what changed`.
- Only commit after: all validation GREEN, recursive review GREEN, evidence documents written.
- Do NOT mix unrelated dirty files into V5.9 commits.
- Do NOT commit until Step 7 (evidence and truth update) is complete.

---

## 13. Preflight Status

**PREFLIGHT COMPLETE — Ready for Step 5: First vertical slice implementation.**

All preflight questions answered:

- Active mode: Standard Mode (Harness-Full rules applied per user request)
- Active stage: V5.9 Boot DSL
- Forbidden scope: Listed in section 9
- Relevant governance documents: Read (AGENTS.md, how-to-design-components, how-to-architecture,
  how-to-dependency-injection, how-to-coding-standards, how-to-system-security, how-to-system-performance,
  how-to-code-review, how-to-unit-test, how-to-production-readiness)
- Relevant source files: Read (all boot entrypoints, builders, providers)
- Expected validation commands: Listed in section 10
- Expected output artifact: Boot DSL + App returned + 1 test + evidence
- Next allowed action: Implement first vertical slice (Step 5)
