# Fix-This Program Preflight

Date: 2026-05-15
Branch: main
Commit: 014e97b3b (governance: prove final how-to quality gates)

## Worktree Status

```
M .agents/how-to/how-to.txt
 M avax.txt
 M fix-this.md
```

3 modified files, all governance/documentation. No production code dirty.

## Discovered How-To Files (19)

1. how-to-architecture-extension-with-ddd.md
2. how-to-architecture.md
3. how-to-clean-code.md
4. how-to-code-review.md
5. how-to-code-style.md
6. how-to-coding-standards.md
7. how-to-dependency-injection.md
8. how-to-design-components.md
9. how-to-document.md
10. how-to-dogfooding.md
11. how-to-events-listeners-event-sourcing-cqrs-realtime.md
12. how-to-git.md
13. how-to-modern-php-attributes-di.md
14. how-to-production-readiness.md
15. how-to-runtime-composition.md
16. how-to-system-performance.md
17. how-to-system-security.md
18. how-to-unit-test.md
19. how-to-use-advanced-architecture-patterns.md

## Discovered Governance Gates (12)

1. check-canonical-terms.php
2. check-component-adoption.php
3. check-gate-self-tests.php
4. check-governance-index-current.php
5. check-how-to-document-structure.php
6. check-large-unit-thresholds.php
7. check-quality-ratchet.php
8. check-security-commit-block-readiness.php
9. check-semantic-phpdoc.php
10. check-serviceprovider-governance-consistency.php
11. check-stage-lock.php
12. check-truth-consistency.php

## Discovered Refactor Tools (key)

- check-runtime-composition-leaks.php
- check-runtime-leaks.php
- check-direct-instantiation.php
- check-constructor-bloat.php
- check-container-service-locator.php
- check-public-surface.php
- check-service-provider-coverage.php
- check-component-canonical-shape.php
- check-advanced-pattern-folder-violations.php
- check-forbidden-folders.php
- check-namespace-drift.php
- check-duplicate-owners.php

## Discovered Component Tools (key)

- check-component-runtime-assembly.php
- check-hollow-public-surfaces.php
- check-component-status-lock.php
- check-component-status-lock-coverage.php
- check-no-unclassified-scaffolding.php

## Current Validation Baseline

| Command                                    | Result   | Count                                                             |
|--------------------------------------------|----------|-------------------------------------------------------------------|
| composer validate                          | GREEN    | -                                                                 |
| composer dump-autoload -o                  | GREEN    | 9327 classes                                                      |
| phpunit --no-coverage                      | GREEN    | 8351 tests, 24026 assertions, 0 errors, 0 failures, 1 deprecation |
| phpstan analyse framework components tests | GREEN    | 0 errors                                                          |
| check-runtime-composition-leaks.php        | **FAIL** | 163 findings                                                      |
| check-component-runtime-assembly.php       | **FAIL** | 3 violations                                                      |
| check-public-surface.php                   | PASS     | -                                                                 |
| check-hollow-public-surfaces.php           | PASS     | -                                                                 |
| check-truth-consistency.php                | **FAIL** | -                                                                 |

## Fix-This Finding Groups

From fix-this.md:

| Group | Description                   |                   Count | Severity            |
|-------|-------------------------------|------------------------:|---------------------|
| A     | Runtime Composition Leaks     |                     197 | BLOCKER/HIGH/MEDIUM |
| B     | Direct Runtime Instantiation  |                      48 | HIGH                |
| C     | Constructor Bloat (8+)        |                     139 | WARNING             |
| D     | Large Units                   |           365+1 BLOCKER | BLOCKER/REVIEW      |
| E     | AuthBuilder BLOCKER           |              1729 lines | BLOCKER             |
| F     | ServiceProvider Coverage      | 1 missing + 35 SCAFFOLD | HIGH                |
| G     | Semantic PHPDoc Debt          |                  17,245 | HIGH (legacy)       |
| H     | Security/Performance Triggers |                     TBD | TBD                 |
| I     | Gate Proof / Enforcement      |                  2 FAIL | BLOCKER             |
| J     | Component Status Ownership    |                     TBD | TBD                 |

## Planned Phases

