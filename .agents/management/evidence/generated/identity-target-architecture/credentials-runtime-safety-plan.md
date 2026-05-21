# Credentials Runtime Safety Plan

Date: 2026-05-21

## Slice Scope

- Remove static mutable credential-store state from `Credentials` PublicSurface.
- Introduce `CredentialsRuntime` as the executable owner of store/read/forget and sub-surface access.
- Assemble root `Identity::credentials()` with an explicit in-memory credential store.
- Update characterization tests to prove instance-scoped runtime isolation.

## Out of Scope

- MFA runtime redesign.
- Passkey runtime redesign.
- Passwords DSL expansion.
- Provider-wide credentials assembly.

## Compatibility

The old static `Credentials::store()` / `read()` / `forget()` facade path is not preserved in this slice because preserving it would keep static mutable runtime state. The target DSL is instance-based through `Identity::credentials()`.
