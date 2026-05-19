# How This Works

`Advanced/WindowFunctions` owns analytic SQL expression building.

- `WindowBuilder.php` composes the final `... OVER (...)` fragment.
- `WindowFunction.php` names supported analytic functions.
- `PartitionBuilder.php` isolates partition SQL generation.
- `RowNumber.php`, `Rank.php`, and `LagLead.php` provide narrow entry points for common patterns.

This keeps analytic SQL localized instead of scattering raw strings across the builder layer.

