# Phase G: PHPStan Type System Closure — Evidence

Date: 2026-05-15
Phase: G (PHPStan Closure)
Status: YELLOW (Baseline refreshed)

## G.1-G.2: Catch-all Ignores Removal

Removed 42 catch-all blocks (`#.*#`) from `phpstan.neon`. These were hiding critical errors in:

- `framework/System/Configuration/BuildApplication/ApplicationBuilder.php`
- `components/Application/Container/System/`
- `components/HTTP/System/`
- `components/Identity/Auth/System/`
- and many others.

## G.3-G.4: Baseline Refresh

Refreshed `phpstan-baseline.neon`.
New baseline contains the honest state of the type system.
Number of ignored errors: [PENDING]

## G.5: type_perfect Audit

Verified all `type_perfect` settings are enabled:

- `narrow_param: true`
- `narrow_return: true`
- `no_mixed: true`
- `null_over_false: true`
- `no_mixed_property: true`
- `no_mixed_caller: true`

## Remaining Risk

The baseline is extremely large (~23k+ errors).
Removing the catch-all ignores revealed the true scale of technical debt.
A dedicated "Type Fix" sprint is recommended to start chipping away at this baseline, prioritizing `framework/` and
`PublicSurface` layers.
