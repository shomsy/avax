# Current Gate Failures

Generated: 2026-05-26

## Commands Captured

Exact raw outputs are stored beside this report:

- `phpstan-current.raw`
- `self-explaining-current.out`
- `shallow-tests-current.out`

Commands:

```bash
vendor/bin/phpstan analyse --memory-limit=1G --error-format=raw --no-progress
php tooling/governance/check-self-explaining-architecture.php
php tooling/testing/check-shallow-tests.php
```

## Summary

| Tool | Exit | Total | Severity Counts | Classification |
|---|---:|---:|---|---|
| PHPStan | 1 | 100 | HIGH/MEDIUM classified in baseline | Legacy baseline debt; changed-scope enforcement required |
| Self-explaining architecture | 1 | 563 | BLOCKER 0, HIGH 172, MEDIUM 184, LOW 207 | Legacy baseline debt; changed-scope HIGH/BLOCKER enforcement required |
| Shallow tests | 1 | 392 | HIGH 345, MEDIUM 47, LOW 0 | Legacy baseline debt; changed-test enforcement required |

## Top Patterns

PHPStan:

- PHPUnit assertions that always evaluate to true or false.
- Parameter type mismatches in tests.
- Identity-related type and attribute issues.

Self-explaining architecture:

- Missing README files for important boundaries.
- Missing dictionaries for important boundaries.
- Missing ADR folders.
- Orphan documentation references.
- Missing Mermaid diagrams for complex flows.

Shallow tests:

- `missing_failure_assertions`: 133
- `no_negative_assertions`: 97
- `implementation_coupled_test`: 78
- `meaningless_assertion`: 35
- `happy_path_only_auth`: 22
- `getter_setter_only_test`: 20
- `no_assertions`: 3
- `missing_fail_closed_test`: 3
- `fake_coverage_farming`: 1

## Immediate BLOCKERs

No immediate unbaselined BLOCKER was identified in the captured legacy output.

## Legacy Baseline Candidates

All captured findings are treated as legacy baseline candidates because they existed before this adoption pass and are not introduced by the current changed scope.

## Changed-File Enforcement Candidates

- PHPStan: changed PHP files under `framework/`, `components/`, and `tests/`.
- Self-explaining architecture: changed files that intersect documented ownership-boundary findings.
- Shallow tests: changed test files with any shallow-test finding.
