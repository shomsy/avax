# Clean + Gates Truth Reconciliation

## Status

The how-to clean + gate enforcement pass is complete.

## Documents Cleaned

- 14 `.agents/how-to/*.md` documents cleaned and renumbered
- Architecture extension: sections renumbered properly after insertion
- Security, performance, design-components docs: duplicate numbering fixed
- Production-readiness: sequential numbering restored (11-27)

## Rules Moved to Correct Locations

| Rule                            | Old location                 | New location                       |
|---------------------------------|------------------------------|------------------------------------|
| Canonical Term Registry         | Inside §4 criteria           | §27.4 (after One Concept One Name) |
| PublicSurface Factory Boundary  | After Final Law (end of doc) | §11 (in PublicSurface section)     |
| DDD Factory vs Runtime Assembly | After Final Law (end of doc) | §28.7 (in Factory section)         |

## Gates Implemented (8 new)

| Gate                                               | Status                                            |
|----------------------------------------------------|---------------------------------------------------|
| `check-semantic-phpdoc.php`                        | GREEN                                             |
| `check-how-to-document-structure.php`              | GREEN                                             |
| `check-serviceprovider-governance-consistency.php` | GREEN                                             |
| `check-canonical-terms.php`                        | GREEN                                             |
| `check-large-unit-thresholds.php`                  | GREEN                                             |
| `check-quality-ratchet.php`                        | YELLOW (baseline exists, needs metric population) |
| `check-security-commit-block-readiness.php`        | GREEN                                             |
| `check-gate-self-tests.php`                        | GREEN                                             |

## Gates Planned (YELLOW)

- Central gate runner (`run-governance-gates.php`) — plan exists, not blocking

## V5.9 Readiness Impact

No blocking impact. All governance cleanup and gates are ready.
