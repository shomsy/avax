# 03 — Bug Inventory

**Date:** 2026-05-12
**Branch:** main
**Commit:** 36949b7ac25a67412251cac172d1fb36d29b86c6
**Scope:** P0-P3 bug scan across framework, components, tests, tooling

## Findings

| Finding                                                     | File                        |          Line | Severity           | Real? | Action                                                          | Owner   |
|-------------------------------------------------------------|-----------------------------|--------------:|--------------------|-------|-----------------------------------------------------------------|---------|
| `assertTrue(true)` — Span attribute overwrite test          | SpanTest.php                |           158 | P2_CODE_QUALITY    | Yes   | FIXED — asserts exported attribute value                        | agent   |
| `assertTrue(true)` — Span exception recording test          | SpanTest.php                |           180 | P2_CODE_QUALITY    | Yes   | FIXED — asserts event count and data                            | agent   |
| `assertTrue(true)` — Span various types test                | SpanTest.php                |           275 | P2_CODE_QUALITY    | Yes   | FIXED — asserts all types via export                            | agent   |
| `assertTrue(true)` — EventBus no handlers test              | EventBusTest.php            |            63 | P2_CODE_QUALITY    | Yes   | CLASSIFIED ALLOWED — void return, no-exception proof            | —       |
| `assertTrue(true)` — MessageBus unregistered event test     | MessageBusTest.php          |           109 | P2_CODE_QUALITY    | Yes   | CLASSIFIED ALLOWED — void return, no-exception proof            | —       |
| `assertTrue(true)` — EnforceBackpressure below threshold    | EnforceBackpressureTest.php |            25 | P2_CODE_QUALITY    | Yes   | CLASSIFIED ALLOWED — void return, no-exception proof            | —       |
| PSR-4 autoload warnings: `Tests\...` namespace              | 15 test files               |             — | P2_STATIC_ANALYSIS | Yes   | BACKLOG — namespace migration requires test registry update     | human   |
| PSR-4 autoload warnings: FailureBoundary test sub-namespace | 4 test files                |             — | P2_STATIC_ANALYSIS | Yes   | BACKLOG — namespace flattening required                         | human   |
| Mutable `DateTime` in RetentionPolicyManager                | RetentionPolicyManager.php  | 7,22,28,46,47 | P3_ROADMAP         | Yes   | BACKLOG — should be DateTimeImmutable but non-critical          | roadmap |
| Mutable `DateTime` in MigrationGenerator                    | MigrationGenerator.php      |          8,43 | P3_ROADMAP         | Yes   | BACKLOG — generates timestamp string, immutability not critical | roadmap |
| Mutable `DateTime` in ProcessRegistry                       | ProcessRegistry.php         |       7,34,43 | P3_ROADMAP         | Yes   | BACKLOG — formats date strings, immutability not critical       | roadmap |
| Mutable `DateTime` in RotatingFileWriter (2 files)          | RotatingFileWriter.php ×2   |             — | P3_ROADMAP         | Yes   | BACKLOG — date formatting, immutability not critical            | roadmap |
| `@phpstan-ignore` on ReactPhp server                        | RunReactHttpServer.php      |           143 | FALSE_POSITIVE     | Yes   | ALLOWED — PSR response set via try/catch, phpstan can't prove   | —       |
| `@noinspection SqlNoDataSourceInspection`                   | PdoSessionRegistry.php ×2   |             — | FALSE_POSITIVE     | Yes   | ALLOWED — IDE hints for example SQL, not production code        | —       |
| `@phpstan-ignore` on enum range checks                      | HttpStatusCode.php ×5       |        91-143 | FALSE_POSITIVE     | Yes   | ALLOWED — PHPStan can't narrow enum range on value comparison   | —       |
| `check-raw-file-operations.php` UNAVAILABLE                 | tooling/                    |             — | P2_CODE_QUALITY    | Yes   | BACKLOG — gate missing, document as UNAVAILABLE                 | roadmap |

## Summary

- **P0 (Security/Correctness):** 0
- **P1 (Runtime/Test/Static):** 0
- **P2 (Code Quality):** 3 fixed (SpanTest), 3 classified ALLOWED (void-return no-exception tests), 2 backlogged (
  PSR-4), 1 backlogged (missing gate)
- **P3 (Roadmap):** 4 DateTime immutability items backlogged
- **False Positive:** 5 @phpstan-ignore/@noinspection items classified ALLOWED

## Bugs Fixed in This Pass

1. SpanTest: 3 `assertTrue(true)` replaced with behavior assertions using `export()` — +14 assertions
2. TODO.md: 5 V5.6-Y items truth-reconciled from todo/blocked to done

## Bugs Backlogged

1. PSR-4 namespace migration for 15+ test files (requires coordinated namespace change)
2. DateTime→DateTimeImmutable in 4 non-critical files (date formatting only)
3. Missing `check-raw-file-operations.php` gate
