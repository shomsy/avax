# AvaX Identity Enterprise Reference Component

This package is a greenfield reference implementation for the AvaX Identity component.
It is designed as a target architecture package, not as a blind drop-in replacement for the current repository.

Status: `YELLOW_REFERENCE_IMPLEMENTATION`

Reason: all PHP files are syntax-checked in the generation environment, but the package still needs to be copied into the real AvaX worktree and validated with AvaX governance, PHPUnit, PHPStan, security checks, public-surface checks, intrusive-coupling checks, and runtime-safety gates.

## Intent

The component follows the AvaX architecture direction discussed for Identity:

1. `AuthenticationGraph`
2. `AuthorizationGraph`
3. `SessionGraph`
4. `TokenGraph`
5. `ExternalIdentityGraph`
6. `TenancyGraph`
7. `IdentityRuntimeGraph`

Public API stays thin. Runtime dependencies are assembled explicitly in `Configuration/Graphs`. Runtime code does not use service locators, global helpers, static singletons, hidden construction of dependencies, or framework-specific runtime APIs.

## Shape

```text
components/Identity/
  PublicSurface/
  Flows/
  Capabilities/
  Configuration/
    Graphs/
  Foundation/
```

Folder says flow/capability. Class says responsibility. Method says exact action.

## Non-goals

This package does not provide a full AvaX container integration because the target container API must be taken from the real repository. The integration point is `Configuration/CreateIdentityRuntimeGraph.php`, which can be called from the real AvaX service provider.

## Validation after import

Run in the real AvaX worktree:

```bash
composer dump-autoload -o
php -l $(find components/Identity -name '*.php' | sort)
vendor/bin/phpstan analyse components/Identity tests/Unit/Components/Identity --memory-limit=1G --error-format=raw --no-progress
vendor/bin/phpunit --no-coverage --filter Identity
php tooling/refactor/check-public-surface.php
php tooling/governance/CheckIntrusiveCoupling.php
php tooling/components/check-component-static-state-safety.php
php tooling/refactor/check-container-service-locator.php
php tooling/refactor/check-runtime-composition-leaks.php
```
