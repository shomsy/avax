# Failure Semantics

Generated: 2026-05-26

## Failure Points

| Failure | Behavior |
|---|---|
| Missing baseline file | Baseline mode returns RED. |
| Invalid baseline JSON | Baseline mode returns RED. |
| New finding not in baseline | Baseline mode returns RED. |
| Baseline entry disappears without baseline update | Baseline mode returns RED as stale baseline entry. |
| Changed-scope BLOCKER/HIGH in self-explaining architecture | Changed mode returns RED. |
| Any changed-scope shallow-test finding | Changed mode returns RED. |
| PHPStan finding in changed configured analysis scope | Changed mode returns RED. |
| Git unavailable inside PHP runtime | Helper falls back to `.git` metadata instead of silently scanning zero files. |

## Safety Decision

The gates fail closed for missing/invalid baselines and new findings. Baseline mode cannot produce GREEN unless current findings exactly match the recorded baseline.

## Known Limitation

The PHPStan changed-mode wrapper enforces files under the configured PHPStan analysis roots: `framework/`, `components/`, and `tests/`. Governance tooling scripts are syntax-checked and functionally exercised by their own commands in this pass.
