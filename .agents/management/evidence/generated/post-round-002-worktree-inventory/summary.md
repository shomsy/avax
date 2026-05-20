# Post-Round-002 Worktree Inventory

Generated: 2026-05-20
Main HEAD: `0b67ef44e` docs(governance): close Round 002 — all 4 branches merged and validated

---

## 1. Main Preflight

| Check | Result |
|-------|--------|
| `git status --short` | CLEAN |
| `git pull --ff-only origin main` | Already up to date |
| `git log -1 --oneline` | 0b67ef44e docs(governance): close Round 002 |
| Branch | main |
| Composer valid | YES |
| Governance index | GREEN |
| Evidence hygiene | GREEN |

---

## 2. Worktree Classification Table

| Worktree | Branch | HEAD | Classification |
|----------|--------|------|----------------|
| avax-round-002-review | review/parallel-round-002 | 153976187 | REVIEW_ONLY_BRANCH |
| avax-todo-003 | security/todo-003-csrf-session-authority | 098f803b0 | ALREADY_MERGED_TO_MAIN |
| avax-todo-004 | security/todo-004-dynamic-class-loading | c3d510fec | REAL_UNMERGED_WORK |
| avax-todo-004-b | security/todo-004-batch-b-container-migration | 1e7145e0e | REAL_UNMERGED_WORK |
| avax-todo-005 | security/todo-005-static-secret-state | 8203dd981 | REAL_UNMERGED_WORK |
| avax-todo-010 | cleanup/todo-010-cache-publicsurface | 0b67ef44e | ALREADY_MERGED_TO_MAIN |
| avax-todo-016 | cleanup/todo-016-broken-reference-semantics | 0ba3f9d85 | ALREADY_MERGED_TO_MAIN |
| avax-todo-017 | cleanup/todo-017-filesystem-boundaries | 50a0d9ff2 | REAL_UNMERGED_WORK |
| avax-todo-018 | security/todo-018-security-logging-redaction | 99243821e | REAL_UNMERGED_WORK |
| avax-todo-019 | security/todo-019-global-helper-shortcuts | 91c015afc | REAL_UNMERGED_WORK |
| avax-todo-026a | cleanup/todo-026a-csv-formula-injection | 920ef56ea | ALREADY_MERGED_TO_MAIN |
| avax-todo-026b | cleanup/todo-026b-compile-data-query-identifiers | c423da81c | ALREADY_MERGED_TO_MAIN |
| avax-todo-phase0 | docs/todo-phase0-reconcile-truth | bd6c44089 | REAL_UNMERGED_WORK |

---

## 3. Branch Containment Table

| Branch | Commit on main | Ahead of main | Behind main | Status |
|--------|---------------|---------------|-------------|--------|
| review/parallel-round-002 | NO | 2 | 10 | DIVERGED (docs only) |
| security/todo-003-csrf-session-authority | YES | 0 | 9 | MERGED |
| security/todo-004-dynamic-class-loading | NO | 1 | 0 | UNMERGED |
| security/todo-004-batch-b-container-migration | NO | 1 | 0 | UNMERGED |
| security/todo-005-static-secret-state | NO | 1 | 0 | UNMERGED |
| cleanup/todo-010-cache-publicsurface | YES | 0 | 0 | EMPTY/SAME AS MAIN |
| cleanup/todo-016-broken-reference-semantics | YES | 0 | 9 | MERGED |
| cleanup/todo-017-filesystem-boundaries | NO | 1 | 0 | UNMERGED |
| security/todo-018-security-logging-redaction | NO | 1 | 0 | UNMERGED |
| security/todo-019-global-helper-shortcuts | NO | 1 | 0 | UNMERGED |
| cleanup/todo-026a-csv-formula-injection | YES | 0 | 9 | MERGED |
| cleanup/todo-026b-compile-data-query-identifiers | YES | 0 | 9 | MERGED |
| docs/todo-phase0-reconcile-truth | NO | 1 | 0 | UNMERGED |

All unmerged branches fork from `0b67ef44e` (current main HEAD). No rebase needed.

---

## 4. Diff Summary Table

