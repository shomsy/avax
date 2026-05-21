# Auth Provider Registration Cleanup Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/Auth/System/Configuration/AuthServiceProvider.php`
- `components/Identity/Auth/System/Configuration/Providers/RegisterAuthDefaults.php`

## Implementation

`RegisterAuthDefaults` moved from `Configuration/Builders` to `Configuration/Providers`, and its namespace now matches its registration ownership.

`AuthServiceProvider` imports and delegates to `Providers\RegisterAuthDefaults`.

## Boundary Result

The `Builders` folder no longer contains this default-registration class. This reduces builder naming drift while preserving the existing provider registration behavior.
