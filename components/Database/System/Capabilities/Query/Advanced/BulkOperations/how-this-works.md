# How This Works

`BulkOperations` turns large write payloads into deterministic SQL batches.

- `BulkInserter.php` keeps the existing lightweight insert path.
- `BatchInsert.php` builds chunked insert statements and bindings.
- `BatchUpdate.php` builds per-row update statements keyed by one identifier.
- `BulkUpsert.php` delegates chunked conflict-aware writes to the upsert slice.

This slice is intentionally SQL-generation focused. Execution stays outside this folder.
