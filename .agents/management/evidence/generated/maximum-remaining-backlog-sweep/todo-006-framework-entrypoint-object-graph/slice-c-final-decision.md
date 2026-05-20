# TODO-006 Slice C Final Decision

## Decision

MERGE_READY_WITH_YELLOW.

## Why

Slice C removes `App.php` from the direct-instantiation findings while preserving route registration and request handling behavior.

Validation proves:

- focused PHPUnit is GREEN
- changed-file PHPStan is GREEN
- public-surface gate is GREEN
- namespace drift, governance index, root evidence hygiene, and diff hygiene are GREEN

## TODO-006 State After Slice C

TODO-006 remains PARTIAL until `Avax.php` public entrypoint assembly is remediated or explicitly accepted.

## Next Allowed Action

Commit Slice C, merge to `main`, run post-merge validation, then evaluate `Avax.php` as the next TODO-006 slice.
