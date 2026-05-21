# Root Identity Runtime Import Cleanup Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/System/Configuration/Builders/IdentityRuntime.php`

## Implementation

Root Identity runtime assembly now imports Access/Tenancy requirement collaborators and
constructs them by short class name instead of inline fully-qualified `new \Avax\...`
expressions.

## Boundary Result

- Runtime behavior changed: NO.
- Public API changed: NO.
- Assembly ownership changed: NO.
- Readability and semantic import discipline improved.
