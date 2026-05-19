# How This Works

`Query/Projections` owns typed result mapping.

- `Projection.php` defines the public projection contract.
- `ResultMapper.php` maps flat rows into PHP objects.
- `TypedResult.php` provides convenience wrappers for one row or many rows.
- `ProjectionBuilder.php` allows explicit field mapping when inference is not enough.
- `TypeGuesser.php` provides low-cost inference hooks for projection tooling.
- `Column.php` stores projection metadata on DTO properties.

This keeps result typing separate from SQL building and from ORM entity hydration.

