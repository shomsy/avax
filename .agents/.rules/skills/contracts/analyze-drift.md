# Skill: Analyze Drift
**ID**: SKILL-ANALYZE-DRIFT
**Version**: 1.0.0

## Contract
- **Input**: `profile_name` (string)
- **Execution**: Compares local files against the canonical profile in .rules.
- **Output**: `drift_score` (0-1), `diff` (patch)
- **Evidence**: Writes result to `.agents/management/evidence/truth/drift-analysis.json`
