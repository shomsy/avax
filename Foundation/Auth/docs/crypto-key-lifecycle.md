# Crypto And Key Lifecycle

This package does not own KMS or HSM infrastructure, but it does own the
runtime contract that token artifacts must support key versioning and rotation
discipline.

## Package-Owned Controls

- `Flow/Token/HmacTokenCodec.php` can stamp a `kid` header on issued JWTs
- JWT verification rejects tokens whose `kid` does not match the configured key
  version
- token claims remain algorithm-agnostic at the kernel boundary

## Required Operational Practices

- assign a versioned `kid` to every active signing key
- rehearse signing-key rollover before using it in production
- document a forced-compromise drill for leaked or suspected leaked signing
  material
- keep backup and restore posture for encryption materials under application
  control
- preserve crypto agility by not coupling callers to one concrete algorithm

## Recommended Rollover Procedure

1. publish the next key version in the application environment
2. switch issuers to the new `kid`
3. continue accepting the previous `kid` for a bounded overlap window at the
   application boundary
4. revoke or age out tokens signed by the retired `kid`
5. remove the retired key from active verification

## Forced Compromise Drill

1. stop issuing with the suspected key immediately
2. rotate to a new `kid`
3. revoke active token families if compromise scope is uncertain
4. audit unusual token introspection, refresh reuse, and admin activity
5. preserve evidence under legal hold when needed

## Boundary Note

Multi-key verification and external KMS orchestration remain application or
adapter concerns. The kernel provides the token artifact seam and the explicit
`kid` contract.
