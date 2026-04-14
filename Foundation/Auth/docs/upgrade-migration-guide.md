# Upgrade & Migration Guide

Version: 1.0.0
Status: Normative / Local
Scope: `Avax\Auth\` upgrade and namespace migration

This guide is the canonical migration path for the package boundary changes that
separated kernel code from optional integration surfaces.

## Current Rule

- `System/` owns kernel behavior
- `integrations/` owns optional adapters
- container-specific adapters do not move back into the kernel for backward
  compatibility

## Breaking Boundary Change

The old adapter location:

```php
Avax\Auth\System\Configuration\AuthServiceProvider
```

is replaced by:

```php
Avax\Auth\Integrations\AvaxContainer\AuthServiceProvider
```

## Mapping

| Old import | New import | Owner |
|---|---|---|
| `Avax\Auth\System\Configuration\AuthServiceProvider` | `Avax\Auth\Integrations\AvaxContainer\AuthServiceProvider` | optional integration surface |

## Why No BC Shim

A compatibility shim inside `System/` would put a container-specific adapter back
into the kernel lane. That would violate the current architecture contract.

The migration strategy is therefore:

1. keep the kernel boundary clean
2. document the rename explicitly
3. ship an automated migration check
4. treat the namespace move as an intentional major-boundary break

## Upgrade Checklist

1. Replace old `AuthServiceProvider` imports with the integration namespace.
2. Run `php composer.phar migration:check`.
3. Run `php composer.phar test`.
4. Run `php composer.phar analyse`.
5. Run `php composer.phar analyse:strict`.

## Automation

- `tooling/check-migration-path.php`
- `composer migration:check`
- `tests/System/ProductBoundaryTest.php`
