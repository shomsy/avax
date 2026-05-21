# External Identity Assembly Naming Cleanup Plan

Date: 2026-05-21
Branch: architecture/identity-target-architecture

## Scope

- Rename `Configuration/Assembly/ExternalIdentityGraph` to
  `Configuration/Assembly/ExternalIdentity`.
- Update root Identity runtime assembly call site.
- Preserve behavior.

## Compatibility

Internal configuration-class rename. Out-of-repo direct use of `ExternalIdentityGraph` is
compatibility-yellow.
