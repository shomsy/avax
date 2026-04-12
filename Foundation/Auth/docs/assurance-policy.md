# Assurance Policy Matrix

This package treats assurance as an explicit policy surface, not as a side
effect of whichever factor happened to succeed.

## Primary Rules

- passkeys or another phishing-resistant factor are required for admin and
  tenant-admin posture
- password plus TOTP remains a compatibility path, not the preferred primary
  path for high-assurance users
- sender-constrained tokens are the default high-security API posture
- machine identities must use sender-constrained tokens when the deployment
  enables workload identity

## Policy Matrix

| Actor | Assurance | Allowed Factors | Required Factors | Idle | Absolute | Fresh MFA | Recovery | Extra Rules |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| `user` | `standard` | password, TOTP, backup code, passkey, federated SSO | password | 30m | 12h | n/a | password reset | deny by default |
| `privileged_user` | `high` | password, TOTP, backup code, passkey, federated SSO | password + TOTP | 15m | 8h | 5m | MFA recovery | resource checks required |
| `admin` | `phishing_resistant` | passkey, federated SSO, password, TOTP, backup code | passkey | 10m | 4h | 3m | admin approval | phishing-resistant, admin elevation, SoD, approval path |
| `support` | `high` | password, TOTP, passkey, federated SSO | password + TOTP | 10m | 6h | 3m | admin approval | SoD and approval path |
| `tenant_admin` | `phishing_resistant` | passkey, federated SSO, password, TOTP | passkey | 15m | 6h | 3m | admin approval | phishing-resistant and SoD |
| `machine_identity` | `high` | DPoP, mTLS | mTLS | 5m | 1h | n/a | none | sender-constrained tokens required |
| `break_glass` | `emergency` | passkey | passkey | 5m | 30m | 1m | admin approval | phishing-resistant, SoD, approval path |

## Package Surface

- `Capability/Access/Policy/IdentityPolicy.php` owns one matrix row
- `Capability/Access/Policy/IdentityPolicyCatalog.php` owns the default package
  catalog
- `Capability/Access/Policy/AccessPolicy.php` can be built from an explicit
  identity policy
- `Flow/OAuth/RegisterClient/RegisterClientData.php` and `Capability/OAuth/`
  own client-level phishing-resistant and sender-constraint posture

## OAuth And API Notes

- high-assurance OAuth clients can require phishing-resistant user auth before
  authorization-code issuance
- sender-constrained tokens are represented by
  `Capability/OAuth/SenderConstraint/OAuthSenderConstraint.php`
- DPoP is the preferred public-client binding
- mTLS is the preferred enterprise server-to-server binding

## Compatibility Note

This package owns policy and enforcement seams. Applications still own final
transport integration, real passkey runtime integration, certificate
verification, DPoP proof verification, and session-store configuration.
