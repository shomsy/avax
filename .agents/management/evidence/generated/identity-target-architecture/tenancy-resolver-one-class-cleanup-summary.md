# Tenancy Resolver One-Class Cleanup Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/Tenancy/System/Capabilities/Resolution/TenantResolver.php`
- `components/Identity/Tenancy/System/Capabilities/Resolution/DomainResolver.php`
- `components/Identity/Tenancy/System/Capabilities/Resolution/HeaderResolver.php`
- `components/Identity/Tenancy/System/Capabilities/Resolution/PathResolver.php`

## Implementation

Removed duplicate `DomainResolver`, `HeaderResolver`, and `PathResolver` class definitions from `TenantResolver.php`.

The dedicated resolver files are now canonical and all use `Psr\Http\Message\RequestInterface`, matching `TenantResolver`.

## Boundary Result

Each resolver file contains one class. `TenantResolver` now orchestrates the dedicated resolver classes instead of embedding duplicates.
