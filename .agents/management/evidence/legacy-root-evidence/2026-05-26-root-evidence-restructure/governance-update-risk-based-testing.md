# Governance Update — Risk-Based Behavioral Testing

**Date:** 2026-05-25
**Stage:** Governance Update (REMEDIATION_ACTIVE)
**Status:** GREEN

## Source-of-Truth Decision

- AGENTS.md is the root contract
- how-to-unit-test.md is the detailed testing governance
- TEST_STRATEGY.md is the project-level strategy summary
- No contradictions detected with existing governance
- Identity.txt change is pre-existing/unrelated

## Changed Files

| File | Change |
|------|--------|
| `AGENTS.md` | Added §24A Risk-Based Behavioral Testing Rule |
| `.agents/how-to/verification/how-to-unit-test.md` | Added §93 Risk-Based Behavioral Testing Policy |
| `.agents/management/evidence/TEST_STRATEGY.md` | Rewritten with full risk-based strategy |

## Governance Additions

1. **Risk-based testing philosophy** — Explicitly states that coverage percentage is NOT truth; behavioral proof IS truth. V1 optimizes for behavioral confidence, not line coverage inflation.

2. **Required test types** — Mandatory categories: happy path, failed-when/sad-path, validation-path, security-path, runtime/lifecycle. A unit with only happy-path coverage is NOT proven.

3. **Forbidden shallow tests** — Enforceable list of patterns that are BLOCKER unless accepted as YELLOW debt: assertTrue(true), meaningless not-null, constructor-only, getter/setter-only, coverage-padding, implementation-detail obsession, mocking entire SUT, tests that only prove execution, tests written only to increase percentages.

4. **Coverage policy by phase** — V1: no 100% required, behavioral confidence required, branch/path preferred, critical flows must be tested. Hardening: coverage may increase toward 100%, used as review aid. Enterprise: high coverage expected, behavioral correctness more important.

5. **Identity/security special rules** — Identity, Auth, Tokens, Sessions, Authorization, Security, Tenant boundaries MUST have positive + negative + denial + invalidity + fail-closed tests. No security flow may be GREEN with only happy-path coverage.

## Conflicts Detected

- None. Existing §12-17 scenario coverage rules in how-to-unit-test.md complement the new rules. §43 coverage rule is aligned. §81 forbidden anti-patterns are compatible.
- The `components/Identity/Identity.txt` change is pre-existing and unrelated.

## Validation Proof

- `git diff --check`: no whitespace errors
- `php tooling/governance/check-governance-index-current.php`: GREEN
- `php tooling/governance/check-stage-lock.php`: GREEN, stage allows governance work

## Future Enforcement Tooling Opportunities

1. **Static analysis rule** — PHPStan rule to flag `assertTrue(true)`, bare `assertNotNull`, constructor-only tests
2. **Coverage gate** — Script that verifies critical paths have negative tests, not just line coverage
3. **Test type audit** — Tool that scans test suites for required categories (happy/sad/security/runtime) and reports gaps
4. **Shallow test detector** — Regex-based scanner for shallow patterns in new PRs
5. **Security test matrix gate** — Verifies identity/auth/token/session units have negative + fail-closed tests before GREEN

## Verification

All three files were re-read and verified for:
- Correct formatting
- Consistent terminology
- No contradictions with existing rules
- Alignment with AvaX governance language
- Enterprise-grade tone
