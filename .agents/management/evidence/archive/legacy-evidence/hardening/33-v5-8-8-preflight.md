# V5.8.8 Preflight — PHPStan, Runtime Gate & Truth Integrity Closure

## Date
2026-05-15

## Branch
main

## Commit
1ede9db49 — hardening: V5.8.7 closure hygiene — cache removal, governance review, final audit

## Worktree Status
Clean. No dirty files, no untracked files in main tree.

## Current PHPStan Claim
V5.8.7 claimed "No new errors introduced" with 308 pre-existing errors.
V5.8.7 truth reconciliation said "Remaining YELLOW: None" despite 308 PHPStan errors.
This is dishonest. 308 errors is not GREEN.

## Current Gate Claim
- Runtime composition gate: PASS but mentions pre-existing Cache/GraphQL findings
- Runtime assembly gate: GREEN
- Public surface gate: GREEN
- Hollow public surface gate: GREEN
- Truth consistency gate: GREEN (but truth itself claims no YELLOW while PHPStan has 308 errors)

## Current Truth Claim
CURRENT_TRUTH.md says V5.8.7 = "FULL_GREEN_BASELINE_RESTORED" with 8351 tests GREEN.
EXECUTION.md says V5.8.7 = "FULL GREEN" with "all gates GREEN".
Both ignore PHPStan 308 errors.

## V5.8.7 Honesty Assessment
V5.8.7 PHPUnit baseline restoration is genuine: 8351 tests, 24012 assertions, 0 errors, 0 failures.
V5.8.7 gate results are genuine: all gates PASS.
But calling it "FULL GREEN" is dishonest because PHPStan reports 308 errors.
"No new errors introduced" is not FULL GREEN. Pre-existing debt is still debt.

## Planned Validation Commands

```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress
php tooling/refactor/check-runtime-composition-leaks.php
php tooling/components/check-component-runtime-assembly.php
php tooling/refactor/check-public-surface.php
php tooling/components/check-hollow-public-surfaces.php
php tooling/governance/check-truth-consistency.php
```

## Planned Fix Groups

1. **PHPStan root-cause inventory** — parse all 308 errors into groups with fix strategies
2. **PHPStan remediation** — fix by priority: missing classes, constructor drift, param drift, types, arrays, mixed, tests
3. **Runtime gate reality classification** — classify every Cache/GraphQL finding as active/non-active/blocking/accepted
4. **Truth reconciliation** — update CURRENT_TRUTH.md and EXECUTION.md to match actual validation state
5. **Backlog reconciliation** — update TODO.md, ACTIVE.md, BUGS.md

## Risk Assessment
- HIGH: 308 PHPStan errors may include real type bugs, not just style warnings
- MEDIUM: Runtime gate Cache/GraphQL findings may be active runtime leaks
- LOW: Truth file updates are clerical once validation is honest
- LOW: Pass 1 and Pass 2 guarantees should be preserved (scope-limited fixes)

## Next Allowed Action
Run full validation baseline, capture raw outputs, begin PHPStan inventory.
