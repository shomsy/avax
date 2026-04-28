# Authentication And Session Security

Version: 1.0.0
Status: Normative

Use this policy for login surfaces, session-bearing applications, APIs with
tokens, service-to-service identity, and role or tenant-protected systems.

## Identity Rule

Authentication proves identity.
Authorization proves permission.
The two must be explicit and separately reviewable.

## Authentication Baseline

- login, token issue, session creation, and machine identity issuance must be
  explicit owned flows
- secrets, tokens, and factors must never be logged in raw form
- password storage must use current one-way hashing with per-secret salt and a
  tunable work factor
- step-up authentication should exist for high-risk actions when the product
  risk justifies it
- failed authentication must produce useful operator evidence without leaking
  account enumeration details by default

## Session And Token Rules

- decide one primary auth posture per surface: session cookie, bearer token,
  mutual TLS, signed request, or another explicit contract
- session expiry, absolute lifetime, idle timeout, and revocation rules must be
  explicit
- session fixation and token replay resistance must be considered for login and
  renewal flows
- browser storage of long-lived credentials is a security decision, not a
  convenience default
- refresh flows and rotation must preserve caller binding and auditability

## Authorization Rules

- authorization must be enforced on every protected resource and business flow
- role checks alone are not enough when object ownership, tenant, property, or
  function-level rules exist
- default-deny and least privilege are the baseline
- admin, support, impersonation, and break-glass flows require explicit review
  and audit posture
- permission changes and role assignment are security-sensitive mutations

## Browser Session Rules

- CSRF posture must be explicit for browser-attached credentials
- session cookies should be `Secure`, `HttpOnly`, and intentionally scoped
- origin, referer, and same-site behavior should be defined where relevant
- logout, revocation, and session invalidation must be real, not cosmetic UI
- password reset and email change flows need anti-abuse and token integrity
  posture

## Service Identity Rules

- service accounts, API keys, workload identities, and machine credentials need
  named ownership and narrow scope
- shared long-lived machine credentials are a last resort, not a default
- service-to-service auth should validate audience, issuer, and transport trust
  as applicable
- machine identities should be rotatable without emergency code edits
- non-human credentials should be inventoried and revocable

## Verification

Protected systems should test:

- login success and failure posture
- authorization denial paths
- object and property ownership enforcement
- session expiry and revocation
- CSRF posture where browsers attach credentials
- admin or break-glass path auditability
