# Projections

`Query/Projections` owns typed result mapping for read models and DTO-style query outputs.

Available pieces:

- `Projection` as the public contract
- `ResultMapper` for row-to-object mapping
- `TypedResult` helpers for one row or many rows
- `ProjectionBuilder` for explicit column-to-property maps
- `TypeGuesser` for low-cost inference

This layer is distinct from ORM hydration. Use projections for read models, summaries, and query-specific DTOs; use ORM
hydration for entity lifecycle management.
