# V5.9 Codex Deep Execution Preflight

Date: 2026-05-16
Mode: Harness-Full (`uradi po pravilima .agents`)
Branch: `main`
Current commit: `4698be5f1383974bc82531c8a3af3cf8a68df41b`

## 1. Worktree Status

Commands run before this evidence file was created:

| Command                         | Result                                                                                                                                                                                                                                                                                                           |
|---------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `git status --short`            | `M .agents/how-to/how-to.txt`, `M .codex`, `D avax.txt`                                                                                                                                                                                                                                                          |
| `git diff --stat`               | 3 files, 92 insertions, 23 deletions                                                                                                                                                                                                                                                                             |
| `git diff --name-only`          | `.agents/how-to/how-to.txt`, `.codex`, `avax.txt`                                                                                                                                                                                                                                                                |
| `git diff --cached --stat`      | empty                                                                                                                                                                                                                                                                                                            |
| `git diff --cached --name-only` | empty                                                                                                                                                                                                                                                                                                            |
| `git branch --show-current`     | `main`                                                                                                                                                                                                                                                                                                           |
| `git log -30 --oneline`         | latest relevant commits include `4698be5f1 fix-this fixes`, `32864e068 V5.9 Boot DSL correction: fix 6 critical findings from independent review`, `ec6a0c076 repo-truth: reconcile ApiVersioning scope and stale duplicates before V5.9`, `370e20252 hardening: reconcile phase b proof with code and evidence` |

Pre-existing dirty files are not part of the V5.9 Codex scope and must not be staged by this execution unless a later
truth correction explicitly requires it.

## 2. Current V5.9 Status From Truth Files

| Source                         | Status Found                                                                                                                                                                                           | Decision                                                                                                                                         |
|--------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------|
| `CURRENT_TRUTH.md`             | V5.8.x repo-wide truth reconciliation says V5.9 is ready. It does not contain a current V5.9 first-slice completion section.                                                                           | Truth drift exists; V5.9 evidence is newer for first-slice status.                                                                               |
| `EVIDENCE/EXECUTION.md`        | Next allowed stage is V5.9 Boot DSL. Repo-wide truth reconciliation is complete and V5.9 ready.                                                                                                        | V5.9 execution is allowed.                                                                                                                       |
| `.agents/management/TODO.md`   | Latest queue entries stop at V5.8.9 and older cleanup items.                                                                                                                                           | Backlog is stale for V5.9.                                                                                                                       |
| `.agents/management/ACTIVE.md` | Still says V5.9 Boot DSL is blocked until cleanup GREEN.                                                                                                                                               | Stale and contradicted by newer `CURRENT_TRUTH.md`, `EVIDENCE/EXECUTION.md`, and `EVIDENCE/fix-this/60-repo-wide-truth-final-reconciliation.md`. |
| `.agents/management/BUGS.md`   | No active items.                                                                                                                                                                                       | No active bug queue blocker.                                                                                                                     |
| `EVIDENCE/v5.9/*`              | V5.9 Boot DSL first slice exists and correction pass fixed public boundary, provider lifecycle, freeze, test expansion, and route API deferral. Root container ownership remains accepted YELLOW debt. | Phase 1 starts from GREEN_WITH_ACCEPTED_YELLOW_DEBT, not pure FULL_GREEN.                                                                        |

## 3. Assumptions Verified Or Disproven

