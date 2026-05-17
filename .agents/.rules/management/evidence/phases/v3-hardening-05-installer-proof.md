# V3 Hardening — Phase 5: Installer Productization Proof (FINAL)

**Date**: 2026-05-16
**Status**: COMPLETE — DEBT-INSTALLER-01 RESOLVED

## Full Validation Matrix

| Scenario | Command | Expected | Actual | Pass |
|---|---|---|---|---|
| Clean install with profile + adapter | `./install-os.sh /tmp/target --language=go --platform=claude` | AGENTS.md, CLAUDE.md, EVIDENCE/, go profile selected | ✅ All created, profile confirmed | YES |
| Validate installed target | `./install-os.sh /tmp/target --validate` | "OS Validation Passed." exit 0 | ✅ Exit 0, message confirmed | YES |
| Upgrade — re-copy .rules engine | `./install-os.sh /tmp/target --upgrade` | .rules re-populated, exit 0 | ✅ Exit 0, .rules repopulated | YES |
| Migrate — archive legacy files | `./install-os.sh /tmp/target --migrate` | TODO.md/BUGS.md archived to `archive/legacy/` | ✅ Both archived, exit 0 | YES |
| Invalid profile warns clearly | `--language=nonexistent --dry-run` | Warning printed, no crash | ✅ "Unknown reusable language profile" | YES |
| Dry-run changes nothing | `--dry-run` | "DRY RUN MODE" printed, no files modified | ✅ Exit 0, no modifications | YES |

## Bug Fixed During This Pass
- `install-os.sh` line 272: backticks in echo caused shell to try to execute `.rules` as a command
- Fixed: removed backticks from echo string
- Re-tested: clean exit 0 ✅

## Raw Evidence
`.agents/management/evidence/raw/v3-hardening/installer-full-smoke.txt`

## Debt Resolution
- **DEBT-INSTALLER-01**: RESOLVED
- All 6 installer scenarios proven with real execution
- No claims remain without smoke proof

## Acceptance Criteria
- [x] Core install works
- [x] Profile selection works (valid and invalid)
- [x] Dry-run changes nothing
- [x] Validate detects installation
- [x] Upgrade re-copies .rules engine
- [x] Migrate archives legacy structures
- [x] All docs match real installer behavior
