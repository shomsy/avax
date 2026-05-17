# V3 Hardening — Phase 8: Security LOUD Blocker Model

**Date**: 2026-05-16

## Security Blocker Matrix

| Security issue | Blocks commit? | Blocks release? | Exception allowed? | Required evidence |
|---|---|---|---|---|
| Active secret in source code | YES | YES | NO — must revoke and fix | Incident evidence + revocation proof |
| OWASP Top 10 exploitable vulnerability | YES | YES | NO | Remediation evidence + re-scan |
| Dependency with critical CVE | YES | YES | NO (>30d) | CVE ref + upgrade evidence |
| Dependency with high CVE | YES | YES | YELLOW (max 30d) | Risk entry + mitigation |
| Privilege escalation by agent | YES | YES | NO | Halt report + human approval |
| Unsafe shell command (`rm -rf /`, pipe-to-shell) | YES | YES | NO | Blocked by approval-policy |
| Missing security headers (MEDIUM) | NO | YELLOW | YES (max 30d) | RISKS.md entry + owner |
| Unsafe AI action without sandbox | YES | YES | NO | Sandbox boundary policy must be satisfied |
| Insecure publish/release flow | NO | YES | NO | Release evidence must include security scan |
| Unredacted PII in logs/evidence | YES | YES | NO | Must be removed and evidence purged |

## Exception Process (for MEDIUM only)
1. Create risk entry in `RISKS.md` conforming to `risk.schema.json`
2. Field `owner` must be a named person, not "team"
3. Field `expiry` must be set (max 30 days)
4. Field `mitigation` must describe active risk reduction
5. Field `blockingDecision` must quote the explicit acceptance text
6. Review finding must reference the RISKS.md entry

## What's New in This Pass
- `security-operating-model.md` already created in previous pass
- This evidence document formalizes the blocker matrix as machine-verifiable reference

## Acceptance Criteria
- [x] Security cannot be silently hidden as quality issue
- [x] HIGH/BLOCKER security findings block commit without exception
- [x] MEDIUM security findings require explicit RISKS.md acceptance
- [x] Exception process is concrete and traceable
