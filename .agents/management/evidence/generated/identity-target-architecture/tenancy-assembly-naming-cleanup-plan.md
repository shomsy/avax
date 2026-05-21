# Tenancy Assembly Naming Cleanup Plan

Date: 2026-05-21
Branch: architecture/identity-target-architecture

## Scope

- Rename `Configuration/Assembly/TenancyGraph` to `Configuration/Assembly/Tenancy`.
- Update Identity runtime, Tenancy service provider, and Tenancy tests.
- Preserve runtime behavior.

## Compatibility

Internal configuration-class rename. Out-of-repo direct use of `TenancyGraph` is
compatibility-yellow.
