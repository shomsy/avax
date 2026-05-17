# V3 Hardening — Recursive Governance Review

**Date**: 2026-05-16
**Reviewer**: Antigravity (automated pass + human-level reasoning)
**Commit reviewed**: 8d4e3d274a4eca00e4e95f81ba068e31a417e26b + hardening changes

## Scope

Reviewed: root AGENTS.md, README.md, MCP-STACK.md, EVIDENCE/, .agents/AGENTS.md,
.agents/governance/**, .agents/management/**, .agents/config/schemas/**, installer scripts,
scaffolds/**, profiles/**, architecture profile overlays.

## Findings

| Finding | Severity | Fixed? | Remaining? | Decision |
|---|---|---|---|---|
| Root AGENTS.md version 2.0.0 | MEDIUM | YES | NO | Bumped to 3.0.0 |
| EVIDENCE/CURRENT.md claims V2 | MEDIUM | YES | NO | Replaced with V3 truth |
| README.md says "Evidence Model (V2)" | LOW | YES | NO | Label removed |
| All 7 schemas had only 3 fields each | HIGH | YES | NO | All hardened to 10-15 fields |
| MCP-STACK.md exposed concrete API key examples | HIGH | YES | NO | Neutralized; opt-in framing added |
| test-install-dry/ not in .gitignore | MEDIUM | YES | NO | Added to .gitignore |
| Temp Python scripts not in .gitignore | LOW | YES | NO | Added to .gitignore |
| No architecture profiles for style overlays | LOW | YES | NO | 4 profiles created |
| Previous final reports had placeholder SHA | MEDIUM | YES | NO | This pass uses real SHA throughout |
| Installer upgrade/migrate not smoke-proven | MEDIUM | PARTIAL | YES — YELLOW | Accepted as DEBT-INSTALLER-01 |

## BLOCKER Findings: 0
## HIGH Findings resolved: 2 (schemas, MCP secrets)
## MEDIUM Findings resolved: 4 / 5 (1 accepted YELLOW)
## LOW Findings resolved: 3

## Accepted YELLOW Debt

| Field | Value |
|---|---|
| **ID** | DEBT-INSTALLER-01 |
| **Owner** | maintainer |
| **Target** | Run `./install-os.sh /tmp/clean-repo --upgrade` and `--migrate` against a freshly installed target; capture output as smoke evidence |
| **Risk** | LOW — dry-run, profile selection, and core install all work. Logic for upgrade/migrate is implemented. |
| **Expiry** | 2026-06-16 |
| **Evidence** | `.agents/management/evidence/phases/v3-hardening-05-installer-proof.md` |
| **Blocking Decision** | Accepted. Does not block this commit. Blocks final sign-off as "FULL_GREEN_NO_DEBT". |

## Parent Genericity Check
- No AvaX, PHP, PolyMoly, or project-specific terms in `.agents/governance/` core ✅
- No framework forced globally ✅
- Architecture styles are optional overlays ✅
- Language profiles are optional ✅

## Final Decision: APPROVED_WITH_DEBT
