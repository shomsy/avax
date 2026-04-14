# Supported Deployment Profiles

Version: 1.0.0
Status: Normative / Local
Scope: `Avax\Auth\` deployment packaging

This package supports the following deployment profiles.

## Browser + Session Profile

- server-side session storage
- secure cookies
- CSRF protection at the HTTP boundary
- durable session registry required for enterprise mode

Use when:
- browser is the primary client
- you want cookie-backed auth instead of bearer tokens in storage

## OAuth API Profile

- short-lived access tokens
- refresh rotation
- sender-constrained tokens where required
- DPoP or mTLS verification at the HTTP boundary

Use when:
- SPA, mobile, CLI, or partner API clients consume the system

## Tenant Identity Profile

- tenant lifecycle
- tenant-owned OAuth clients
- federation and SCIM runtime core
- tenant security change approval and rollback

Use when:
- the package acts as an identity kernel for multi-tenant products

## Enterprise Mode

Enterprise mode is a stricter build posture, not a separate product.

Required:
- durable session registry
- release evidence
- source-truth checks
- documented deployment trust boundary

## Out Of Scope

These profiles are not shipped as package-owned products:

- tenant-admin UI
- SIEM, mail, queue, and KMS infrastructure
- external certification envelope
- SAML brokering runtime
