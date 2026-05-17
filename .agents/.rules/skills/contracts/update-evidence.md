# Skill: Update Evidence
**ID**: SKILL-UPDATE-EVIDENCE
**Version**: 1.0.0

## Contract
- **Input**: `type` (phase|review|validation), `content` (json_string)
- **Execution**: Validates JSON against schema and writes to the correct evidence lane.
- **Output**: `file_path` (string)
- **Evidence**: The file itself is the evidence.

## Example Usage
```bash
./.agents/skills/bin/update-evidence.py --type phase --content '{"name": "test"}'
```
