# ADR-0001: Authentication Strategy

## Status

proposed

## Context

AvaX requires a unified authentication strategy that supports multiple credential types (passwords, passkeys, OAuth/OIDC, API keys), multi-factor authentication, and tenant-aware identity resolution. The strategy must work across different runtimes (PHP-FPM, FrankenPHP, RoadRunner, long-lived workers) and maintain security invariants regardless of execution context.

Key requirements:
- Pluggable credential verification
- Consistent identity output regardless of credential type
- Support for MFA as a conditional flow step
- Tenant context resolution integrated with authentication
- Fail-closed behavior on any uncertainty
- Observable authentication events

## Decision

AvaX will use a capability-based authentication strategy where:

1. A single authentication capability accepts any credential type
2. Credential-specific verification is delegated to typed credential validators
3. All validators produce the same verified identity claim set
4. MFA is integrated as a conditional step in the authentication flow, triggered by policy
5. Authentication events are observable through the observability boundary
6. Authentication fails closed on any verification uncertainty

The authentication strategy separates:
- Credential collection (user-facing flow concern)
- Credential verification (capability concern)
- Identity production (authentication capability output)
- Session/token creation (separate capabilities)

## Consequences

- New credential types require implementing a credential validator, not modifying the authentication flow
- All authentication produces the same identity claim structure regardless of credential type
- MFA policy can be changed without modifying the authentication capability
- Authentication events are consistently observable
- Testing must cover each credential type independently and the unified flow

## Trade-offs

Gained:
- Unified authentication interface regardless of credential type
- Pluggable credential verification without flow modification
- Consistent security invariants across all authentication paths
- Observable authentication behavior for security monitoring

Given up:
- Simplicity of credential-specific authentication paths
- Direct coupling between credential type and identity production
- Some performance optimization opportunities from specialized paths
