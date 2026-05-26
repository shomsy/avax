# Mode Honesty Report

Task: Engineering Canon Convergence checker mode honesty.

## Policy

Unsupported modes must fail explicitly. A checker must not accept `--mode=baseline` or `--mode=full` and return GREEN unless that mode is implemented.

## Checker Mode Status

| Checker | changed | baseline | full |
|---|---|---|---|
| `check-scenario-input.php` | implemented | explicit non-zero not implemented | explicit non-zero not implemented |
| `check-coupling-decisions.php` | implemented | explicit non-zero not implemented | explicit non-zero not implemented |
| `check-architecture-fitness-functions.php` | implemented | explicit non-zero not implemented | explicit non-zero not implemented |
| `check-antipatterns.php` | implemented | explicit non-zero not implemented | explicit non-zero not implemented |

## Expected Baseline Output

```text
RED: Baseline mode is not implemented yet for this checker.
```

## Expected Full Output

```text
RED: Full mode is not implemented yet for this checker.
```
