# Source Of Truth Decision

Date: 2026-05-26
Branch: architecture/identity-runtime-convergence
Task: Clean and reorganize root `EVIDENCE/`.

## Decision

- New task evidence belongs in `.agents/management/evidence/generated/<task-name>/`.
- Root `EVIDENCE/` is legacy/transitional only.
- Root `EVIDENCE/` must stay small and flat.
- New unavoidable root `EVIDENCE/` files must use `YYYY-MM-DD-HH-MM-SS-descriptive-name.md`.
- Existing non-anchor root evidence files were moved to `.agents/management/evidence/legacy-root-evidence/2026-05-26-root-evidence-restructure/`.
- The exception ledger was moved to `.agents/management/evidence/accepted-exceptions-ledger.md`.

## Root Files Kept

```text
EVIDENCE/README.md
EVIDENCE/EXECUTION.md
EVIDENCE/route-cache-plan.md
```

These remain because current tooling or tests still reference them.
