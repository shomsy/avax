# Data Correctness Governance Report

This document reports the governance of data correctness and transaction integrity.

## What Changed
- Validated that `.agents/templates/evidence/data-correctness.md` matches `tooling/governance/check-data-correctness-evidence.php` heading-for-heading.
- Validated checking of headings: Task, System of Record, Derived State, Cache Behavior, Transaction Boundary, Idempotency, Retry Behavior, Duplicate Handling, Ordering, Consistency Expectation, Stale Read Behavior, Schema Evolution, Failure Matrix, Reconciliation, Observability, Tests / Evidence, Review Date.

## Why It Was Needed
- Ensured consistent and automated inspection of data correctness decisions for data-sensitive modules in the codebase.

## Files Affected
- `.agents/templates/evidence/data-correctness.md`
- `tooling/governance/check-data-correctness-evidence.php`

## Validation Command(s)
```bash
/usr/bin/php8.4 tooling/sdlc/validate-governance.php
/usr/bin/php8.4 vendor/bin/phpunit tests/Unit/Tooling/Governance/DataCorrectnessEvidenceCheckTest.php
```

## Result
- **PASS**: Mappings align, tests successfully identify missing files and malformed headings.

## Remaining Risks
- Large scale data refactorings need to be planned beforehand using the template to avoid validation blocks in the CI pipeline.
