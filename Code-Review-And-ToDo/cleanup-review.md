# Cleanup Review Report (REGENERATED - HONEST)

> Generated: 2026-04-27
> Status: AUDITED - REALITY CHECKED

---

## Migration Integrity Matrix

| Component          | Status (Previous) | Status (Actual) | Problem                                                                 |
|--------------------|-------------------|-----------------|-------------------------------------------------------------------------|
| **DataFoundation** | DONE              | **PARTIAL**     | Still contains 600+ lines of real behavior (Arrhae, Collection).        |
| **DataLayer**      | DONE              | **DONE**        | Genuinely removed.                                                      |
| **Database**       | DONE              | **PARTIAL**     | Legacy root files remain; unsafe SQL found.                             |
| **Config**         | DONE              | **PARTIAL**     | PublicSurface contained mutable state + loading logic (now refactored). |
| **Namespace**      | DONE              | **DRIFTING**    | Mixed `components\` and `Avax\` namespaces persist.                     |

---

## Security Audit (SQL)

| File                         | Issue                      | Status                              |
|------------------------------|----------------------------|-------------------------------------|
| `DatabaseExporter.php`       | `addslashes` in INSERT     | **FIXED** (using PDO::quote)        |
| `ColumnSQLRenderer.php`      | `addslashes` in COMMENT    | **FIXED** (using str_replace)       |
| `RouterMetricsCollector.php` | `addslashes` in Prometheus | **FIXED** (using explicit escaping) |

---

## PublicSurface Audit

| Component       | Status        | Action Taken                                                |
|-----------------|---------------|-------------------------------------------------------------|
| **Config**      | **COMPLIANT** | Moved state to ConfigurationRepository and loading to Flow. |
| **Data**        | **COMPLIANT** | Thin interface delegating to Capabilities.                  |
| **Persistence** | **COMPLIANT** | Thin interface delegating to Capabilities.                  |

---

## Namespace Drift Report (Post-Cleanup)

| Folder                      | Status       | Priority                                     |
|-----------------------------|--------------|----------------------------------------------|
| `DataFoundation/`           | **CRITICAL** | Needs full migration to `Data/System`.       |
| `ApplicationWorkflow/Saga/` | **HIGH**     | Uses `components\` lowercase namespace.      |
| `Container/`                | **MEDIUM**   | Generated code uses `components\` namespace. |

---

## Recovered Skeleton Audit Summary

**15 files** in `ApplicationWorkflow/Saga/` were identified as skeletons.

- 14 are pure architectural placeholders.
- 1 (ChooseCompensationSteps) was "partial" logic marked as skeleton.
- Full report in: `Code-Review-And-ToDo/recovered-skeletons.md`

---

## Critical Remaining Risks

1. **Stale behavior in DataFoundation**: Until `Arrhae` and `Collection` are fully migrated, we have split ownership.
2. **Namespace Drift**: The `composer.json` still maps `components\` to `components/`, enabling legacy drift.
3. **ORM extraction**: Database component still contains ORM-like behavior (EntityManager) that belongs in Persistence.

---

## Next Steps

1. Complete Phase 3 (DataFoundation -> Data full migration).
2. Execute `tooling/refactor/check-namespace-drift.php` in CI.
3. Address ADR 0017 (Router Boundary Promotion).