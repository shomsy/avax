# V3 Hardening — Phase 3: Schema Hardening

**Date**: 2026-05-16

## Schema Hardening Results

| Schema | Before | After | Machine-useful? | Validation |
|---|---|---|---|---|
| `evidence.schema.json` | 3 fields, no required depth | 15 fields, enums, required, additionalProperties:false | YES | `python3 -c "import json; json.load(...)"` PASS |
| `review.schema.json` | 3 fields, no findings structure | 13 fields, findings array with severity enum | YES | PASS |
| `risk.schema.json` | 3 fields | 12 fields, likelihood/severity enums, expiry | YES | PASS |
| `debt.schema.json` | 3 fields | 13 fields, severity constrained to MEDIUM/LOW | YES | PASS |
| `release.schema.json` | 3 fields | 13 fields, approval sub-object, commitSHA | YES | PASS |
| `validation.schema.json` | 3 fields | 12 fields, exitCode, environment, commitSHA | YES | PASS |
| `status.schema.json` | 3 fields | 12 fields, overallStatus enum, nextAllowedAction | YES | PASS |

## Key Design Decisions
1. `debt.schema.json` — severity is constrained to `["MEDIUM", "LOW"]` only. BLOCKER/HIGH cannot be accepted as debt, they must be fixed.
2. `review.schema.json` — findings is a typed array with per-finding severity and resolved flag.
3. `release.schema.json` — approval is a required sub-object with approvedBy + timestamp.
4. All schemas use `additionalProperties: false` to prevent silent additions.
5. All schemas use `required` for mandatory fields.

## Raw Validation Evidence
`.agents/management/evidence/raw/v3-hardening/smoke-validation.txt`

## Acceptance Criteria
- [x] Schemas are not decorative
- [x] Accepted debt is machine-readable (severity, owner, expiry, blockingDecision)
- [x] Review findings are machine-readable (severity, resolved, id)
- [x] Validation result is machine-readable (exitCode, status, commitSHA)
- [x] Release readiness is machine-readable (approval sub-object, blockers, deploymentScope)