| Phase | Scope                          | Target                          |
|-------|--------------------------------|---------------------------------|
| 0     | Preflight                      | This file                       |
| 1     | Worktree baseline              | Classify dirty files            |
| 2     | Baseline validation            | Capture all gate outputs        |
| 3     | Finding inventory              | Map fix-this groups to files    |
| A     | Runtime composition hot-path   | Close active runtime leaks      |
| B     | Container exception policy     | Separate compile vs runtime     |
| C     | Direct runtime instantiation   | Remove new in runtime           |
| D     | AuthBuilder split              | 1729 lines -> explicit owners   |
| E     | ServiceProvider coverage       | TestSupport + classify SCAFFOLD |
| F     | Semantic PHPDoc ratchet        | Touched files only              |
| G     | Large unit / constructor bloat | Highest risk items              |
| H     | Security/performance review    | Touched areas                   |
| I     | Gate proof                     | All gates GREEN                 |
| J     | Full validation                | PHPUnit + PHPStan + gates       |
| K     | Recursive governance review    | Full compliance check           |
| L     | Truth reconciliation           | Update truth files              |
| M     | Commits                        | Phase commits                   |

## V5.9 Blocker Classification

This program resolves V5.9 blockers:

1. **Runtime composition leaks** (197 findings) — BLOCKER for V5.9
2. **Direct runtime instantiation** (48 findings) — BLOCKER for V5.9
3. **AuthBuilder 1729 lines** — BLOCKER for V5.9
4. **Constructor bloat** (139 classes 8+) — HIGH for V5.9, phased reduction acceptable
5. **ServiceProvider coverage** (1 missing) — HIGH for V5.9
6. **Semantic PHPDoc** (17,245 legacy) — YELLOW with ratchet, not blocking V5.9 if touched files are clean

## Risks

1. **Scope**: 197 runtime composition leaks is massive. Must prioritize hot-path files first.
2. **Container exception**: Container inherently needs class_exists() for autowiring. Gate must distinguish compile vs
   runtime.
3. **AuthBuilder**: 1729-line file split is high-risk for breaking changes. Need comprehensive test coverage.
4. **Evidence integrity**: Must NOT weaken gates. Must NOT fake GREEN.
5. **Dirty files**: 3 modified files in worktree are governance docs, not code. Must not mix into code commits.

## Commands To Be Used

```bash
# Validation
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests --memory-limit=1G

# Gates
php tooling/refactor/check-runtime-composition-leaks.php
php tooling/components/check-component-runtime-assembly.php
php tooling/refactor/check-public-surface.php
php tooling/components/check-hollow-public-surfaces.php
php tooling/governance/check-truth-consistency.php
php tooling/governance/check-semantic-phpdoc.php
php tooling/governance/check-large-unit-thresholds.php
php tooling/governance/check-quality-ratchet.php
php tooling/governance/check-security-commit-block-readiness.php
php tooling/governance/check-canonical-terms.php
php tooling/governance/check-serviceprovider-governance-consistency.php
php tooling/governance/check-gate-self-tests.php
php tooling/governance/check-how-to-document-structure.php

# Refactor tools
php tooling/refactor/check-constructor-bloat.php
php tooling/refactor/check-direct-instantiation.php
php tooling/refactor/check-service-provider-coverage.php
```

## Worktree Classification

**Pre-existing dirty files:**

- `.agents/how-to/how-to.txt` — governance docs, unrelated
- `avax.txt` — project notes, unrelated
- `fix-this.md` — this program's source, will be updated

**Files this program will touch:**

- Production PHP files with runtime composition leaks
- Production PHP files with direct runtime instantiation
- AuthBuilder.php and new auth owners
- ServiceProviders
- Touched tests
- Evidence files in EVIDENCE/fix-this/
- CURRENT_TRUTH.md
- EVIDENCE/EXECUTION.md
- .agents/management/TODO.md
- .agents/management/ACTIVE.md

**Files this program MUST NOT touch:**

- `.agents/how-to/how-to.txt` — pre-existing dirty, unrelated
- `avax.txt` — pre-existing dirty, unrelated
- Unrelated governance documents
- Cache files
- Generated files
- .qoder/worktrees/**
- .phpunit.cache/**

## Decision: Proceed

Baseline is proven. Runtime composition gate FAIL (163 findings), runtime assembly gate FAIL (3 violations), PHPUnit
GREEN, PHPStan GREEN.

This program addresses the FAIL gates directly.
