# Security Defaults Provider Cleanup Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/Security/System/Configuration/Providers/RegisterSecurityDefaults.php`
- `components/Identity/Security/System/Configuration/SecurityServiceProvider.php`
- old `Configuration/Builders/RegisterSecurityDefaults.php` deleted by move

## Implementation

`RegisterSecurityDefaults` moved from `Configuration/Builders` to
`Configuration/Providers`, and `SecurityServiceProvider` now imports and delegates to the
provider registrar directly.

## Boundary Result

- Builders folder no longer contains registration classes.
- Security registration behavior is preserved.
- Public API changed: NO.
