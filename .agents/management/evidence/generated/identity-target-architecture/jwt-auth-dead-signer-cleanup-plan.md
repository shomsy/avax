# JwtAuth Dead Signer Cleanup Plan

Date: 2026-05-21

## Scope

- Remove unused empty `JwtAuth/Verification/JwtSigner.php`.
- Preserve canonical `JwtAuth/Signing/JwtSigner.php`.
- Add focused characterization source asserting the duplicate verification signer is absent.

## Non-Scope

- Do not redesign signing/verification.
- Do not remove other placeholder classes in this slice.

## Design Decision

`Signing/JwtSigner` is the only signer used by JwtAuth assembly and verification.
