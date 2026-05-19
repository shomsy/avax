# How This Works

`Advanced/CTE` owns explicit construction of common table expressions.

- `CTEBuilder.php` aggregates named CTE definitions.
- `RecursiveCTE.php` models a recursive seed plus recursive branch.
- `CTEUnion.php` models the union boundary between CTE branches.

The output of this slice is dialect-ready SQL fragments, not live execution.

