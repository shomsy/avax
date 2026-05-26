# Validation Summary

Task: Engineering Canon Convergence content depth and checker hardening.

Date: 2026-05-26

Branch: governance/engineering-canon-convergence

Commit: 8b385baf4a8f983a6b2281c936e7578105674a4c

## Command Table

| Command | Exit Code | Result | Important Output |
|---|---:|---|---|
| `pwd` | 0 | PASS | `/home/shomsy/projects/avax-auth-rewrite-v2` |
| `git branch --show-current` | 0 | PASS | `governance/engineering-canon-convergence` |
| `git status --short` | 0 | PASS | worktree dirty with governance/SDLC/docs/evidence changes and two old top-level archive artifacts |
| `git diff --check` | 0 | PASS | no output |
| `/usr/bin/php8.4 -l tooling/sdlc/SdlcRuntime.php` | 0 | PASS | `No syntax errors detected` |
| `/usr/bin/php8.4 -l tooling/sdlc/GitChangedFiles.php` | 0 | PASS | `No syntax errors detected` |
| `/usr/bin/php8.4 -l tooling/governance/check-engineering-canon-traceability.php` | 0 | PASS | `No syntax errors detected` |
| `/usr/bin/php8.4 -l tooling/governance/check-scenario-input.php` | 0 | PASS | `No syntax errors detected` |
| `/usr/bin/php8.4 -l tooling/governance/check-coupling-decisions.php` | 0 | PASS | `No syntax errors detected` |
| `/usr/bin/php8.4 -l tooling/governance/check-architecture-fitness-functions.php` | 0 | PASS | `No syntax errors detected` |
| `/usr/bin/php8.4 -l tooling/governance/check-antipatterns.php` | 0 | PASS | `No syntax errors detected` |
| `/usr/bin/php8.4 -l tooling/sdlc/preflight.php` | 0 | PASS | `No syntax errors detected` |
| `/usr/bin/php8.4 -l tooling/sdlc/validate-changed.php` | 0 | PASS | `No syntax errors detected` |
| `/usr/bin/php8.4 -l tooling/sdlc/validate-governance.php` | 0 | PASS | `No syntax errors detected` |
| `/usr/bin/php8.4 -l tooling/sdlc/validate-agent-task.php` | 0 | PASS | `No syntax errors detected` |
| `/usr/bin/php8.4 -l tooling/governance/create-actual-changes-review-pack.php` | 0 | PASS | `No syntax errors detected` |
| `composer validate --no-check-publish` | 0 | PASS | `./composer.json is valid` |
| `/usr/bin/php8.4 tooling/sdlc/preflight.php` | 0 | PASS | `GREEN: Preflight passed. strict=no`; dirty status is reported as YELLOW |
| `/usr/bin/php8.4 tooling/sdlc/validate-changed.php` | 0 | PASS | `GREEN_CHANGED_SCOPE_READY: Changed-scope SDLC validation passed required commands.` |
| `/usr/bin/php8.4 tooling/sdlc/validate-governance.php` | 0 | PASS | `GREEN_CHANGED_SCOPE_READY: Governance validation passed required commands.` |
| `/usr/bin/php8.4 tooling/sdlc/validate-agent-task.php` | 0 | PASS | `GREEN_SDLC_AUTOMATION_READY: Agent task runner passed.` |
| `/usr/bin/php8.4 tooling/governance/check-engineering-canon-traceability.php` | 0 | PASS | `GREEN: Engineering canon traceability is complete. mode=changed` |
| `/usr/bin/php8.4 tooling/governance/check-scenario-input.php --mode=changed` | 0 | PASS | `production_behavior_changes=0; evidence_files=0` |
| `/usr/bin/php8.4 tooling/governance/check-coupling-decisions.php --mode=changed` | 0 | PASS | `sensitive_changes=0; evidence_files=0` |
| `/usr/bin/php8.4 tooling/governance/check-architecture-fitness-functions.php --mode=changed` | 0 | PASS | `sensitive_changes=37; evidence_files=1` |
| `/usr/bin/php8.4 tooling/governance/check-antipatterns.php --mode=changed` | 0 | PASS | `files_scanned=83; dictionary_entries=11` |
| `/usr/bin/php8.4 tooling/governance/check-scenario-input.php --mode=baseline` | 1 | PASS_FOR_MODE_HONESTY | `RED: Baseline mode is not implemented yet for this checker.` |
| `/usr/bin/php8.4 tooling/governance/check-coupling-decisions.php --mode=baseline` | 1 | PASS_FOR_MODE_HONESTY | `RED: Baseline mode is not implemented yet for this checker.` |
| `/usr/bin/php8.4 tooling/governance/check-architecture-fitness-functions.php --mode=baseline` | 1 | PASS_FOR_MODE_HONESTY | `RED: Baseline mode is not implemented yet for this checker.` |
| `/usr/bin/php8.4 tooling/governance/check-antipatterns.php --mode=baseline` | 1 | PASS_FOR_MODE_HONESTY | `RED: Baseline mode is not implemented yet for this checker.` |
| `/usr/bin/php8.4 tooling/governance/check-scenario-input.php --mode=full` | 1 | PASS_FOR_MODE_HONESTY | `RED: Full mode is not implemented yet for this checker.` |
| `/usr/bin/php8.4 tooling/governance/check-coupling-decisions.php --mode=full` | 1 | PASS_FOR_MODE_HONESTY | `RED: Full mode is not implemented yet for this checker.` |
| `/usr/bin/php8.4 tooling/governance/check-architecture-fitness-functions.php --mode=full` | 1 | PASS_FOR_MODE_HONESTY | `RED: Full mode is not implemented yet for this checker.` |
| `/usr/bin/php8.4 tooling/governance/check-antipatterns.php --mode=full` | 1 | PASS_FOR_MODE_HONESTY | `RED: Full mode is not implemented yet for this checker.` |
| `/usr/bin/php8.4 tooling/governance/check-governance-index-current.php` | 0 | PASS | `GREEN: Governance index is current.` |
| `/usr/bin/php8.4 tooling/governance/check-governance-canonical-truth.php` | 0 | PASS | `GREEN: Governance canonical truth is coherent.` |
| `/usr/bin/php8.4 tooling/governance/check-governance-leakage.php` | 0 | PASS | `Files checked: 36; Files with leaks: 0; Total findings: 0` |
| `/usr/bin/php8.4 tooling/governance/check-stage-lock.php` | 0 | PASS | `Active Stage: REMEDIATION_ACTIVE`; V2/V3/V4 production implementation remains governed by stage lock |
| `/usr/bin/php8.4 tooling/governance/create-actual-changes-review-pack.php --purpose=engineering-canon --timestamp=2026-05-26-23-20-00` | 0 | PASS | `copied_files=81; skipped_files=2; missing_expected=0` |
| `tar -tzf _pack/2026-05-26-23-20-00-engineering-canon-actual-changes-review.tar.gz >/tmp/avax-actual-changes-tar-list-final.txt` | 0 | PASS | tar archive listed successfully |
| `unzip -t _pack/2026-05-26-23-20-00-engineering-canon-actual-changes-review.zip` | 0 | PASS | `No errors detected in compressed data` |
| `unzip -l _pack/2026-05-26-23-20-00-engineering-canon-actual-changes-review.zip >/tmp/avax-actual-changes-zip-list-final.txt` | 0 | PASS | zip archive listed successfully |
| `find _pack/2026-05-26-23-20-00-engineering-canon-actual-changes-review/files -type f \( -name '*engineering-canon-actual-changes-review*.zip' -o -name '*engineering-canon-actual-changes-review*.tar.gz' \) -print` | 0 | PASS | no output |
| `grep -c 'repo=YES; pack=YES' _pack/2026-05-26-23-20-00-engineering-canon-actual-changes-review/validation/expected-engineering-canon-presence.md` | 0 | PASS | `47` |

## Review Pack Status

Actual-changes pack generated:

```text
_pack/2026-05-26-23-20-00-engineering-canon-actual-changes-review/
_pack/2026-05-26-23-20-00-engineering-canon-actual-changes-review.tar.gz
_pack/2026-05-26-23-20-00-engineering-canon-actual-changes-review.zip
```

Normal generated review packs were not regenerated in this pass.

## Final Validation Status

Changed-scope SDLC validation passed.

Governance validation passed.

Agent task runner passed with `/usr/bin/php8.4`.
