# Auth SCIM Assembly Import Cleanup Plan

Date: 2026-05-21
Branch: architecture/identity-target-architecture

## Scope

- Remove fully-qualified SCIM runtime construction from `AssembleAuthIdentityGraph`.
- Add explicit imports for the five SCIM collaborators.
- Preserve Auth assembly behavior.

## Out Of Scope

- No AuthBuilder split.
- No SCIM runtime redesign.
- No public API changes.
