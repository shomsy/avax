# Advanced Queries

Advanced SQL features live under `Query/Advanced`.

Sub-slices:

- `CTE` for common table expressions and recursive CTE helpers
- `WindowFunctions` for analytic expressions and partition builders
- `BulkOperations` for chunked insert/update/upsert SQL generation
- `Upsert` for conflict-aware write statements

These helpers intentionally extend the query capability without adding generic buckets or leaking advanced SQL details
into the default builder path.
