# TODO

Current Source of Truth:

- CURRENT_TRUTH.md
- Code-Review-And-ToDo/EXECUTION.md
- Code-Review-And-ToDo/truth-reconciliation/truth-reconciliation-report.md

## Active Stage

Stage: Stage 00 — Current Truth Lock
Status: RED
Goal: Establish one trusted repository truth and resolve all V1 Kernel Green blockers

## Current Tasks

[ ] Fix composer.json/composer.lock sync (phpstan ^1.10 vs 2.1.54, psalm missing from lock)
[ ] Create tooling/refactor/categorize-phpstan-errors.php to parse and prioritize PHPStan fixes
[ ] Expand phpstan.neon to cover full codebase (framework, components, tests) honestly
[ ] Fix PHPStan errors by component — use audit_broken_refs.php output to prioritize
[ ] Fix Cache component PSR-4 namespace violations (27+ autoload skips)
[ ] Resolve 797 broken references (502 CRITICAL, 295 MINOR)
[ ] Add meaningful tests beyond current 12 — map untested components first
[ ] Achieve static analysis GREEN or create an honest baseline
[ ] Prove V1 Kernel Green

## Locked

### V2

Locked until:

- V1 Kernel Green proven

### V3

Locked until:

- V1 Kernel Green proven
- V2 platform baseline at least YELLOW/GREEN
- labs/SystemDesignKit MVP approved

## Forbidden

[ ] no production code changes outside active stage
[ ] no V2 implementation while locked
[ ] no V3 implementation while locked
[ ] no placeholders
[ ] no broad refactor

## Available Tooling

Use existing tooling to accelerate blocker resolution (per how-to-write-avax.md):

```text
tooling/audit_broken_refs.php                         — prioritize broken ref fixes (797 missing)
tooling/refactor/check-component-suite-structure.php  — verify suite structure (currently PASS)
tooling/refactor/check-duplicate-owners.php           — verify no duplicates (currently PASS)
tooling/refactor/check-namespace-drift.php            — verify no drift (currently PASS)
tooling/refactor/check-public-surface.php             — verify public surface (currently PASS)
tooling/refactor/check-runtime-leaks.php              — verify no leaks (currently PASS)
tooling/check-superglobals.php                        — verify no superglobals (currently PASS)
tooling/docs/validate-docs.php                        — verify docs (currently PASS)
tooling/docs/validate-docs-mirror-source.php          — verify doc mirror (currently PASS)
```

Create new tooling if needed:

```text
tooling/refactor/categorize-phpstan-errors.php — group PHPStan raw output by error type and component
tooling/refactor/list-psr4-skips.php           — extract PSR-4 violations from composer dump output
tooling/refactor/audit-test-coverage-map.php   — map which components have tests vs. which don't
```

## Validation Commands

```bash
git status --short
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress
vendor/bin/psalm --no-progress
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
php tooling/audit_broken_refs.php
php tooling/docs/validate-docs.php
php tooling/docs/validate-docs-mirror-source.php
php tooling/check-superglobals.php
php avax runtime:doctor
```

## Next Stage Queue

Do not start these until Stage 00 is GREEN.

```text
[ ] Stage 01: Final Project Tree Freeze
[ ] Stage 02: Taxonomy Integrity Green
[ ] Stage 03: API Classification and Evolution Rules
[ ] Stage 04: Component Completion
[ ] Stage 05: Canonical Class Map
[ ] Stage 06: Autoload and Namespace Repair
[ ] Stage 07: Test Layer Repair
[ ] Stage 08: Static Analysis Green
[ ] Stage 09: AvaX Kernel Green
[ ] Stage 10: Production Readiness Baseline
[ ] Stage 11: Golden Path App
```

## Locked Planning Sections

These may be planned, reviewed, or documented. They must not be implemented until V1 Kernel Green.

### V2 Locked

```text
[ ] API Contract Engine
[ ] Integration Engine
[ ] Reliability Engine
[ ] Operations Engine
[ ] Observability Engine
[ ] Security / Identity / Tenancy Engine
[ ] Delivery Engine
[ ] Runtime Supervision Engine
[ ] Memory Lifecycle Engine
[ ] Developer Experience Engine
```

### V3 Locked

```text
[ ] SystemDesignKit
[ ] Capacity model
[ ] Load model
[ ] Latency budget
[ ] Availability target
[ ] Consistency model
[ ] Partitioning model
[ ] Sharding model
[ ] Replication model
[ ] Messaging model
[ ] Projection model
[ ] Failure model
[ ] Simulation runner
[ ] Architecture tests
[ ] Reference architectures
[ ] Tradeoff reports
```

## Agent Completion Report Template

Every agent must finish work with:

```text
Stage:
Status:
Files changed:
Files intentionally not touched:
Validation commands run:
Validation summary:
Remaining risks:
Next allowed action:
```

No evidence means incomplete. No validation means incomplete. No TODO update means incomplete.

## Current Instruction For The Next Agent

```text
Read CURRENT_TRUTH.md.
Read EXECUTION.md.
Read this TODO.md.
Read how-to-write-avax.md.
Discover .agents/how-to/*.md.
Use tooling/ scripts to optimize work.
Execute Stage 00 blocker fixes only.
First priority: fix composer.json/composer.lock sync.
Second priority: create PHPStan error categorization tooling.
Third priority: fix PHPStan errors by component.
Do not implement any V2/V3 feature.
Do not touch production code outside blocker scope.
Return a Stage 00 report with evidence.
```
