# AvaX Agent OS Adoption Verification

**Date:** 2026-05-18
**Branch:** recovery/clean-before-harness-v6
**Agent Harness Source:** feature/idempotent-adoption-runtime (~/projects/agent-harness)
**Installer Version:** V6.0.0

## Install Result

```
Mode: ADOPT
Created: 0
Updated: 1 (.agents/.rules/skills/bin/agent-harness-diagnose.py)
Preserved: 3 (AGENTS.md, verify-governance.sh, GEMINI.md)
Excluded: 25 (runtime artifacts: __pycache__, quarantine, etc.)
Migrated: 1758 (EVIDENCE -> EVIDENCE/.install-archive)
Conflicts: 1 (AGENTS.md local customization — preserved correctly)
```

## Diagnostics Result

```
Detected Layout: adopted
Overall Status: YELLOW
  adoption_readiness:  GREEN
  upgrade_readiness:   GREEN
  migration_readiness: GREEN
  install_health:      GREEN
  orphan_artifacts:    GREEN
  baseline_integrity:  GREEN
  naming_compliance:   YELLOW
```

## Layout Detection

Correctly detects `.agents/.rules/...` as the adopted layout.
Resolves:
- `.agents/.rules/skills/bin` (32 Python tools, all syntax-clean)
- `.agents/.rules/hooks` (shell + Python hooks present)
- `.agents/.rules/governance` (all key governance files present)

Does NOT falsely require `.agents/skills/bin` or `.agents/governance`.

## Files Fixed

1. **agent-harness-diagnose.py** — Updated by installer from canonical harness source (V6 adoption diagnostics rewrite). The previous local copy was outdated.

## Files Changed (36 total)

- 28 modified `.agents/.rules/` baseline files (from install overlay)
- 8 deleted interrupted install markers (moved to archive)
- New: install journals, HMAC key, archive directories, execution manifests

## Remaining YELLOW Items

1. **naming_compliance: 188 forbidden directory names**
   - These are pre-existing AvaX PHP code structures (Contracts, Diagnostics, Events, Docs, Domain, Repositories, etc.)
   - Located in `components/`, `tests/`, `examples/`, `EVIDENCE/.install-archive/`
   - This is a AvaX codebase naming issue, NOT an Agent OS adoption bug
   - Requires separate AvaX architecture refactor to resolve

2. **Legacy structures: .agents/management/BUGS.md, TODO.md, docs/governance**
   - These are AvaX-specific management files at pre-adopted locations
   - Should be migrated via `install-os.sh --migrate` in a future pass
   - Not blocking adoption

## Interrupted Marker Decision

8 interrupted install markers from session 20260517-205746 through 20260517-210300 were from failed installs before the recovery branch was created. They contained only timestamps (no error context).

**Decision:** Archived to `.agents/management/evidence/archive/install-journal/` as historical evidence. Not deleted.

## AGENTS.md Merge Decision

The 3-way diff showed the harness template (100 lines, child-contract) vs AvaX root AGENTS.md (1521 lines, root-contract). These serve different purposes:

- AvaX `AGENTS.md` = project-specific root contract (preserved)
- `.agents/.rules/AGENTS.md` = reusable harness baseline (updated by installer)

**Decision:** No merge required. The two files coexist correctly at different precedence levels.

## HMAC Key

New HMAC-SHA256 key generated: `27910354c7aa`
Saved to: `.agents/management/evidence/security/hmac-key.bin`

## Next Actions

1. Run `install-os.sh --migrate` to archive legacy structures (BUGS.md, TODO.md, docs/governance)
2. Address AvaX naming compliance (188 forbidden dirs) as separate architecture refactor
3. Clean orphan `__pycache__` directories in backup folders (cosmetic, non-blocking)
