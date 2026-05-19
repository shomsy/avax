# FailureBoundary Ownership Decision

Date: 2026-05-12

## Current Owner

`framework/System/Capabilities/FailureBoundary/`

39 PHP files following canonical AvaX component shape:

```
FailureBoundary/
  PublicSurface/    — Static facade
  Flows/            — RunProtectedAction, ResolveFailurePolicy
  Capabilities/     — 12 capabilities (pipeline, classification, retry, etc.)
  Configuration/    — Config DTO + builder
  Foundation/       — Models, enums, attributes, cache
  Integration/      — HTTP middleware
```

## Why This Pass Does Not Move It

1. The component is already in `framework/System/Capabilities/` which is the correct location for framework-level
   behavior.
2. The component is fully implemented, tested (52 tests), PHPStan clean, and committed.
3. Moving it would break tests, gates, and documentation without functional benefit.
4. The component follows the canonical AvaX shape and naming rules.

## What Must Not Be Duplicated

- No second `ErrorHandling/` directory
- No duplicate retry engine in other components
- No duplicate dead-letter queue implementation
- No duplicate error reporting/logger implementation
- No local try/catch that duplicates the boundary's decision routing

## Historical Context

`framework/System/Capabilities/ErrorHandling/` previously contained `ClassifyApplicationException.php` and
`RenderApplicationError.php`. These were migrated into the FailureBoundary component during implementation (commit
`a2c7c7b18`). The ErrorHandling directory no longer exists.

## Future Convergence

If a broader "Error Handling" domain is needed later (encompassing validation errors, domain exceptions,
application-level error types), it should be a higher-level capability that **contains** FailureBoundary as a
sub-capability, not a parallel implementation. FailureBoundary owns the mechanism (attributes → compiled policy →
pipeline → decision). A broader ErrorHandling domain would own the taxonomy and classification of errors across the
system.
