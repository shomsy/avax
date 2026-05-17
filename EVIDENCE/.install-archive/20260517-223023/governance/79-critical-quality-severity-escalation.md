# Critical Quality Signal Severity Escalation

## Changes

Added BLOCKER severity escalation to all 5 documents containing Critical Quality Signal Rule:

- `how-to-code-review.md` §25 — added escalation section
- `how-to-production-readiness.md` §27 — added escalation section
- `how-to-clean-code.md` §22 — added escalation section
- `how-to-system-security.md` §55 — added escalation section
- `how-to-system-performance.md` §48 — added escalation section

## Escalation Rule

Default: HIGH. Becomes BLOCKER when issue threatens security, data integrity, runtime safety, long-lived worker safety, truth/evidence integrity, public API compatibility, dependency graph correctness, or rollback/recovery safety.
