# how-to-git.md

# AvaX Git & Workflow Governance

## 1. Status

**MANDATORY** — This document defines the non-negotiable workflow for commits, pushes, and reviews in AvaX.

---

## 2. Core Principle

AvaX is governed by evidence, not optimism.

Commit/push is forbidden after validation only.

---

## 3. Workflow Protocol

Commit/push is allowed only after:

1. **Implementation/Fix Complete**: The task is fully implemented according to the requirement.
2. **Validation Passes**: The focused or full validation set (per `AGENTS.md`) is GREEN.
3. **Relevant Gates Pass**: Architecture, security, and performance gates are GREEN.
4. **Recursive Governance Code Review Passes**: The work has been reviewed against all relevant `how-to-*.md` documents.
5. **Evidence is Written**: Operational artifacts and proof reports are updated in `EVIDENCE/`.
6. **Truth/Backlog Reconciled**: `CURRENT_TRUTH.md`, `TODO.md`, and `BUGS.md` are updated.
7. **Staged Files are Intentional**: `git status` shows only the intended changes.
8. **No Local Junk**: No cache, generated, or local files (`.phpunit.cache`, `vendor`, `.qoder/worktrees`, etc.) are
   committed.

---

## 4. Mandatory Recursive Governance Review Before Commit

For the detailed recursive review protocol and required checks, see:

```text
.agents/how-to/how-to-code-review.md
```

**Short version:**

```text
Implementation is not complete when tests pass.
Implementation is complete only when validation passes AND governance review passes.
```

---

## 5. Commit Discipline

- MUST check `git status` before every commit.
- MUST use descriptive commit messages.
- MUST NOT commit `vendor/`.
- MUST NOT commit `.phpunit.cache`.
- MUST NOT commit `avax.txt` (unless explicitly requested as a snapshot).
- MUST NOT commit `.qoder/worktrees/`.
- MUST NOT introduce accidental structural drift.

---

## 6. Final Gate

A task MUST NOT be marked CLOSED until the evidence report is committed and the status in `CURRENT_TRUTH.md` is updated
to GREEN based on that evidence.

---

## 7. Security Must Scream Rule

### Status

**MANDATORY**
**Severity:** BLOCKER

### Rule

Security-sensitive findings MUST be loud, explicit, and blocking by default.

Any OWASP-class weakness, injection risk, authentication bypass, authorization bypass, sensitive data leak, unsafe deserialization, unsafe redirect, filesystem traversal, command execution risk, SSRF risk, XSS risk, CSRF risk, SQL/query injection risk, weak cryptography, secret exposure, unsafe logging, or session/cookie weakness MUST be classified as HIGH or BLOCKER unless proven otherwise.

Security findings MUST NOT be hidden as:

```text
cleanup
style issue
minor refactor note
pre-existing harmless debt
accepted risk without owner/expiry
non-blocking note
code quality nit
low-priority cleanup
```

A security finding may be downgraded only with:

```text
exact threat explanation
affected path
exploitability assessment
mitigation proof
test or gate evidence
owner
expiry if accepted temporarily
truth/backlog entry
explicit stage-blocking decision
```

### Minimum Severity Rule

- exploitable or likely exploitable security weakness = **BLOCKER**
- potential OWASP-class weakness = **HIGH** or **BLOCKER**
- defense-in-depth gap = **MEDIUM** or **HIGH**, depending on blast radius
- documentation-only security clarification = **LOW** only when no exploit path exists

### Commit Rule

A commit MUST NOT be created if any unresolved BLOCKER or HIGH security issue exists in the changed scope.

---

## 9. Gate Self-Test Rule

### Status

**MANDATORY**
**Severity:** BLOCKER

### Rule

Every mandatory validation gate MUST have at least one negative test case proving it fails when the rule is violated.

The Git gate must fail on:

```text
.phpunit.cache/**
.qoder/worktrees/**
avax.txt
generated local cache files
forbidden local AI cache files
```

A gate that cannot fail is not a gate.

## 10. No Zero-Scan Gate Rule

Every validation report MUST include the count of items scanned. 0 items scanned = UNPROVEN.

## 11. Quality Ratchet Rule

Quality metrics MUST NOT regress from the previously established baseline.

For full rule text including tracked metrics and evidence table, see:

```text
.agents/how-to/how-to-code-review.md §14.1
.agents/how-to/how-to-production-readiness.md §18
```

---

## 13. Examples Are Architecture Rule

Examples, GoldenPath apps, and documentation snippets MUST show canonical framework usage. They MUST NOT show manual runtime service assembly, hidden fallback dependencies, or service locator patterns.

If examples teach an anti-pattern, the codebase will reproduce it.

## 14. Governance Exception Register Rule

Every governance exception MUST be recorded in `EVIDENCE/accepted-exceptions-ledger.md` with owner and expiry. An exception without owner and expiry is not an exception — it is unresolved governance debt.

---

## 15. Security Commit Block Rule

### Status

**MANDATORY**
**Severity:** BLOCKER

### Rule

A commit is FORBIDDEN if the current change introduces, exposes, or leaves unresolved any security issue classified as:

```text
BLOCKER
HIGH
OWASP-class weakness
authentication bypass
authorization bypass
injection risk
XSS risk
CSRF risk
SSRF risk
unsafe redirect
unsafe deserialization
path traversal
command execution risk
secret exposure
sensitive data logging
weak cryptography or hashing
session or cookie weakness
unsafe file upload or download
database query injection risk
unsafe event payload crossing trust boundary
unsafe queue payload handling
unsafe tenant boundary
unsafe plugin or sandbox execution
```

### Required Action

1. Do not commit.
2. Document the finding.
3. Fix it first.
4. Rerun validation.
5. Rerun security review.
6. Rerun recursive governance review.
7. Commit only when security review is clean.

### Temporary Acceptance

A security issue may remain only if final status is YELLOW or RED. Never GREEN.

If temporarily accepted, it MUST have:

```text
exact issue
affected path
severity
exploitability assessment
owner
mitigation
expiry or version
backlog or truth entry
evidence
explicit decision whether it blocks the current stage
```

### Forbidden Masking

Security issues MUST NOT be hidden as:

```text
cleanup
style issue
low priority
pre-existing harmless debt
non-blocking note
accepted risk without proof
```
