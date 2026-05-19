# EVIDENCE — Human Dashboard Only

Root EVIDENCE/ is a small human-readable dashboard only (max 10 files, max 10MB, no subdirectories).
Canonical machine evidence lives under `.agents/management/evidence/`.

## Current Dashboard Files

| File | Purpose |
|:---|:---|
| `README.md` | Dashboard model and rules |
| `CURRENT.md` | Current operational state |
| `ACTIVE_PLAN.md` | Active execution plan |
| `FLOW.md` | Execution flow and phases |
| `LINKS.md` | Quick links to machine evidence |
| `EXECUTION.md` | Canonical execution control and stage lock |
| `accepted-exceptions-ledger.md` | Governance exception ledger |
| `cleanup_execution_report.md` | Latest cleanup summary |

## Where Machine Evidence Lives

| Path | Contents |
|:---|:---|
| `.agents/management/evidence/generated/` | Generated reports, reconciliation, benchmarks |
| `.agents/management/evidence/archive/legacy-evidence/` | Historical version evidence (v1-v5, recovery) |
| `.agents/management/evidence/indexes/` | JSON ledgers and structured catalogs |
| `.agents/management/evidence/validation/` | Raw validation outputs |

## Enforcement

Hygiene is enforced by `php tooling/governance/check-root-evidence-hygiene.php`.
Fails if: any subdirectory, >10 files, >10MB, archive folders, or .install-archive.

## Last Cleanup

2026-05-19 — Moved 34 dirs to archive, 17 reports to generated, deleted 2 runtime noise items.
