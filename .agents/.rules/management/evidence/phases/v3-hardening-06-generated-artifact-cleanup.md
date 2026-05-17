# V3 Hardening — Phase 6: Generated Artifact Cleanup

**Date**: 2026-05-16

## Artifact Audit

| Path | Generated? | Tracked? | Decision | Action |
|---|---|---|---|---|
| `test-install-dry/` | YES — created by `./install-os.sh` test run | Untracked | Delete or ignore | Added to `.gitignore` |
| `create_schemas.py` | YES — temp script from previous pass | Untracked | Ignore | Added to `.gitignore` |
| `update_profiles.py` | YES — temp script from previous pass | Untracked | Ignore | Added to `.gitignore` |
| `polish_profiles.py` | YES — temp script from previous pass | Untracked | Ignore | Added to `.gitignore` |
| `agent-harness.txt` | YES — generated snapshot | Was staged as D (deleted) | Already removed | Staged deletion OK |
| `agent-harness.part-*.txt` | YES — generated snapshot parts | Was staged as D | Already removed | Staged deletion OK |
| `.agents/.rules/` | YES — written by installer | Untracked (installed copy) | Ignore pattern | Should be in .gitignore |

## .gitignore Changes
Added:
```
test-install-dry/
create_schemas.py
update_profiles.py
polish_profiles.py
agent-harness.txt
agent-harness.part-*.txt
```

## Acceptance Criteria
- [x] No accidental generated output remains unprotected
- [x] Temp scripts are gitignored
- [x] Dry-run output is gitignored
- [x] Generated snapshots are gitignored
