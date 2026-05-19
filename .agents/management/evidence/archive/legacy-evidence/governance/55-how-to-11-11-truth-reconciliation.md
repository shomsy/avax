# How-To 11/11 Truth Reconciliation

## Status

The how-to 11/11 governance hardening pass is complete.

## Documents Changed

| Document | Action |
|---|---|
| `.agents/how-to/how-to-coding-standards.md` | Fixed ServiceProvider contradiction (line 1157) |
| `.agents/how-to/how-to-system-security.md` | Added Security Must Scream (§40), Security Review Trigger (§41), Security Commit Block (§42), Critical Quality Signal (§43), Security/Performance Trigger Cross-Rule (§44) |
| `.agents/how-to/how-to-code-review.md` | Added Security Must Scream (§16), Security Review Trigger (§17), Security Commit Block (§18), Critical Quality Signal (§19), Security/Performance Trigger Cross-Rule (§20), Large Unit Thresholds (§21), Canonical Term Registry (§22), Exception Register (§23). Upgraded Gate Self-Test (§14.2) to BLOCKER. |
| `.agents/how-to/how-to-production-readiness.md` | Added Security Must Scream (§11), Security Review Trigger (§12), Security Commit Block (§13), Critical Quality Signal (§15), Gate Self-Test (§16), No Zero-Scan Gate (§17), Quality Ratchet (§18), Status State Machine (§20), Component Status Ownership (§21), Security/Performance Trigger Cross-Rule (§22), Large Unit Thresholds (§23), Examples Are Architecture (§24), Canonical Term Registry (§25), PublicSurface Factory Boundary (§26), DDD Factory vs Runtime Assembly (§27), Exception Register (§28). Updated Final Verdict (§30) to historical. |
| `.agents/how-to/how-to-git.md` | Added Security Must Scream (§7), Security Commit Block (§8), Gate Self-Test (§9), No Zero-Scan Gate (§10), Quality Ratchet (§11), Examples Are Architecture (§13), Exception Register (§14) |
| `.agents/how-to/how-to-system-performance.md` | Added Security/Performance Trigger Cross-Rule (§44), Critical Quality Signal (§45) |
| `.agents/how-to/how-to-clean-code.md` | Added Critical Quality Signal (§22), Large Unit Thresholds (§23) |
| `.agents/how-to/how-to-document.md` | Added Examples Are Architecture, Canonical Term Registry |
| `.agents/how-to/how-to-dependency-injection.md` | Added PublicSurface Factory Boundary (§15), DDD Factory vs Runtime Assembly (§16). Updated Final Law (§17). |
| `.agents/how-to/how-to-design-components.md` | Added Component Status Ownership (§22), Large Unit Thresholds (§23) |
| `.agents/how-to/how-to-architecture.md` | Added Canonical Term Registry (§54) |
| `.agents/how-to/how-to-architecture-extension-with-ddd.md` | Added PublicSurface Factory Boundary (§50), DDD Factory vs Runtime Assembly (§51) |
| `.agents/how-to/how-to-dogfooding.md` | Fixed 4-backtick fence defects |
| `.agents/how-to/how-to-unit-test.md` | Removed Serbian text. Added Gate Self-Test cross-reference (§76). |
| `.agents/how-to/how-to-events-listeners-event-sourcing-cqrs-realtime.md` | Updated V5.7/V5.8/V5.9 roadmap status (§27) |
| `docs/governance/canonical-terms.md` | **NEW** — canonical terms registry |
| `EVIDENCE/accepted-exceptions-ledger.md` | **NEW** — global exception register at canonical path |
| `EVIDENCE/governance/32-*.md` through `EVIDENCE/governance/55-*.md` | **NEW** — 23 evidence files |

## Rules Clarified

| Rule | Before | After |
|---|---|---|
| ServiceProvider requirement | Ambiguous "every component" | "Every ACTIVE production component..." with exempt statuses |
| Security finding severity | No minimum severity rule | BLOCKER for exploitable, HIGH for potential OWASP, MEDIUM for defense-in-depth |
| Security review | No trigger list | 34-item trigger list with mandatory evidence table |
| Security commit block | No rule forbidding GREEN commit with security issue | Explicitly FORBIDDEN with required action |
| Gate self-test | Not required for GREEN | BLOCKER for mandatory gates without self-test |
| Status definitions | Scattered, vague | Exact GREEN/YELLOW/RED criteria with forbidden patterns |
| Component status | Nonexistent | 8 allowed statuses with required fields |

## Remaining Governance Gaps

1. **Component Status Ownership gate** — no dedicated automated gate. Must be enforced via manual review.
2. **Security gate** — no dedicated automated security governance gate with self-test proof.
3. **Gate self-test proof** — existing gates (check-direct-instantiation, check-service-provider-coverage, etc.) lack confirmed negative test cases.

## Gate Gaps

See `52-how-to-11-11-gate-alignment.md` for full gate alignment table.

## Security Governance Status

All P0 security rules are now in place across 4 documents (security, code-review, production-readiness, git).

## V5.9 Readiness Impact

This pass does not block V5.9. Governance hardening improves the rule system but does not introduce new runtime requirements. V5.9 may proceed when the current plan lock allows it.
