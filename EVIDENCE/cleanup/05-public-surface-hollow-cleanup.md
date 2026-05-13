# Stage D PublicSurface and Hollow Shell Cleanup

Date: 2026-05-13
Status: YELLOW_PARTIAL

## Checked

- `php tooling/refactor/check-public-surface.php`: PASS.
- `php tooling/components/check-hollow-public-surfaces.php`: PASS, 229 public surface files checked.
- Router middleware callable check simplified where PHPStan proved it was always callable after string resolution.
- Controller resolver now resolves controller instances through `ResolveCallable::resolveInstance()`.

## Not Fully Completed

- Full manual review of every public method returning `new self`, `0`, `null`, TODO-only methods, and request-scoped
  state was not completed.
- No final GREEN claim is made for Stage D.

Ledger: coverage gap recorded in `how-to-coverage-gaps.md`.
