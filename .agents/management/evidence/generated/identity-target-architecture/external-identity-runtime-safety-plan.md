# ExternalIdentity Runtime Safety Plan

Date: 2026-05-21

## Slice Scope

- Remove static mutable link-store state from `ExternalIdentity` PublicSurface.
- Introduce `ExternalIdentityRuntime` as the executable owner of link/resolve behavior.
- Assemble root `Identity::externalIdentity()` with an explicit store.
- Update characterization tests from static reflection reset to instance-scoped runtime behavior.

## Out of Scope

- OAuth/OIDC/Federation capability redesign.
- Provider-wide external identity assembly.
- Auth external identity graph changes.

## Compatibility

The old static `ExternalIdentity::link()` / `resolve()` facade path is not preserved in this slice because keeping it would keep static mutable runtime state. This is documented as COMPATIBILITY_YELLOW; the target DSL is `Identity::externalIdentity()->link(...)`.
