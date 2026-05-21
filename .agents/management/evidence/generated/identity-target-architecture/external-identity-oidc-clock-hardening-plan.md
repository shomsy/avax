# External Identity OIDC Clock Hardening Plan

Date: 2026-05-21

## Scope

- Remove direct wall-clock reads from OIDC request-object storage and provider token issue/resolve logic.
- Add a tiny ExternalIdentity clock primitive.
- Update default OIDC request-object store assembly.
- Add focused characterization source for deterministic OIDC request object and ID token expiry behavior.

## Non-Scope

- Do not redesign OAuth/OIDC flows.
- Do not alter AuthBuilder.
- Do not replace OpenSSL/JWT signing logic.
- Do not clean unrelated Auth raw `time()` usage in this slice.

## Design Decision

ExternalIdentity gets its own `System/Foundation/Time` primitive to avoid coupling OIDC runtime behavior to Auth foundation time classes.

`SystemClock` is the only ExternalIdentity clock file expected to read wall-clock time.
