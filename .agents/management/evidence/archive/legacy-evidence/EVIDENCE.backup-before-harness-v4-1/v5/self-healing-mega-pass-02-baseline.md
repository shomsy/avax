# V5 Self-Healing Mega Pass 02 — Baseline

**Date:** 2026-05-10
**Branch:** main

## Current Validation Status

| Command                                                 | Result                               |
|---------------------------------------------------------|--------------------------------------|
| `composer validate --no-check-publish`                  | GREEN                                |
| `composer dump-autoload -o`                             | GREEN                                |
| `vendor/bin/phpunit --no-coverage`                      | GREEN (7451 tests, 21635 assertions) |
| `vendor/bin/phpstan analyse framework components tests` | GREEN (0 errors)                     |
| `php tooling/security/check-security-blockers.php`      | PASS                                 |
| `php tooling/governance/check-component-adoption.php`   | PASS                                 |
| `php tooling/security/check-raw-file-operations.php`    | FAIL (234 violations)                |

## DeadLetter Status (Before)

- Resilience/DeadLetter: owns generic pattern (DeadLetterStore interface + InMemoryDeadLetterStore)
- Queue/FailedJobs: owns PDO-backed FailedJobsStore for CLI operations
- Queue/MemoryQueue: uses inline private `$deadLetters` array — acceptable for in-memory lightweight broker but not
  explicit store
- Status: **YELLOW** — inline state in MemoryQueue, documented as in-memory-only

## Raw File Operations (Before)

Total: **234 violations**

| Category              | Count | Assessment                               |
|-----------------------|------:|------------------------------------------|
| Container compilation |    66 | NEEDS MIGRATION — container cache writes |
| PreCommit framework   |    42 | ALLOWED — tooling-like context           |
| Other                 |    39 | MIXED — needs classification             |
| Observability writers |    16 | MUST MIGRATE — should use Filesystem     |
| Logging writers       |     8 | MUST MIGRATE — should use Filesystem     |
| Cache storage         |     6 | ALLOWED — Filesystem behavior internals  |
| Session storage       |     6 | ALLOWED — Filesystem behavior internals  |
| Filesystem component  |     6 | ALLOWED — canonical owner internals      |
| Security/Cryptography |     3 | NEEDS REVIEW                             |
| Database              |     1 | NEEDS REVIEW                             |

## Component Adoption Gate (Before)

- Status: **PASS**
- Scope: narrow (Redaction duplicate check only)
- Must expand to: Queue, Messaging, Runtime, SchemaGeneration, Storage, Filesystem, Observability, Reliability
  relationships

## Security Gate (Before)

- Status: **PASS**

## Enforcing vs Report-Only Gates

| Gate                            | Mode                                 |
|---------------------------------|--------------------------------------|
| `check-security-blockers.php`   | ENFORCING (fails on P0/P1)           |
| `check-raw-file-operations.php` | ENFORCING (fails on any violation)   |
| `check-component-adoption.php`  | REPORT-ONLY (passes on narrow check) |

## Scope for This Pass

1. DeadLetter explicit store and reset-safe queue state
2. Raw file operations gate hardening (categorization)
3. Logging/Observability file writers → Filesystem
4. Remaining production runtime file I/O migration
5. Component adoption gate hardening
6. Security/serialization re-check
7. Evidence update
8. Full validation loop
