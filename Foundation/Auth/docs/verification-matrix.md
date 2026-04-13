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
- DPoP replay
- DPoP method and URI mismatch
- mTLS mismatch
- multi-key token verification during rollover

## Tenant And Admin

- tenant crossing rejection
- admin elevation abuse
- break-glass auditability
- deprovisioning revokes active access

## Data And Compliance

- PII masking in audit export
- legal hold overrides anonymization correctly
