# Auth Dependency Registrar Provider Cleanup Plan

Date: 2026-05-21
Branch: architecture/identity-target-architecture

## Source Of Truth Decision

Previous Auth Builder detox evidence left one accepted-yellow item:
`RegisterAuthDependencies` remained in `Configuration/Builders` even though it is a
container dependency registrar.

Current source grep shows no active production or test references to the old FQCN.

## Scope

- Move `RegisterAuthDependencies` from `Configuration/Builders` to `Configuration/Providers`.
- Update its namespace.
- Do not redesign `AuthBuilder`.
- Do not change Auth runtime behavior.

## Compatibility

`RegisterAuthDependencies` is a configuration adapter, not a PublicSurface class. Because
active tracked code does not reference the old FQCN, this is a bounded internal namespace
cleanup. Out-of-repository callers of this internal adapter are compatibility-yellow.

## Expected Validation

- `git diff --check`
- static grep for old namespace/FQCN in active code
- PHP syntax/PHPUnit/PHPStan if environment permits