| Branch | Files changed | Insertions | Deletions | Key areas |
|--------|--------------|------------|-----------|-----------|
| review/parallel-round-002 | 10 | 996 | 0 | Evidence/docs only |
| security/todo-004 | 16 | 480 | 19 | QueueWorker, FailureBoundary, tests |
| security/todo-004-b | 14 | 328 | 5 | Container ProviderRegistry, Migrations/Seeders, tests |
| security/todo-005 | 8 | 155 | 1 | Secrets, StateReset, tests |
| cleanup/todo-017 | 7 | 74 | 10 | FileSessionStore, FailureBoundary routes, dispatch builder |
| security/todo-018 | 7 | varies | varies | AesEncrypter, EncryptionKey, RequestSigning, tests |
| security/todo-019 | 4 | 115 | 9 | shortcuts.php (HTTP + Security), tests |
| docs/todo-phase0 | 8 | 106 | 25 | CURRENT_TRUTH.md + evidence |

---

## 5. Evidence Availability Table

| Branch | Context-loaded | Impl summary | Validation output | Governance review | Final decision | Test proof | Threat analysis | Decision value |
|--------|---------------|--------------|-------------------|-------------------|---------------|------------|-----------------|----------------|
| TODO-004 | YES | YES | YES | YES | YES | YES | YES | TODO_CLOSED (partial scope) |
| TODO-004-b | YES | YES | YES | YES | YES | NO | YES | TODO_CLOSED |
| TODO-005 | YES | YES | YES | NO | YES | NO | YES | TODO_CLOSED |
| TODO-017 | NO | YES | YES | NO | YES | NO | NO | TODO_CLOSED |
| TODO-018 | NO | NO | YES | NO | NO | NO | YES | NOT_FOUND |
| TODO-019 | NO | NO | YES | NO | NO | NO | NO | NOT_FOUND |
| Phase0 | YES | YES | YES | YES | YES | YES | NO | TODO_CLOSED |

---

## 6. Already Merged Branches

| Branch | HEAD | Merge commit on main |
|--------|------|---------------------|
| security/todo-003-csrf-session-authority | 098f803b0 | c3abfc1bb merge(round-002): integrate TODO-003 |
| cleanup/todo-010-cache-publicsurface | 0b67ef44e | Same as main (empty branch) |
| cleanup/todo-016-broken-reference-semantics | 0ba3f9d85 | 6718fa716 merge(round-002): integrate TODO-016 |
| cleanup/todo-026a-csv-formula-injection | 920ef56ea | 64cc4189a merge(round-002): integrate TODO-026a |
| cleanup/todo-026b-compile-data-query-identifiers | c423da81c | b1a66c781 merge(round-002): integrate TODO-026b |

---

## 7. Real Unmerged Work Branches

| Branch | HEAD | Files | Evidence complete? | Notes |
|--------|------|-------|--------------------|-------|
| security/todo-004-dynamic-class-loading | c3d510fec | 16 | YES | TODO_CLOSED but partial scope; TODO-004-b completes remainder |
| security/todo-004-batch-b-container-migration | 1e7145e0e | 14 | YES | TODO_CLOSED; no test-proof evidence file but tests exist in diff |
| security/todo-005-static-secret-state | 8203dd981 | 8 | PARTIAL | Missing governance-review, test-proof evidence files |
| cleanup/todo-017-filesystem-boundaries | 50a0d9ff2 | 7 | PARTIAL | Missing context-loaded, governance-review, test-proof, threat-analysis |
| security/todo-018-security-logging-redaction | 99243821e | 7 | INCOMPLETE | Missing impl-summary, final-decision, governance-review, test-proof |
| security/todo-019-global-helper-shortcuts | 91c015afc | 4 | INCOMPLETE | Missing impl-summary, final-decision, governance-review, test-proof, threat-analysis |
| docs/todo-phase0-reconcile-truth | bd6c44089 | 8 | YES | TODO_CLOSED; full evidence |

---

## 8. Empty/Stale Cleanup Candidates

| Branch | HEAD | Reason | Safe to delete? |
|--------|------|--------|-----------------|
| cleanup/todo-010-cache-publicsurface | 0b67ef44e | Points to main HEAD; no changes | YES (after confirmation) |
| review/parallel-round-002 | 153976187 | Review docs only; already completed | YES (after confirmation) |

---

## 9. Branches Ready for Review

These branches have real unmerged work with complete or near-complete evidence:

| Branch | Priority | Review readiness |
|--------|----------|-----------------|
| security/todo-004-dynamic-class-loading | P0 (security) | READY — full evidence |
| security/todo-004-batch-b-container-migration | P0 (security) | READY — full evidence |
| docs/todo-phase0-reconcile-truth | P1 (docs) | READY — full evidence |

---

## 10. Branches Blocked from Review

| Branch | Blocker | Required action |
|--------|---------|-----------------|
| security/todo-005-static-secret-state | Missing governance-review, test-proof evidence files | Add evidence files or validate inline |
| cleanup/todo-017-filesystem-boundaries | Missing context-loaded, governance-review, test-proof, threat-analysis | Add evidence files |
| security/todo-018-security-logging-redaction | Missing impl-summary, final-decision, governance-review, test-proof | Add evidence files; work is incomplete by AvaX standards |
| security/todo-019-global-helper-shortcuts | Missing impl-summary, final-decision, governance-review, test-proof, threat-analysis | Add evidence files; work is incomplete by AvaX standards |

---

## 11. Recommended Review Batches

### Batch 1 — TODO-004 Cluster (review together)
- `security/todo-004-dynamic-class-loading` (QueueWorker + FailureBoundary)
- `security/todo-004-batch-b-container-migration` (Container + Migrations/Seeders)

**Rationale:** Both address TODO-004 dynamic class-loading security. They are sequential batches covering different attack surfaces. No file overlap but same security domain. Review together for completeness.

### Batch 2 — Phase0 Docs (independent)
- `docs/todo-phase0-reconcile-truth`

**Rationale:** Pure governance/docs change to CURRENT_TRUTH.md. Independent of all code branches. Can be reviewed in parallel with Batch 1.

### Batch 3 — Security Cross-Cutting (review after Batch 1)
- `security/todo-018-security-logging-redaction` (evidence incomplete — needs remediation)
- `security/todo-019-global-helper-shortcuts` (evidence incomplete — needs remediation)

**Rationale:** Both touch security-sensitive code. No file overlap. Must complete evidence before formal review.

### Batch 4 — Cleanup (review last)
- `cleanup/todo-017-filesystem-boundaries` (evidence incomplete — needs remediation)
- `security/todo-005-static-secret-state` (evidence partially incomplete)

**Rationale:** Independent changes. TODO-005 may have conceptual dependency on TODO-004 (secrets reset lifecycle), but no file overlap.

---

## 12. Blockers

1. **TODO-018** missing final-decision.md — cannot claim TODO_CLOSED without evidence
2. **TODO-019** missing final-decision.md — cannot claim TODO_CLOSED without evidence
3. **TODO-017** missing context-loaded and threat-analysis — partial evidence only
4. **TODO-005** missing governance-review evidence file
5. **TODO-004-b** missing test-proof evidence file (tests exist in diff but evidence file absent)

---

## 13. Next Exact Prompt Target

**Recommended next action:** Run review on Batch 1 (TODO-004 + TODO-004-b) and Batch 2 (Phase0) in parallel. These have the most complete evidence and represent the highest-value security work.

Prompt target: `review/todo-004-cluster-review` and `review/todo-phase0-review`

After those reviews complete, address evidence gaps in TODO-018, TODO-019, TODO-017, and TODO-005 before scheduling their reviews.

---

## 14. Stale Branch/Worktree Cleanup Candidates

After reviews complete and merges happen:

| Branch | Worktree | Action |
|--------|----------|--------|
| cleanup/todo-010-cache-publicsurface | avax-todo-010 | Delete branch + worktree (empty, same as main) |
| review/parallel-round-002 | avax-round-002-review | Delete branch + worktree (review complete) |
| security/todo-003-csrf-session-authority | avax-todo-003 | Delete branch + worktree after merge confirmed |
| cleanup/todo-016-broken-reference-semantics | avax-todo-016 | Delete branch + worktree after merge confirmed |
| cleanup/todo-026a-csv-formula-injection | avax-todo-026a | Delete branch + worktree after merge confirmed |
| cleanup/todo-026b-compile-data-query-identifiers | avax-todo-026b | Delete branch + worktree after merge confirmed |

---

## 15. Validation

| Command | Result |
|---------|--------|
| `composer validate --no-check-publish` | GREEN |
| `php tooling/governance/check-governance-index-current.php` | GREEN |
| `php tooling/governance/check-root-evidence-hygiene.php` | GREEN |