| Assumption                                               | Evidence                                                                                                                                                                      | Result                                                   |
|----------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------|
| V5.8.x Fix-This program is closed.                       | `EVIDENCE/fix-this/60-repo-wide-truth-final-reconciliation.md`, `EVIDENCE/EXECUTION.md`                                                                                       | Verified.                                                |
| Phase A/B runtime composition and facade debt is closed. | `EVIDENCE/fix-this/57-60`, latest commits `370e20252`, `083d4ff54`, `284b74cca`                                                                                               | Verified by evidence, pending fresh baseline validation. |
| Repo-wide truth reconciliation is closed.                | `EVIDENCE/fix-this/60-repo-wide-truth-final-reconciliation.md`                                                                                                                | Verified by evidence, but management board is stale.     |
| Corrected V5.9 Boot DSL first slice exists.              | `framework/System/PublicSurface/BootDsl.php`, `framework/System/Configuration/BootDsl/*`, `tests/Unit/Framework/Configuration/BootDsl/BootDslTest.php`, `EVIDENCE/v5.9/08-16` | Verified.                                                |
| Corrected first slice is pure green.                     | `EVIDENCE/v5.9/11-root-container-ownership-truth.md`, `BootDslEngine::createRuntimeAndApp()` manual graph assembly, `BootDsl::create()` manual service assembly               | Disproven. It is GREEN_WITH_ACCEPTED_YELLOW_DEBT.        |
| Route DSL is implemented.                                | `EVIDENCE/v5.9/12-route-dsl-behavior-correction.md`, `BootDsl` API lacks `withRoutes()`/`withRouteFiles()`                                                                    | Disproven intentionally. Route DSL is deferred.          |
| Container freeze is real.                                | `FrozenContainer::freeze()`, mutation tests in `BootDslTest`                                                                                                                  | Verified pending fresh validation.                       |
| Provider lifecycle reuses the same instance.             | `BootDslEngine::$providerInstances`, lifecycle tests in `BootDslTest`                                                                                                         | Verified pending fresh validation.                       |

## 4. Governance Documents Read

- `AGENTS.md`
- `.agents/GOVERNANCE_INDEX.md`
- `CURRENT_TRUTH.md`
- `EVIDENCE/EXECUTION.md`
- `.agents/management/TODO.md`
- `.agents/management/ACTIVE.md`
- `.agents/management/BUGS.md`
- `fix-this.md`
- `.agents/how-to/how-to-*.md` inventory and rule scan
- `.agents/how-to/how-to-runtime-composition.md`
- `.agents/how-to/how-to-dependency-injection.md`
- `.agents/how-to/how-to-code-review.md`
- `.agents/how-to/how-to-git.md`
- `.agents/how-to/how-to-document.md`
- `.agents/how-to/how-to-unit-test.md`
- `.agents/how-to/how-to-architecture.md`
- `.agents/how-to/how-to-design-components.md`
- `.agents/how-to/how-to-system-security.md`
- `.agents/how-to/how-to-system-performance.md`
- `.agents/how-to/how-to-production-readiness.md`
- Project skills: review, validation, testing, security, performance, refactor
- `EVIDENCE/v5.9/00-16`
- `EVIDENCE/fix-this/52-60`
- `composer.json`

## 5. Relevant Source And Test Files Read

- `framework/System/PublicSurface/Avax.php`
- `framework/System/PublicSurface/BootDsl.php`
- `framework/System/Configuration/BootDsl/BootDslBuilder.php`
- `framework/System/Configuration/BootDsl/BootDslEngine.php`
- `framework/System/Configuration/BootDsl/ProviderRegistry.php`
- `framework/System/Configuration/BootDsl/BootPhase.php`
- `framework/System/Flows/BootApplication/BootWithDsl.php`
- `framework/System/Configuration/BuildApplication/Builders/ApplicationBuilder.php`
- `components/Application/Container/System/Foundation/FrozenContainer.php`
- `components/Application/Container/System/Foundation/SimpleContainer.php`
- `components/Application/Container/System/PublicSurface/ContainerInterface.php`
- `components/Application/Container/System/Capabilities/ServiceProvider/ServiceProvider.php`
- `framework/System/Configuration/FrameworkServiceProvider.php`
- `components/Application/Container/System/Configuration/ContainerServiceProvider.php`
- `tests/Unit/Framework/Configuration/BootDsl/BootDslTest.php`

## 6. Active Stage

Active execution stage for this pass: V5.9 Codex Deep Execution Program.

Current phase: Phase 0 preflight.

Next phase after preflight: baseline validation.

## 7. Forbidden Scope

- No production code edits before this preflight exists.
- No uncontrolled mega-rewrite.
- No direct feature work on unrelated V5/V6 areas.
- No stage claims without current validation evidence.
- No weakening gates, tests, PHPStan config, or governance rules.
- No staging or committing pre-existing dirty files unless later explicitly required and reviewed.
- No route DSL claim unless implemented and tested.
- No FULL_GREEN root-container claim while manual graph assembly remains.

