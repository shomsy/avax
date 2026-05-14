# Privacy, Retention, And Deletion Policy

This package treats privacy posture as part of auth design, not as an afterthought.

## Data Minimization

- do not log passwords, passkeys, TOTP secrets, backup codes, access tokens,
  refresh tokens, reset tokens, or client secrets
- keep only the minimum identity and event context needed for auth decisions,
  audit, incident response, and revocation
- prefer hashed or derived identifiers for reset, recovery, and token storage

## Recommended Retention Windows

| Record                                               | Recommended Retention                     |
|------------------------------------------------------|-------------------------------------------|
| audit events                                         | 365 days                                  |
| security events                                      | 400 days                                  |
| revoked sessions and token-family compromise markers | 90 days after expiry                      |
| password reset and MFA recovery records              | purge immediately after use or TTL expiry |
| temporary auth challenges                            | purge immediately after use or TTL expiry |

## Deletion And Anonymization

- revoke active sessions and refresh-token families before account deletion
- delete one-time auth challenges and recovery artifacts immediately after
  success or expiry
- anonymize audit rows when the retention window expires unless legal hold is
  active
- treat exported forensics as separate controlled artifacts owned by the
  application environment

## Legal Hold And Forensics Exception

- legal hold overrides normal anonymization and purge schedules
- legal hold must be explicit, time-bounded, and auditable
- break-glass or compromise investigations may preserve more context, but only
  through a documented incident path

## Residency And Segregation

- tenant or regional segregation is application-owned persistence policy
- the package keeps auth artifacts slice-local so applications can store them
  per region, per tenant, or per compliance boundary
- SCIM, SAML, and regional control-plane rules are conditional enterprise
  posture, not universal package defaults

## Ownership Boundary

The kernel owns safe defaults and event discipline. Applications own actual
database retention jobs, SIEM export retention, residency routing, and legal
hold enforcement.
