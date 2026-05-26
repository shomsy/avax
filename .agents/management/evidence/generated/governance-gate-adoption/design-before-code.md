# Design Before Code

Generated: 2026-05-26

## High-Level Design

The pass converts three global legacy-blocking gates into phased enforcement:

- PHPStan: wrapper enforces full, baseline, and changed modes.
- Self-explaining architecture: existing checker keeps full mode and adds baseline/changed modes.
- Shallow tests: existing checker keeps full mode and adds baseline/changed modes.

The design preserves strict full-mode semantics. Baseline mode is an adoption layer, not a bypass.

## Low-Level Design

Changed files:

- `tooling/validation/governance-gate-baseline-lib.php`: shared baseline, changed-file, and reporting helpers.
- `tooling/validation/check-phpstan-baseline.php`: PHPStan phased enforcement wrapper.
- `tooling/governance/check-self-explaining-architecture.php`: `--mode` and `--write-baseline` support.
- `tooling/testing/check-shallow-tests.php`: `--mode` and `--write-baseline` support.
- `tooling/governance/generate-review-packs.php`: blocked review-pack README language and adoption evidence inclusion.
- Governance how-to/index files: policy and command routing.

## Failure Behavior

- Full mode fails on any finding.
- Baseline mode fails on new findings and stale baseline entries.
- Changed mode fails on changed-scope blockers.
- If Git is unavailable inside PHP, changed-file discovery falls back to Git index/worktree metadata and avoids returning false zero.

## Out of Scope

- Fixing all legacy PHPStan findings.
- Fixing all legacy self-explaining architecture findings.
- Fixing all legacy shallow tests.
- Identity implementation.
