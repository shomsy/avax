# Skill: Recovery

## Purpose

This skill restores lost or broken behavior from old sources.

## Trigger

Use this skill when the user asks for:

- recover
- restore
- old backup
- Framework.txt
- Components.txt
- avax-backup.txt
- git history restoration
- missing behavior
- fix broken code

## Must Read

- `AGENTS.md`
- `.agents/GOVERNANCE_INDEX.md`
- `.agents/how-to/components/how-to-design-components.md`
- `.agents/how-to/architecture/how-to-architecture-extension-with-ddd.md`
- `.agents/how-to/architecture/how-to-use-advanced-architecture-patterns.md`
- `CURRENT_TRUTH.md`
- `EVIDENCE/EXECUTION.md`
- relevant recovery reports in `EVIDENCE/recovery-reports/`

## Rules

- Old behavior is valuable.
- Old structure is not automatically valuable.
- Current architecture is the target.
- Tests are the judge.
- Do not restore skeletons.
- Do not unlock V2/V3 early.
- Do not create forbidden folders.
- Original behavior must be proven with tests.

## Classification

For each candidate, classify:

```text
source:                     (backup file, git history, old review)
old path:
old namespace:
behavior summary:
target V1/V2/V3/V4 stage:
target component:
target flow or capability:
proof test required:        (yes/no)
risk:                     (low/medium/high)
decision:                 (restore/refactor/defer/reject)
```

## Output

Must produce:

```text
source:
old path:
behavior summary:
target component:
target flow or capability:
required tests:
risk:
next action:
```

## Validation

Required commands:

```bash
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress
php tooling/refactor/check-component-canonical-shape.php
```