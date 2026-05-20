# TODO-006 Slice B Final Decision

## Decision

MERGE_READY_WITH_YELLOW.

## Why

Slice B moved BootDsl public entrypoint engine assembly into Configuration without changing the public DSL contract.

Validation proves:

- focused PHPUnit is GREEN
- changed-file PHPStan is GREEN
- public-surface gate is GREEN
- namespace drift, governance index, root evidence hygiene, and diff hygiene are GREEN
- direct-instantiation no longer reports `framework/System/PublicSurface/BootDsl.php`

## Why TODO-006 Is Not Closed

Remaining TODO-006 public-surface findings:

- `framework/System/PublicSurface/App.php:199`
- `framework/System/PublicSurface/App.php:203`
- `framework/System/PublicSurface/App.php:282`
- `framework/System/PublicSurface/App.php:316`

## Next Allowed Action

Commit Slice B, merge to `main` after review, run post-merge validation, then continue TODO-006 with the remaining `App.php` slice if ownership remains clear.
