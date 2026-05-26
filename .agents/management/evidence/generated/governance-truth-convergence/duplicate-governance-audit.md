# Duplicate Governance Audit

**Date:** 2026-05-25
**Status:** ACTIVE — results driving convergence pass

## BLOCKER Findings

### 1. Duplicate: how-to-document.md

| Instance | Path | Status |
|----------|------|--------|
| Root shadow | `.agents/how-to/how-to-document.md` | UNTRACKED — must be removed |
| Canonical | `.agents/how-to/documentation/how-to-document.md` | TRACKED (modified) |

**Conflict:** Root-level document is a stale duplicate of the categorized canonical version.
**Canonical winner:** `.agents/how-to/documentation/how-to-document.md`
**Action:** `git rm .agents/how-to/how-to-document.md`
**Risk:** AI agents loading `how-to/**/*.md` recursively will load BOTH, creating confusion about which rule wins.

### 2. Duplicate: how-to-write-self-explaining-architecture.md

| Instance | Path | Status |
|----------|------|--------|
| Root shadow | `.agents/how-to/how-to-write-self-explaining-architecture.md` | UNTRACKED — must be removed |
| Canonical | `.agents/how-to/documentation/how-to-write-self-explaining-architecture.md` | UNTRACKED |

**Conflict:** Two copies of the same document, one at root level (forbidden by .agents/how-to/README.md) and one in correct documentation/ subfolder.
**Canonical winner:** `.agents/how-to/documentation/how-to-write-self-explaining-architecture.md`
**Action:** `rm .agents/how-to/how-to-write-self-explaining-architecture.md`
**Risk:** Any future AI loading order that picks the root copy will miss the categorized version's sibling documents.

### 3. Root-Level Shadow Governance Violation

The `.agents/how-to/README.md` explicitly states:
> "The root `.agents/how-to/` directory contains only this README, `00-reading-order.md`, and `how-to.txt` (a generated artifact that must not be staged per AGENTS.md)."

**Violating files (root level, outside categorized subfolders):**

| File | Action |
|------|--------|
| `.agents/how-to/how-to-document.md` | REMOVE (duplicate) |
| `.agents/how-to/how-to-write-self-explaining-architecture.md` | REMOVE (duplicate) |
| `.agents/how-to/how-to-create-ai-code-review-packs.md` | MIGRATE to `verification/` |
| `.agents/how-to/how-to-test-risk-based-behavioral-testing.md` | MIGRATE to `verification/` |

## HIGH Findings

### 4. Reading Order Missing Entries

The following canonical governance documents exist but are not listed in `00-reading-order.md`:

| Document | Suggested Position |
|----------|-------------------|
| `.agents/how-to/documentation/how-to-write-self-explaining-architecture.md` | After how-to-document.md (Documentation section) |
| `.agents/how-to/how-to-create-ai-code-review-packs.md` (after migration to verification/) | In Verification section |
| `.agents/how-to/how-to-test-risk-based-behavioral-testing.md` (after migration to verification/) | In Verification section, after how-to-unit-test.md |
| `.agents/how-to/implementation/how-to-dependency-injection.md` | Already listed at #21 — OK |

### 5. Reading Order Numbering Bug

Line 54 (`how-to-write-avax.md`) has "28." but the previous section (Documentation) also uses "28." — duplicate numbering.

### 6. Reference Integrity

| Source | Target | Status |
|--------|--------|--------|
| `GOVERNANCE_INDEX.md` | `how-to-test-risk-based-behavioral-testing.md` | References exist but path in reading order is missing |
| `GOVERNANCE_INDEX.md` | `how-to-write-self-explaining-architecture.md` | References exist but path in reading order is missing |

## MEDIUM Findings

### 7. Stale Reference in .agents/how-to/README.md

The README still references `how-to.txt` as generated artifact. This is fine but the rule about root-level files has clearly been violated multiple times since it was written.

## Classification Summary

| Severity | Count | Description |
|----------|-------|-------------|
| BLOCKER | 3 | Duplicate governance files, shadow governance |
| HIGH | 3 | Missing reading order entries, numbering bug, reference mismatches |
| MEDIUM | 1 | Stale governance enforcement |

## Migration Plan

1. `git rm .agents/how-to/how-to-document.md` — remove root duplicate
2. `rm .agents/how-to/how-to-write-self-explaining-architecture.md` — remove root duplicate (untracked, no git rm needed)
3. `git mv .agents/how-to/how-to-create-ai-code-review-packs.md .agents/how-to/verification/how-to-create-ai-code-review-packs.md` — migrate to correct location
4. `git mv .agents/how-to/how-to-test-risk-based-behavioral-testing.md .agents/how-to/verification/how-to-test-risk-based-behavioral-testing.md` — migrate to correct location
5. Update `00-reading-order.md` — add missing entries, fix numbering, add canonical truth section
6. Update `.agents/how-to/README.md` — reflect correct structure
7. Update `GOVERNANCE_INDEX.md` — verify references point to new paths
8. Verification: confirm no duplicates remain, confirm reading order is complete
