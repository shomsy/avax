# Auth Provider Registration Cleanup Plan

Date: 2026-05-21

## Slice Scope

Move `RegisterAuthDefaults` out of `Configuration/Builders` because it registers container defaults and does not build a cohesive product.

## Ownership Decision

- `Configuration/Providers/RegisterAuthDefaults` owns default Auth container registrations.
- `Configuration/Builders/AuthBuilder` remains the fluent typed auth assembly object.
- `AuthServiceProvider` delegates default registration to the provider registrar.

## Out of Scope

- No Auth runtime behavior changes.
- No renaming of `RegisterAuthDependencies`; that adapter remains a later cleanup item.
- No broad provider restructuring.