## 8. Expected Validation Commands

Baseline and final validation:

```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests --memory-limit=1G
php tooling/refactor/check-runtime-composition-leaks.php
php tooling/components/check-component-runtime-assembly.php
php tooling/refactor/check-public-surface.php
php tooling/components/check-hollow-public-surfaces.php
```

Governance gates when present:

```bash
php tooling/governance/check-truth-consistency.php
php tooling/governance/check-semantic-phpdoc.php
php tooling/governance/check-how-to-document-structure.php
php tooling/governance/check-serviceprovider-governance-consistency.php
php tooling/governance/check-canonical-terms.php
php tooling/governance/check-large-unit-thresholds.php
php tooling/governance/check-quality-ratchet.php
php tooling/governance/check-security-commit-block-readiness.php
php tooling/governance/check-gate-self-tests.php
```

## 9. Baseline Validation Plan

1. Capture full baseline command output into `EVIDENCE/v5.9-codex/raw/*-before.txt`.
2. Write `EVIDENCE/v5.9-codex/01-baseline-validation.md`.
3. If baseline is RED because current V5.9 code is broken, fix Phase 1 scope only.
4. If baseline is RED because unrelated pre-existing files or stale local state are broken, stop phase and reconcile
   truth before continuing.

## 10. High-Risk Areas

| Area                                        | Risk                                                                                                      |
|---------------------------------------------|-----------------------------------------------------------------------------------------------------------|
| `BootDsl::create()`                         | PublicSurface class currently assembles HTTP/runtime services directly.                                   |
| `Avax::create()` and `Avax::bootInternal()` | Existing public facade still manually constructs runtime graph.                                           |
| `BootDslEngine::createRuntimeAndApp()`      | Manual graph assembly remains; root container ownership is accepted YELLOW debt, not full green.          |
| `ProviderRegistry`                          | Duplicate provider behavior is not explicit yet.                                                          |
| `compileContainer()` / `verifyContainer()`  | Only verifies four core bindings; missing service graph dependencies are not fully proven before runtime. |
| Route DSL                                   | Public route DSL was removed/deferred; any docs/examples claiming route DSL would be false.               |
| Management truth                            | `.agents/management/ACTIVE.md` is stale and says V5.9 blocked.                                            |
| Existing dirty files                        | `.agents/how-to/how-to.txt`, `.codex`, and `avax.txt` changed before this pass.                           |

## 11. Phase Execution Plan

| Phase | Goal                                                                         | Commit Candidate                                   |
|-------|------------------------------------------------------------------------------|----------------------------------------------------|
| 0     | Preflight and truth classification                                           | Evidence only unless truth correction is required. |
| 1     | Verify and harden corrected Boot DSL first slice                             | `v5.9: harden boot dsl first slice`                |
| 2     | Strengthen root Application Container ownership and boot lifecycle           | `v5.9: strengthen root container boot ownership`   |
| 3     | Route loading / HTTP Golden Path only if route DSL is intentionally in scope | `v5.9: prove boot dsl route loading`               |
| 4     | Documentation, examples, and public API honesty                              | `v5.9: document boot dsl first slice`              |
| 5     | At most one high-value remaining blocker phase if validation remains GREEN   | Scope-specific hardening commit                    |

## 12. Commit Policy

Commit per clean phase only after:

1. implementation/fix complete
2. targeted validation passes
3. relevant gates pass
4. recursive governance review finds no unresolved BLOCKER/HIGH/MEDIUM
5. any YELLOW is formally accepted with owner, target, risk, expiry, evidence, and blocking decision
6. evidence is written
7. truth/backlog files are reconciled
8. `git status` and staging are clean and intentional

No push is allowed unless explicitly requested.

## 13. Preflight Decision

Status: YELLOW

V5.9 execution may continue to baseline validation because the latest evidence permits V5.9 and the first slice exists.
However, the repo does not support a pure FULL_GREEN first-slice claim because root container ownership remains accepted
YELLOW debt and management board truth is stale.

Next allowed action: run baseline validation and write `EVIDENCE/v5.9-codex/01-baseline-validation.md`.
