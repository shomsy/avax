# Skill: Verify Checksum
**ID**: SKILL-VERIFY-CHECKSUM
**Version**: 1.0.0

## Contract
- **Input**: `file_path` (string), `expected_hash` (string)
- **Execution**: Computes SHA256 of the file and compares it to expected.
- **Output**: `status` (MATCH|MISMATCH|ERROR), `actual_hash` (string)
- **Evidence**: Writes result to `.agents/management/evidence/validation/checksums.json`

## Example Usage
```bash
./.agents/skills/bin/verify-checksum.sh my-file.txt "a1b2c3d4..."
```
