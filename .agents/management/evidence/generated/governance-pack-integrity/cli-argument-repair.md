# CLI Argument Repair Evidence

## Issue
The `--help` flag previously generated packs instead of printing help and exiting.

## Fix Applied
Modified `tooling/governance/generate-review-packs.php` in the `parse_cli_args` function:
- Removed positional argument handling
- Added early exit for `--help`/`-h` before processing any other arguments
- Enforced that `--purpose` and `--name` must be identical
- Added validation for lowercase letters, numbers, hyphens only
- Unknown options now fail with non-zero exit

## Verification Steps

### 1. Help Command
```bash
php tooling/governance/generate-review-packs.php --help
```
Output: Usage printed, exit code 0, no `_pack/*---help-review` folder created.

### 2. Unknown Option
```bash
php tooling/governance/generate-review-packs.php --invalid 2>&1; echo "Exit code: $?"
```
Output: "Unknown option: --invalid", exit code 1.

### 3. Malformed Value
```bash
php tooling/governance/generate-review-packs.php --purpose=Invalid 2>&1; echo "Exit code: $?"
```
Output: "Invalid purpose. Use lowercase letters, numbers, and hyphens only.", exit code 1.

### 4. Purpose/Name Mismatch
```bash
php tooling/governance/generate-review-packs.php --purpose=test --name=different 2>&1; echo "Exit code: $?"
```
Output: "--purpose and --name must be identical for a single immutable run id.", exit code 1.

### 5. Valid Generation
```bash
php tooling/governance/generate-review-packs.php --purpose=cli-test --name=cli-test 2>&1 | tail -5
```
Output shows successful generation of 6 packs with validation OK.

### 6. No Help Pack Created
```bash
find _pack -maxdepth 1 -type d -name '*-help-review' 2>/dev/null
```
Output: (empty) - no help review packs created.

## Conclusion
CLI argument parsing now correctly handles `--help`, `--purpose`, and `--name` arguments per specification.