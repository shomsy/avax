# Remaining Risks

Date: 2026-05-26

| Severity | Risk | Owner | Mitigation |
|---|---|---|---|
| MEDIUM | `EVIDENCE/EXECUTION.md` remains as a legacy stage-lock source. | Governance/tooling owner | Keep until stage-lock tooling is migrated to `.agents/management/evidence/` or another canonical state file. |
| MEDIUM | `EVIDENCE/route-cache-plan.md` remains because a composition test asserts it. | Test/tooling owner | Update the test and plan location in a future focused pass. |
| LOW | Historical references still point to old root evidence paths. | Governance cleanup owner | Treat them as historical references. Use the manifest for current location. |

No evidence content was deleted in this pass.
