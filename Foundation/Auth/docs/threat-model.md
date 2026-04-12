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
| stolen refresh token | rotation, reuse detection, family revocation | implemented |
| MFA bypass | challenge lifecycle, replay protection, backup code one-time use, fresh-MFA checks | implemented |
| recovery takeover | anti-enumeration start, recovery throttling, reset-driven revocation, MFA recovery auditing | implemented |
| admin account takeover | stronger realm not yet package-owned | planned |
| email change takeover | dedicated email-change flow not yet package-owned | planned |
| refresh token reuse | family revocation and audit | implemented |
| insider misuse | audit trail exists; approval workflows are application-owned | partial |
| tenant isolation bug | tenant model not yet package-owned | planned |

## Incident Classes

- `auth_incident`: login, logout, MFA, or auth-ingress compromise indicators
- `credential_incident`: password reset, password change, or hash-policy compromise indicators
- `session_incident`: session theft, stale session use, or mass revocation scenarios
- `oauth_incident`: authorization-code abuse, refresh reuse, or client secret compromise
- `admin_incident`: privileged access misuse or break-glass usage

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
- refresh reuse revokes the entire token family
- revoke and introspection flows exist for downstream incident handling

## Severity Matrix

| Severity | Definition |
| --- | --- |
| critical | cross-user compromise, tenant boundary break, privileged compromise |
| high | one-user takeover or reusable token/session compromise |
| medium | throttled attack signal, weak audit gap, or single-flow bypass attempt |
| low | observability gap without immediate auth bypass |
