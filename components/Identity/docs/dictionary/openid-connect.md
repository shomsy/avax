# OpenID Connect

## What It Is

OpenID Connect (OIDC) is an identity layer built on top of OAuth 2.0. OIDC provides a standard way to authenticate users and obtain verified identity claims through an ID Token.

OIDC answers the question "Who is this user?" in a standardized, interoperable way.

## What It Is NOT

- OIDC is NOT OAuth. OIDC uses OAuth as its transport, but adds identity semantics. OAuth alone does not provide verified identity.
- OIDC is NOT SAML. SAML is a different protocol with different message formats and trust models.
- OIDC is NOT authorization. OIDC provides identity claims. Authorization decisions consume those claims but are a separate concern.
- OIDC is NOT a replacement for local identity. OIDC provides external identity that may be mapped to or linked with local identity.

## Common Confusion

People often treat OIDC and OAuth as interchangeable. They are not. OAuth provides access tokens for resource access. OIDC provides ID tokens for identity verification. Using OAuth access tokens for identity decisions without OIDC is a security anti-pattern.

Another confusion is trusting ID tokens without validation. ID tokens must be validated (signature, issuer, audience, expiry, nonce) before any claims are trusted. Skipping validation leads to identity injection attacks.

## In AvaX

AvaX treats OIDC as:

- The standard protocol for external identity provider integration
- A source of verified identity claims through ID token validation
- Integrated with the authentication capability as an external credential type
- Subject to strict ID token validation before any trust is granted
- Observable: OIDC flow events produce security monitoring data

OIDC in AvaX produces the same verified identity as any authentication method. The OIDC capability handles protocol-specific validation; the authentication capability produces the unified identity claim set.
