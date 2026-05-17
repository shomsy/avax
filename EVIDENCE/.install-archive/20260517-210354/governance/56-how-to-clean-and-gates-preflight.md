# How-To Clean Structure + Enforceable Gates — Preflight

## Identity

| Field   | Value                                                      |
|---------|------------------------------------------------------------|
| Date    | 2026-05-15                                                 |
| Branch  | main                                                       |
| Commit  | e19308e90                                                  |
| Message | governance: add semantic PHPDoc rule to how-to-document.md |
| Mode    | Build — governance cleanup + gate implementation           |

## Worktree

Only 1 dirty file: `.agents/how-to/how-to.txt` (raw backup, unrelated to this pass)

## Discovered How-To Files (19)

All present. No missing files.

## Existing Governance Gates

| Gate                          | Path                                                            |
|-------------------------------|-----------------------------------------------------------------|
| Truth consistency             | `tooling/governance/check-truth-consistency.php`                |
| Stage lock                    | `tooling/governance/check-stage-lock.php`                       |
| Governance index              | `tooling/governance/check-governance-index-current.php`         |
| Component adoption            | `tooling/governance/check-component-adoption.php`               |
| Component runtime assembly    | `tooling/components/check-component-runtime-assembly.php`       |
| Component status lock         | `tooling/components/check-component-status-lock.php`            |
| Hollow public surfaces        | `tooling/components/check-hollow-public-surfaces.php`           |
| Component docs policy         | `tooling/components/check-component-docs-status-policy.php`     |
| Component health policy       | `tooling/components/check-component-health-doctor-policy.php`   |
| Component static state safety | `tooling/components/check-component-static-state-safety.php`    |
| Component behavior proof      | `tooling/components/check-component-behavior-proof-map.php`     |
| ServiceProvider coverage      | `tooling/components/check-service-provider-coverage.php`        |
| No unclassified scaffolding   | `tooling/components/check-no-unclassified-scaffolding.php`      |
| Health proof map              | `tooling/components/check-health-proof-map.php`                 |
| Runtime composition leaks     | `tooling/refactor/check-runtime-composition-leaks.php`          |
| Public surface                | `tooling/refactor/check-public-surface.php`                     |
| Canonical shape               | `tooling/refactor/check-component-canonical-shape.php`          |
| Service locator               | `tooling/refactor/check-container-service-locator.php`          |
| Direct instantiation          | `tooling/refactor/check-direct-instantiation.php`               |
| Constructor bloat             | `tooling/refactor/check-constructor-bloat.php`                  |
| Forbidden folders             | `tooling/refactor/check-forbidden-folders.php`                  |
| Namespace drift               | `tooling/refactor/check-namespace-drift.php`                    |
| Advanced pattern violations   | `tooling/refactor/check-advanced-pattern-folder-violations.php` |

## Documents with Misplaced Sections

| File                                        | Issue                                                                        |
|---------------------------------------------|------------------------------------------------------------------------------|
| `how-to-architecture.md`                    | Canonical Term Registry §54 — needs review; placed at end after final law    |
| `how-to-architecture-extension-with-ddd.md` | PublicSurface Factory §50, DDD Factory §51 — placed after Final Law section  |
| `how-to-production-readiness.md`            | Has inconsistent section numbering (16-30 inserted after original structure) |

## Documents with Heading/Numbering Problems

| File                             | Issue                                                                          |
|----------------------------------|--------------------------------------------------------------------------------|
| `how-to-production-readiness.md` | Section numbering jumps from 19 to 20, 20 to 21, etc. due to inserted sections |
| `how-to-architecture.md`         | Final law section runs to 2059 lines with no clean terminus                    |

## Planned Cleanup Phases

1. Move Canonical Term Registry into how-to-architecture.md at correct location (near One Concept One Name §27.3)
2. Move PublicSurface Factory Boundary into architecture-extension.md PublicSurface section (§9-10)
3. Move DDD Factory rule into architecture-extension.md DDD Factory section (§28.6)
4. Fix heading numbering in production-readiness.md
5. Tighten Semantic PHPDoc severity across 4 docs

## Planned Gate Phases

1. `check-semantic-phpdoc.php` — scans production PHP for missing/fake docblocks
2. `check-how-to-document-structure.php` — scans how-to docs for integrity
3. `check-serviceprovider-governance-consistency.php` — prevents broad ServiceProvider wording
4. `check-canonical-terms.php` — verifies canonical terms registry
5. `check-large-unit-thresholds.php` — reports large code units
6. `check-quality-ratchet.php` — tracks metric regression
7. `check-security-commit-block-readiness.php` — verifies security commit block governance
8. `check-gate-self-tests.php` — meta-inventories gate self-tests

## Validation Commands

```bash
composer validate --no-check-publish
composer dump-autoload -o
php tooling/governance/check-truth-consistency.php
php tooling/refactor/check-public-surface.php
```
