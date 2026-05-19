# Security Must Scream Rule

## Decision

The Security Must Scream rule is added to:

- `how-to-system-security.md` — as new Section 40
- `how-to-code-review.md` — as new Section 16
- `how-to-production-readiness.md` — as new Security section
- `how-to-git.md` — as new Section 7

## Rule Text

### Security Must Scream Rule

Security-sensitive findings MUST be loud, explicit, and blocking by default.

Any OWASP-class weakness, injection risk, authentication bypass, authorization bypass, sensitive data leak, unsafe
deserialization, unsafe redirect, filesystem traversal, command execution risk, SSRF risk, XSS risk, CSRF risk,
SQL/query injection risk, weak cryptography, secret exposure, unsafe logging, or session/cookie weakness MUST be
classified as HIGH or BLOCKER unless proven otherwise.

Security findings MUST NOT be hidden as:

- cleanup
- style issue
- minor refactor note
- pre-existing harmless debt
- accepted risk without owner/expiry
- non-blocking note
- code quality nit
- low-priority cleanup

A security finding may be downgraded only with:

- exact threat explanation
- affected path
- exploitability assessment
- mitigation proof
- test or gate evidence
- owner
- expiry if accepted temporarily
- truth/backlog entry
- explicit stage-blocking decision

Minimum severity rule:

- exploitable or likely exploitable security weakness = BLOCKER
- potential OWASP-class weakness = HIGH or BLOCKER
- defense-in-depth gap = MEDIUM or HIGH, depending blast radius
- documentation-only security clarification = LOW only when no exploit path exists

## Verification

All 4 documents now contain the Security Must Scream rule with consistent wording.
