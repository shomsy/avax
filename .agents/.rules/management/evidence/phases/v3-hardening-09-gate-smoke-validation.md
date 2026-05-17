# V3 Hardening — Phase 9: Gate / Smoke Validation

**Date**: 2026-05-16
**Commit at start**: 8d4e3d274a4eca00e4e95f81ba068e31a417e26b
**Raw output**: `.agents/management/evidence/raw/v3-hardening/smoke-validation.txt`

## Smoke Validation Results

| Check | Tool/script | Result | Blocks FULL_GREEN? |
|---|---|---|---|
| All 7 schemas parse as valid JSON | `python3 -c "import json; json.load(...)"` | ✅ PASS — all 7 schemas OK | YES if fail |
| `project.schema.json` parses | `python3 -c "import json; json.load(...)"` | ✅ PASS | YES if fail |
| All 7 EVIDENCE dashboard files exist | `test -f` for each | ✅ PASS — all 7 present | YES if fail |
| All 7 management files exist | `test -f` for each | ✅ PASS — all 7 present | YES if fail |
| No placeholder SHA in truth/ | `grep -r "SHA-V3\|SHA-HARDENED"` | ✅ PASS — none found | YES if fail |
| No AvaX leakage in parent governance | `grep -ri "avax" .agents/governance/` | ✅ PASS — none found | YES if fail |
| No V2 claim in EVIDENCE/CURRENT.md | `grep "V2\|v2" EVIDENCE/CURRENT.md` | ✅ PASS — none found | YES if fail |
| No `docs/governance` in precedence | `grep -n "docs/governance"` | ✅ PASS — none found | YES if fail |
| No PHP leakage in parent core | `grep -ri "declare(strict_types"` in core/ | ✅ PASS — none found | YES if fail |
| Installer dry-run works | `./install-os.sh /tmp/x --dry-run` | ✅ PASS | YES if fail |
| Valid profile selected correctly | `--language=go --dry-run` | ✅ PASS — "Selecting language profile: go" | YES if fail |
| Invalid profile warns clearly | `--language=nonexistent --dry-run` | ✅ PASS — "Unknown reusable language profile" | YES if fail |
| Dashboard files all under 50 lines | `wc -l EVIDENCE/*.md` | ✅ PASS — max 40 lines | YES if fail |
| No raw outputs in EVIDENCE/ | Manual inspection | ✅ PASS | YES if fail |
| `PARENT-AGENTS.md` status | `ls PARENT-AGENTS.md` | ✅ N/A — never existed, not needed | NO |
| Contradictory precedence claims | `grep "docs/governance"` all root files | ✅ PASS — none found | YES if fail |

## Known YELLOW (accepted)
- `--validate` on self-repo fails with cp error (expected — cannot install into itself)
- Upgrade/migrate not smoke-tested against a clean external target

## Acceptance Criteria
- [x] All critical smoke checks PASS
- [x] No docs-only claims on tested functionality
- [x] YELLOW limitations are explicit and owned
