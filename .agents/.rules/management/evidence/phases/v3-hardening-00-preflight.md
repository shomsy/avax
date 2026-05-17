# V3 Hardening Pass — Phase 0: Preflight

**Date**: 2026-05-16
**Branch**: main
**Real Commit SHA**: 8d4e3d274a4eca00e4e95f81ba068e31a417e26b

## Current State Audit

### Branch / Commit
- Branch: `main`
- HEAD: `8d4e3d274a4eca00e4e95f81ba068e31a417e26b`
- Last real commit message: `feat: implement comprehensive agent governance, management, and testing frameworks with extensive policy and configuration schemas.`

### Staged Files (problem)
- `agent-harness.part-1-of-4.txt` (D = deleted, was staged)
- `agent-harness.part-2-of-4.txt` (D)
- `agent-harness.part-3-of-4.txt` (D)
- `agent-harness.part-4-of-4.txt` (D)
- `agent-harness.txt` (D)

These are generated merge snapshot files — staged deletions. Must be handled before commit.

### Generated Artifacts in Root (not in .gitignore)
| Path | Type | Tracked? | Problem |
|---|---|---|---|
| `test-install-dry/` | Generated install test output | YES (untracked but present) | Not in .gitignore |
| `create_schemas.py` | Temp script (from previous pass) | YES (untracked) | Should be ignored or removed |
| `update_profiles.py` | Temp script (from previous pass) | YES (untracked) | Should be ignored or removed |
| `polish_profiles.py` | Temp script (from previous pass) | YES (untracked) | Should be ignored or removed |
| `agent-harness.txt` | Generated snapshot | Was tracked, now deleted | OK to clean |

### Old Model Files
| File | Status | Problem |
|---|---|---|
| `PARENT-AGENTS.md` | MISSING — never existed in this repo | N/A |
| `MCP-STACK.md` | Exists with concrete API key examples | Hardcodes GITHUB_TOKEN, BRAVE_API_KEY, DATABASE_URL |
| `EVIDENCE/CURRENT.md` | Claims "V2" model | Stale — V3 work completed |
| `README.md` | Says "Evidence Model (V2)", "Management Model (V2)" | Stale labels |
| Root `AGENTS.md` | Version 2.0.0 | Should be 3.0.0 |

### V3 Files Created in Previous Pass
- `.agents/governance/security/security-operating-model.md`
- `.agents/governance/standards/governance/capability-maturity-model.md`
- `.agents/governance/standards/governance/entropy-control-policy.md`
- `.agents/governance/intelligence/memory/v3-intelligence-lifecycle.md`
- `.agents/governance/delivery/operations/enterprise-operational-lifecycle.md`
- `.agents/governance/delivery/release/advanced-deployment-policy.md`
- `.agents/config/schemas/*.schema.json` (7 schemas — all too shallow)
- `MIGRATION_GUIDE.md`
- `INSTALLER_README.md`

### Schema Quality Audit
All 7 schemas only have: `id`, `timestamp`, `author` — 3 fields, no enums, no required depth, no additionalProperties:false. **USELESS for machine validation.**

### Contradiction Map
| Item | Contradiction |
|---|---|
| `EVIDENCE/CURRENT.md` | Claims V2; `.agents/management/CURRENT.md` claims V3 Hardened |
| `README.md` | Labels "V2" for evidence and management sections |
| Root `AGENTS.md` | Version 2.0.0 despite V3 alignment work |
| `MCP-STACK.md` | Hardcodes concrete secrets — violates parent neutrality |

## Files to Modify
1. `EVIDENCE/CURRENT.md` — sync to V3
2. `README.md` — remove V2 labels, align to V3
3. Root `AGENTS.md` — bump to 3.0.0
4. `.agents/config/schemas/*.schema.json` — harden all 7
5. `.gitignore` — add temp scripts and test-install-dry
6. `MCP-STACK.md` — neutralize, add optional/integration framing

## Files NOT to Touch
- `.agents/AGENTS.md` — already V3
- `.agents/governance/**` — V3 content already good
- `install-os.sh` — already updated
- `tests/smoke-routing-hooks.sh` — existing validation

## Validation Plan
1. Verify schemas parse as valid JSON after hardening
2. Verify no AvaX/PHP in parent core
3. Verify no placeholder SHA in final reports
4. Verify test-install-dry not tracked after gitignore update
5. Verify EVIDENCE/CURRENT.md == machine CURRENT.md in substance

## Rollback Plan
- All changes are in working tree, not committed
- `git checkout <file>` to roll back any specific file
- No DB or external state involved

## Final Status Criteria
- No V1/V2 vs V3 contradiction in root files
- All 7 schemas have ≥10 meaningful fields
- `.gitignore` covers all generated artifacts
- `MCP-STACK.md` is framed as optional integration, not core mandate
- `EVIDENCE/CURRENT.md` says V3
- Root `AGENTS.md` says 3.0.0
- Real SHA used in all evidence
