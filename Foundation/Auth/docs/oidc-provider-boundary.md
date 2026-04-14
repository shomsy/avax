# OIDC Provider Boundary

The kernel now owns a practical OIDC provider lane. It does not yet claim to be
a full standards-certified OIDC product.

## Kernel-Owned Today

- discovery metadata through `Auth::readOidcProviderMetadata()`
- JWKS publication through `Auth::readOidcJsonWebKeySet()`
- RS256 ID-token issuance during authorization-code exchange when `openid` is
  requested
- nonce enforcement for `openid` authorization requests
- userinfo through `Auth::readOidcUserInfo()`
- OpenSSL-backed asymmetric signing through `OpenSslOidcProvider`
- overlap JWKS publication and legacy token verification through
  `RotatingOidcProvider`
- configurable public or pairwise subject identifiers through the OIDC
  provider policy
- optional `sid` claim in ID tokens for logout correlation
- local front-channel logout, back-channel logout token handling, PAR request
  storage, request-object claim validation, shared-secret client-signed PAR
  request verification for confidential clients, and JARM response signing
  through the kernel-owned OIDC flows
- framework-neutral HTTP publication through
  `integrations/http/Oidc/ServeOidcHttpSurface.php`

## Still Outside This Package

- OIDC-standard dynamic client registration endpoint
- asymmetric/public-client JAR request-object key distribution and signature verification
- external conformance certification

## Adjacent Control-Plane Support

- tenant-owned OAuth client registration, update, disable, and secret rotation
  now exist through the tenant-admin control-plane adapter
- the kernel still does not claim RFC-level OIDC registration metadata or
  software-statement validation

## Operational Posture

- keep the active signing key in `OpenSslOidcProvider`
- during rollover, publish a `RotatingOidcProvider` with the new active provider
  plus one or more retiring verification providers
- keep retiring keys published until the maximum ID-token lifetime and RP cache
  TTL have both elapsed
- verify delivery against [oidc-conformance-matrix.md](oidc-conformance-matrix.md)
  and [oidc-key-rollover-runbook.md](oidc-key-rollover-runbook.md)
