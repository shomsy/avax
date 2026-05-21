# Tenancy Resolver One-Class Cleanup Plan

Date: 2026-05-21

## Slice Scope

- Ensure `TenantResolver.php` contains only `TenantResolver`.
- Use existing `DomainResolver.php`, `HeaderResolver.php`, and `PathResolver.php` as the canonical one-class-per-file resolver classes.
- Align resolver method request type hints with `TenantResolver`.

## Out of Scope

- Tenant resolution policy redesign.
- Resolver instance runtime refactor.
- Tenant resolver test expansion beyond static/source validation.
