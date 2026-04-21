# Workload Identity Posture

This package now ships a package-owned `client_credentials` runtime lane for
workload access tokens, while still leaving broader machine-identity control
planes and external trust adapters to host applications or sibling packages.

## Baseline Rules

- inventory every non-human client
- use confidential OAuth clients only
- use short-lived access tokens
- require sender-constrained posture for high-security workloads
- scope every workload to the smallest service boundary that works

The package now exposes a workload inventory surface through
`Auth::readWorkloadIdentities()`, so applications can audit every registered
machine client from the same kernel-owned source of truth that issues the
tokens.

## Recommended Client Policy

- `allowedGrantTypes`: include `client_credentials` only for non-human clients
- `workloadIdentity`: true
- `allowedAudiences`: explicit service boundaries such as `orders-api`
- `audienceScopeBoundaries`: per-service scope ceilings such as
  `orders-api -> orders.read`
- `requiredSenderConstraint`: `mTLS` or `DPoP`

## Operational Rules

- rotate client secrets or certificates without emergency redeploys
- use `FileBackedHmacKeyRingCodec` or another reloadable key ring when the
  host needs overlap windows without process restarts
- pin issuer and audience expectations per service
- keep transport validation at the adapter boundary
- prefer secretless or short-lived credential exchange when the host platform
  supports it
