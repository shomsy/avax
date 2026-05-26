# EVIDENCE - Legacy Root Dashboard

Root `EVIDENCE/` is not the normal evidence workspace.

New agent evidence belongs under:

```text
.agents/management/evidence/generated/<task-name>/
```

Root `EVIDENCE/` remains only as a small legacy/transitional dashboard for paths that current tooling or tests still require.

## Current Root Files

| File | Why It Remains |
|---|---|
| `README.md` | Explains the legacy root dashboard policy. |
| `EXECUTION.md` | Legacy stage-lock source still read by current tooling. |
| `route-cache-plan.md` | Legacy V4 developer-experience plan still asserted by an existing composition test. |

## Moved Files

Legacy root evidence files were moved to:

```text
.agents/management/evidence/legacy-root-evidence/2026-05-26-root-evidence-restructure/
```

The governance exception register now lives at:

```text
.agents/management/evidence/accepted-exceptions-ledger.md
```

## New Root Evidence Rule

Do not create new root `EVIDENCE/` files.

If a legacy tool or explicit human instruction requires a new file in root `EVIDENCE/`, the filename must use this format:

```text
YYYY-MM-DD-HH-MM-SS-descriptive-name.md
```

Example:

```text
2026-05-26-14-30-00-runtime-doctor-validation.md
```

## Enforcement

Run:

```bash
php tooling/governance/check-root-evidence-hygiene.php
```

The root dashboard should stay small, flat, and transitional.
