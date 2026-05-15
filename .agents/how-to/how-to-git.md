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
