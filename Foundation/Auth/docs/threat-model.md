# Threat Model

## Scope

This document captures the current auth-kernel threat model and the controls owned by this package.

## Threat Matrix

| Threat | Package Control | Current Status |
| --- | --- | --- |
| credential stuffing | login rate limiting, safe failures, audit events | implemented |
| brute force | login rate limiting, MFA attempt throttling | implemented |
| session hijack | secure cookies, session ID regeneration, idle timeout, absolute timeout, tracked session revocation | implemented |
| stolen authorization code | short TTL, single use, redirect URI match, PKCE verification for public clients | implemented |
| stolen refresh token | rotation, reuse detection, family revocation, optional sender-constrained binding checks | implemented |
| bearer token replay | DPoP proof verification, mTLS binding verification, binding mismatch audit, and sender-constrained token policy | implemented |
| passkey replay | single-use passkey challenge lifecycle and revoked-credential checks | implemented |
| MFA bypass | challenge lifecycle, replay protection, backup code one-time use, fresh-MFA checks | implemented |
| recovery takeover | anti-enumeration start, recovery throttling, reset-driven revocation, MFA recovery auditing | implemented |
| admin account takeover | explicit phishing-resistant admin policy, admin elevation, fresh-MFA step-up, passkey support, audit trail | implemented |
| email change takeover | dedicated email-change flow, current-password proof, fresh MFA, one-time confirmation, audit | implemented |
| refresh token reuse | family revocation, risk review signal, and audit | implemented |
| insider misuse | audit trail, admin-elevation state, and authorization hardening checklist exist; approval workflows remain application-owned | partial |
| tenant isolation bug | unique-domain federation policy, verified-domain discovery, health-gated SSO start, SCIM directory scoping, and tenant security change validation with rollback exist; full tenant membership model is still not package-owned | partial |

## Incident Classes

- `auth_incident`: login, logout, MFA, or auth-ingress compromise indicators
- `credential_incident`: password reset, password change, or hash-policy compromise indicators
- `session_incident`: session theft, stale session use, or mass revocation scenarios
- `oauth_incident`: authorization-code abuse, refresh reuse, or client secret compromise
- `admin_incident`: privileged access misuse or break-glass usage
- `federation_incident`: tenant SSO configuration or federated-login compromise indicators
- `risk_incident`: deterministic risk rule activation that requires review

## Failure And Recovery Notes

### Session Compromise

- revoke the affected session if known
- revoke all tracked sessions for the user when scope is uncertain
- revoke refresh tokens for the user
- require fresh login and MFA

### Password Reset Compromise

- single-use reset token prevents replay
- successful reset revokes tracked sessions
- successful reset revokes refresh tokens
- successful reset forgets active MFA challenges

### MFA Recovery Compromise

- recovery start is throttled and anti-enumeration
- successful recovery disables MFA and revokes tracked sessions and refresh tokens
- recovery emits auditable security events

### OAuth Grant Compromise

- authorization codes are single use and short lived
- public clients require `S256` PKCE before token exchange
- high-assurance clients can require phishing-resistant user auth before code
  issuance
- sender-constrained clients must present the same DPoP or mTLS binding on
  refresh exchange and can enforce the same binding at the HTTP adapter boundary
- refresh reuse revokes the entire token family
- revoke and introspection flows exist for downstream incident handling

### Passkey Compromise

- registration and authentication use separate one-time challenges
- revoked credentials can no longer authenticate
- users can rename or revoke individual passkeys without affecting password or TOTP ownership

### Federation Misrouting

- login discovery resolves by normalized domain into one verified tenant-owned
  connection
- completed federated login leaves an audit trail with tenant context
- invalid group mappings are rejected at registration time and federated groups
  only map into known package roles

### Tenant Security Misconfiguration

- tenant security changes validate referenced federation and SCIM resources
- apply requires approval and rollback is explicit
- configuration changes emit an auditable diff and rollout version

## Severity Matrix

| Severity | Definition |
| --- | --- |
| critical | cross-user compromise, tenant boundary break, privileged compromise |
| high | one-user takeover or reusable token/session compromise |
| medium | throttled attack signal, weak audit gap, or single-flow bypass attempt |
| low | observability gap without immediate auth bypass |
