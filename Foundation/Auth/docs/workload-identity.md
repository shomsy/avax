# Workload Identity Posture

This package now documents workload identity as a first-class policy lane even
when host applications keep the full runtime in a separate adapter or package.

## Baseline Rules

- inventory every non-human client
- use confidential OAuth clients only
- use short-lived access tokens
- require sender-constrained posture for high-security workloads
- scope every workload to the smallest service boundary that works

## Recommended Client Policy

- `allowedGrantTypes`: include `client_credentials` only for non-human clients
- `workloadIdentity`: true
- `requiredSenderConstraint`: `mTLS` or `DPoP`

## Operational Rules

- rotate client secrets or certificates without emergency redeploys
- pin issuer and audience expectations per service
- keep transport validation at the adapter boundary
- prefer secretless or short-lived credential exchange when the host platform
  supports it
