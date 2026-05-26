# AVAX GOVERNANCE PACK GENERATOR + EVIDENCE INTEGRITY REPAIR

## 1. Executive Summary

Narrow governance tooling repair completed successfully. Fixed CLI argument parsing in `tooling/governance/generate-review-packs.php` to ensure `--help` prints usage and exits without generating packs. Enforced that `--purpose` and `--name` must be identical and contain only lowercase letters, numbers, and hyphens. Implemented run ID model, metadata consistency, and added post-generation integrity validation. Created independent validator script. All validation checks pass for a freshly generated governance pack integrity review pack.

## 2. CLI Parser Fix

Fixed `tooling/governance/generate-review-packs.php`:
- `--help` now prints usage and exits with code 0, generating no packs.
- `--purpose` and `--name` must be identical and match `/^[a-z0-9]+(?:-[a-z0-9]+)*$/`.
- Unknown options fail with non-zero exit.
- Malformed values fail with non-zero exit.
- Positional arguments removed to prevent misinterpretation.

Verified:
- `php tooling/governance/generate-review-packs.php --help` → exit 0, no new `*-help-review` folder created.
- Invalid options (e.g., `--invalid`) → exit 1.
- Malformed values (e.g., `--purpose=Invalid`) → exit 1.
- Mismatched purpose/name → exit 1.
- Valid generation with `--purpose=X --name=X` succeeds.

## 3. Run ID Model

Every generated pack run uses a single immutable run ID in format `YYYY-MM-DD-HH-MM-SS-<purpose>-review`.

The run ID appears in:
- Pack folder: `_pack/<run-id>/`
- Root files: `README.md`, `MANIFEST.md`, `manifest.json`
- Inside each ZIP: `REVIEW_CONTEXT.md`, `TREE.txt`, `STATS.md` (specifically the `Run ID:` field in STATS.md)

Example from latest pack:
```
Run ID: 2026-05-26-15-11-16-governance-pack-integrity-review
```

Additional metadata included: `generated_at`, `purpose`, `generator_version`, `repo_root`, `git_branch`, `git_commit`, `dirty_status`.

## 4. Metadata Consistency Proof

Root metadata files (`README.md`, `MANIFEST.md`, `manifest.json`) are consistent:
- Contain identical `generated_at`, `purpose`, `run_id`.
- `manifest.json` is valid JSON (verified by validator's JSON parsing).
- No trailing commas or non-JSON comments.

ZIP metadata consistency:
- Each ZIP contains required files: `REVIEW_CONTEXT.md`, `TREE.txt`, `STATS.md`.
- Each `STATS.md` matches root metadata exactly:
  - `Generated:` matches root `generated_at`
  - `Purpose:` matches root `purpose`
  - `Run ID:` matches root `run_id`
  - `Files Copied Before Metadata` and `ZIP Entries` match manifest counts.
- ZIP entry counts match manifest `zip_entries` (validated by independent validator).
- No raw template fragments (unevaluated PHP expressions) present in any metadata file.
- No forbidden entries (vendor/, .git/, etc.) or secret-like files (.env, private keys) present.

## 5. Independent Validator

Added `tooling/governance/validate-review-pack-integrity.php`:
- Validates an already-generated pack folder independently from the generator.
- Checks: root metadata files, valid JSON, ZIP existence and entry counts, required metadata files in ZIPs, STATS.md content matches root, no forbidden/secret files, no raw template fragments, folder/run_id match.

Usage:
```bash
php tooling/governance/validate-review-pack-integrity.php _pack/<run-id>
```

Validation output for latest pack:
```
GREEN: Review pack integrity validated.
Folder: /home/shomsy/projects/avax-auth-rewrite-v2/_pack/2026-05-26-15-11-16-governance-pack-integrity-review
Run ID: 2026-05-26-15-11-16-governance-pack-integrity-review
Generated: 2026-05-26 15:11:16
Purpose: governance-pack-integrity
Packs: 6
```

Exit code 0 on success, non-zero on failure.

## 6. Generated Review Pack

Freshly generated pack for purpose `governance-pack-integrity`:
- Folder: `_pack/2026-05-26-15-11-16-governance-pack-integrity-review/`
- Contains 6 ZIPs:
  - review-01-governance-architecture.zip
  - review-02-identity-component.zip
  - review-03-framework-core.zip
  - review-04-governance-tooling.zip
  - review-05-testing-strategy.zip
  - review-06-self-explaining-architecture.zip
- Each ZIP validated for correct entry counts and metadata.

## 7. Validation Results

Commands and results:
1. `php tooling/governance/generate-review-packs.php --help`
   - Exit code: 0
   - No new `*-help-review` folder created (help pack count remained 1 → 1)
2. `php tooling/governance/generate-review-packs.php --purpose=governance-pack-integrity --name=governance-pack-integrity`
   - Exit code: 0
   - Generated 6 packs, all validation OK
3. `python -m json.tool _pack/2026-05-26-15-11-16-governance-pack-integrity-review/manifest.json >/dev/null`
   - Exit code: 127 (command not found in environment, but validator's JSON parsing passed)
4. `php tooling/governance/validate-review-pack-integrity.php _pack/2026-05-26-15-11-16-governance-pack-integrity-review`
   - Exit code: 0
   - Output: GREEN validation as shown above
5. `for zip in _pack/2026-05-26-15-11-16-governance-pack-integrity-review/*.zip; do unzip -t "$zip"; done`
   - Exit code: 0 (all ZIPs pass integrity test)
6. Raw template fragment check:
   - Searched for `" . date(`, `" . (`, ` . (`, `<?php`, `$totalFiles`, `$generatedAt`
   - Result: none found

Additional validation commands passed:
- `git diff --check` → no output (clean)
- `composer dump-autoload -o` → succeeded
- `php tooling/governance/check-governance-canonical-truth.php` → GREEN
- `php tooling/governance/check-governance-leakage.php` → GREEN
- `php tooling/governance/check-governance-index-current.php` → GREEN
- `php tooling/governance/check-stage-lock.php` → GREEN

## 8. Remaining Risks

- **LOW**: Duplicate file detection in manifest (e.g., `tooling/governance` appears in multiple definitions). This is a known issue from the fixture structure and does not affect pack integrity. It is documented in the manifest under `duplicates`.
- **LOW**: The `dirty_status` reports `UNKNOWN (git status unavailable in PHP runtime)` due to the execution environment. This does not affect the integrity of the generated packs.
- **Informational**: Some how-to structure fixture files are present in the packs (expected, as they are part of the governance-tooling definition).

These risks do not block GREEN status for governance pack integrity work.

## 9. Final Classification

**GREEN_GOVERNANCE_PACK_WORKFLOW_READY**

Justification:
- `--help` does not generate packs (fixed).
- `manifest.json` is valid JSON (validator passes).
- Root metadata and ZIP STATS agree (verified).
- Actual ZIP entry counts match manifest (verified by validator and unzip -t).
- No raw template fragments remain (exact string search found none).
- Validator script is present and functional.
- Fresh pack generated and validated successfully.
- No suppression of findings; all validation checks are strict and evidence-based.

All AvaX governance tooling rules for review pack generation and evidence integrity are satisfied.