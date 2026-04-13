# Verification Matrix

The final verification bar for this package now includes the following
scenarios.

## Authentication And Session

- login success and failure
- session fixation
- CSRF on browser session routes
- password reset
- MFA replay rejection
- passkey replay rejection

## Token And Sender Constraint

- refresh reuse
- OIDC discovery metadata and JWKS exposure
- OIDC nonce-bound ID-token issuance
- OIDC userinfo reads from active access tokens
- DPoP replay
- DPoP method and URI mismatch
- mTLS mismatch
- multi-key token verification during rollover

## Tenant And Admin

- tenant crossing rejection
- tenant security approval gating
- tenant security apply and rollback
- admin elevation abuse
- break-glass auditability
- deprovisioning revokes active access

## Provisioning And Directory

- SCIM directory token rotation
- SCIM idempotent update
- SCIM drift detection during group sync

## Data And Compliance

- PII masking in audit export
- legal hold overrides anonymization correctly
