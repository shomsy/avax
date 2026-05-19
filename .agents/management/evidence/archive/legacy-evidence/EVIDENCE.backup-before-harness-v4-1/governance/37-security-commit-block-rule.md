# Security Commit Block Rule

## Decision

The Security Commit Block rule is added to:

- `how-to-git.md` — as new Section 8
- `how-to-system-security.md` — as new Section 42
- `how-to-code-review.md` — as new Section 18
- `how-to-production-readiness.md` — as new Section 13

## Rule Text

### Security Commit Block Rule

A commit is FORBIDDEN if the current change introduces, exposes, or leaves unresolved any security issue classified as
BLOCKER, HIGH, OWASP-class weakness, authentication bypass, authorization bypass, injection risk, XSS risk, CSRF risk,
SSRF risk, unsafe redirect, unsafe deserialization, path traversal, command execution risk, secret exposure, sensitive
data logging, weak cryptography/hashing, session/cookie weakness, unsafe file upload/download, database query injection
risk, unsafe event payload crossing trust boundary, unsafe queue payload handling, unsafe tenant boundary, or unsafe
plugin/sandbox execution.

The agent MUST stop before commit, document the finding, fix it, rerun validation/security review, and only then
continue. Security issues MUST NOT be hidden as cleanup, style issue, low priority, pre-existing harmless debt,
non-blocking note, or accepted risk without proof.

A security issue may remain only if final status is YELLOW or RED, never GREEN. If temporarily accepted, it MUST have
exact issue, affected path, severity, exploitability assessment, owner, mitigation, expiry/version, backlog/truth entry,
evidence, and explicit decision whether it blocks the current stage.

GREEN commit with unresolved security issue is forbidden.
