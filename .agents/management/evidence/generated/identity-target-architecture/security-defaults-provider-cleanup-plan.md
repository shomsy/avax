# Security Defaults Provider Cleanup Plan

Date: 2026-05-21
Branch: architecture/identity-target-architecture

## Scope

- Move `Identity/Security` default registration out of `Configuration/Builders`.
- Update `SecurityServiceProvider` to delegate to `Configuration/Providers`.
- Preserve all existing registrations and runtime behavior.

## Reason

`RegisterSecurityDefaults` registers container defaults. It does not build a cohesive
runtime product, so `Configuration/Providers` is the correct owner under the current
fluent class API and assembly governance.

## Out Of Scope

- No Security public API changes.
- No Security runtime behavior changes.
- No broader Security component redesign.
