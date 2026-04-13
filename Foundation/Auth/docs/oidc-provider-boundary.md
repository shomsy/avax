# OIDC Provider Boundary

Full OIDC provider behavior is larger than this package's current kernel
ownership and should remain a dedicated package or adapter surface.

## Why

- discovery and JWKS require explicit signing-key publication strategy
- ID token issuance needs RP-facing claims, nonce handling, and conformance
  discipline
- userinfo and RP metadata posture belong to a provider surface, not to the
  core auth kernel

## Package Posture

- keep OAuth client and token issuance in the kernel
- expose provider behavior through a separate OIDC package when the product
  truly needs it
- use `MultiKeyHmacTokenCodec` or stronger asymmetric codecs for rollover-aware
  verification during migrations

## Required Provider Deliverables

- discovery document
- JWKS surface with overlap during rollover
- ID token issuance
- userinfo
- RP metadata review and conformance matrix
