# Checker Hardening Report

Task: Engineering Canon Convergence checker hardening.

## Files Hardened

| File | Change |
|---|---|
| `tooling/governance/check-scenario-input.php` | Uses shared changed-file helper, rejects unsupported modes, fails on missing production scenario evidence. |
| `tooling/governance/check-coupling-decisions.php` | Uses shared changed-file helper, rejects unsupported modes, aligns headings with template. |
| `tooling/governance/check-architecture-fitness-functions.php` | Uses shared changed-file helper, rejects unsupported modes, makes governance-sensitive missing evidence RED. |
| `tooling/governance/check-antipatterns.php` | Requires exact 11-entry dictionary and exact headings; rejects unsupported modes. |
| `tooling/governance/check-engineering-canon-traceability.php` | Requires helper files, actual-changes packer, exact dictionary list, title format, and exact heading format. |
| `tooling/governance/generate-review-packs.php` | Removed fixed git path fallback; git is discovered with `command -v git`. |

## Hardcoded Git Status

Command to verify:

```bash
rg -n "/usr/bin/git" tooling/governance/check-*.php tooling/sdlc || true
```

Latest observed result during this pass: no output.

## Mode Honesty

The four changed-scope checkers now accept `changed`, `baseline`, and `full` only.

- `changed`: implemented.
- `baseline`: exits non-zero with exact not-implemented message.
- `full`: exits non-zero with exact not-implemented message.

Unsupported mode does not silently pass.

## Architecture Fitness Strictness

Governance-sensitive changed files under `.agents/how-to/**`, `.agents/knowledge/**`, `tooling/governance/check-*`, `.agents/GOVERNANCE_INDEX.md`, `.agents/how-to/00-how-to-reading-order.md`, `tooling/sdlc/**`, `AGENTS.md`, and `ARCHITECTURE.md` require changed architecture-fitness evidence or an explicit exception.
