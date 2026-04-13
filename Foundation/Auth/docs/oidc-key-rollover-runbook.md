# OIDC Key Rollover Runbook

Use this when rotating the signing key for OIDC ID tokens without breaking
relying parties during the overlap window.

## Preconditions

- new RSA keypair has been generated and stored in application-owned secret
  infrastructure
- current `issuer`, authorization endpoint, token endpoint, and JWKS URI remain
  stable
- RP cache TTL for JWKS is known

## Cutover Steps

1. Build a new `OpenSslOidcProvider` with the next key and key ID.
2. Wrap it in `RotatingOidcProvider`, keeping the retiring provider in
   `verificationProviders`.
3. Deploy the rotating provider so JWKS publishes both the new active key and
   the retiring key.
4. Confirm `Auth::readOidcJsonWebKeySet()` returns both keys.
5. Start issuing new ID tokens from the active provider only.
6. Wait until the maximum ID-token lifetime and RP JWKS cache TTL have both
   elapsed.
7. Remove the retiring provider from `verificationProviders`.

## Verification

- new ID tokens resolve under the new `kid`
- legacy ID tokens still resolve during overlap
- JWKS exposes the expected active and retiring keys
- `tests/Flows/Oidc/OidcFlowTest.php` stays green
