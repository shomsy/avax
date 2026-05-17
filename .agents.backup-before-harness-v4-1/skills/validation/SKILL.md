# Skill: Validation

## Purpose

This skill proves that code is green, safe, and production-ready.

## Trigger

Use this skill when the user asks for:

- validate
- prove green
- check
- verify
- test
- run checkers
- is it ready
- is it safe

## Must Read

- `AGENTS.md`
- `.agents/GOVERNANCE_INDEX.md`
- `.agents/how-to/how-to-architecture.md`
- `.agents/how-to/how-to-design-components.md`
- `.agents/how-to/how-to-code-review.md`
- `.agents/GOVERNANCE_ENFORCEMENT_MAP.md`
- `CURRENT_TRUTH.md`

## Focused Validation

For local/narrow work, run focused validation:

```bash
php tooling/refactor/check-component-canonical-shape.php
php tooling/refactor/check-advanced-pattern-folder-violations.php
php tooling/security/check-security-naming.php
php tooling/performance/check-performance-naming.php
vendor/bin/phpstan analyse <path> --memory-limit=1G --error-format=raw --no-progress
```

## Full Validation

For architecture/foundation/scope changes, run full validation:

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
php tooling/governance/check-stage-lock.php
```

## Validation Matrix

| Check              | Scope             | Command                                      |
|--------------------|-------------------|----------------------------------------------|
| component shape    | new component     | check-component-canonical-shape.php          |
| forbidden folders  | any edit          | check-advanced-pattern-folder-violations.php |
| security naming    | security work     | check-security-naming.php                    |
| performance naming | performance work  | check-performance-naming.php                 |
| stage lock         | V2/V3 work        | check-stage-lock.php                         |
| governance index   | new docs          | check-governance-index-current.php           |
| autoload           | namespace changes | composer dump-autoload -o                    |
| tests              | any change        | phpunit --no-coverage                        |
| static analysis    | any PHP           | phpstan                                      |

## Output

Must produce:

```text
Stage:
Status:            (GREEN/YELLOW/RED/BLOCKER)
Validation run:
Evidence:
Remaining risks:
Next allowed action:
```

## Evidence Rule

If a claim is important, it must point to test output, static analysis output, audit report, or documented governance
rule.

No evidence means not proven.