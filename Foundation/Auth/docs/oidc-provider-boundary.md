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
  storage, request-object claim validation, dynamic client registration,
  package-owned client-signed PAR request verification for confidential and
  public clients, and JARM response signing through the kernel-owned OIDC
  flows
- framework-neutral HTTP publication through
  `integrations/http/Oidc/ServeOidcHttpSurface.php`

## Still Outside This Package

- standards certification and interoperability program ownership
- remote `jwks_uri` fetch/rotation and software-statement validation for dynamic client registration
- external conformance certification
- richer admin/onboarding UI beyond the thin package-owned HTTP registration surface

## Operational Posture

- keep the active signing key in `OpenSslOidcProvider`
- during rollover, publish a `RotatingOidcProvider` with the new active provider
  plus one or more retiring verification providers
- keep retiring keys published until the maximum ID-token lifetime and RP cache
  TTL have both elapsed
- register client verifier keys through the tenant-admin control-plane or the
  thin OIDC registration surface when request-object signatures must be
  enforced
- verify delivery against [oidc-conformance-matrix.md](oidc-conformance-matrix.md)
  and [oidc-key-rollover-runbook.md](oidc-key-rollover-runbook.md)
