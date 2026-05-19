# Phase B Correction Preflight

**Date:** 2026-05-15
**Purpose:** Preflight assessment for Phase B correction — inspect suspected issues before any code changes

## 1. Branch & Commit

- **Branch:** main
- **Current commit:** ae00a5a30 (Phase B Closure: Facade Self-Instantiation & Reset Proof — FULL_GREEN)
- **Working tree:** clean (unrelated dirty files: .agents/how-to/how-to.txt, avax.txt)

## 2. Suspected Phase B Issues

Based on independent code review findings:

| # | Issue | Severity | Description |
|---|---|---|---|
| 1 | ApiVersion still self-instantiates VersionRegistry | HIGH | `??= new VersionRegistry()` in `registry()` method — lazy fallback creates runtime machinery |
| 2 | Pipeline still self-instantiates HookRegistry | HIGH | `??= new HookRegistry()` in `registry()` method — lazy fallback creates runtime machinery |
| 3 | HookRegistry in PublicSurface | MEDIUM | Internal mutable registry lives in PublicSurface, not in Capabilities/ |
| 4 | ApiVersionResolved duplicate class | MEDIUM | Inline class in ApiVersion.php may duplicate ApiVersionResolved.php |
| 5 | PipelineServiceProvider wiring unused | MEDIUM | Provider registers HookRegistry but facade creates its own independent instance |
| 6 | Gate too broad on reset/setInstance | HIGH | `isStaticFacadeFile()` only checks method names, not whether lazy new exists |
| 7 | Reset is test-only, not framework lifecycle | MEDIUM | reset() exists but not wired into StaticStateReset or worker lifecycle |
| 8 | Phase B status claim too strong | HIGH | Reported FULL_GREEN but self-instantiation still present |

## 3. Current Status Claims

- Phase B reported: `FULL_GREEN_PHASE_B_FACADE_DEBT_CLOSED`
- Reality: `reset()`/`setInstance()` added but **lazy `??= new` fallback remains** in both ApiVersion and Pipeline
- **The self-instantiation was not removed, only lifecycle-proofed**
- **This is not FULL_GREEN — it is GREEN_WITH_ACCEPTED_YELLOW_DEBT at best**

## 4. Validation Commands

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

## 5. Gates to Run

- Runtime composition leaks
- Runtime assembly
- Public surface
- Hollow public surface

## 6. Planned Fix Strategy

The core issue: **adding reset/setInstance does not fix self-instantiation — it only makes it testable.**

For honest closure, one of two paths:

**Path A — Remove lazy fallback entirely (FULL_GREEN):**
- ApiVersion: Provider wires VersionRegistry, facade has NO lazy new fallback, missing config fails clearly
- Pipeline: Provider wires HookRegistry, facade has NO lazy new fallback, missing config fails clearly
- HookRegistry: Move from PublicSurface to Capabilities/
- ApiVersionResolved: Remove duplicate, keep one file

**Path B — Formally accept as YELLOW debt (honest reclassification):**
- Document that lazy fallback remains intentional for standalone usage
- Classify as YELLOW with owner/target/risk/expiry
- Update status to GREEN_WITH_ACCEPTED_YELLOW_DEBT

**Recommendation:** Path A is the right fix. The laws are clear: "PublicSurface receives and delegates", "PublicSurface must not assemble runtime service graphs", "Missing required dependencies fail during container compile/verify/boot".

The lazy `??= new` violates all of these. It must be removed.
