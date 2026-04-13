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
- framework-neutral HTTP publication through
  `integrations/http/Oidc/ServeOidcHttpSurface.php`

## Still Outside This Package

- dynamic client registration
- front-channel or back-channel logout
- pairwise subject identifiers
- JAR, PAR, JARM, and other advanced request-object surfaces
- external conformance certification

## Operational Posture

- keep the active signing key in `OpenSslOidcProvider`
- during rollover, publish a `RotatingOidcProvider` with the new active provider
  plus one or more retiring verification providers
- keep retiring keys published until the maximum ID-token lifetime and RP cache
  TTL have both elapsed
- verify delivery against [oidc-conformance-matrix.md](oidc-conformance-matrix.md)
  and [oidc-key-rollover-runbook.md](oidc-key-rollover-runbook.md)
