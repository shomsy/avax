# How This Works

`Query/Advanced` groups opt-in SQL features that extend the core builder without polluting the default query path.

- `CTE/` owns common table expression construction.
- `WindowFunctions/` owns analytic/window expression builders.
- `BulkOperations/` owns batch SQL generation for high-volume writes.
- `Upsert/` owns conflict-handling SQL builders.

The core builder remains readable; advanced SQL features stay local to one ownership boundary.
