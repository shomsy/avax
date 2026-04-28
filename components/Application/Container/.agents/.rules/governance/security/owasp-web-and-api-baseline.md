# OWASP Web And API Baseline

Version: 1.0.0
Status: Normative

Use this baseline for web applications, APIs, SPAs, backend-for-frontend
layers, webhook surfaces, and browser-exposed systems.

## Scope

This document is aligned to current OWASP awareness anchors:

- OWASP Top 10: 2025 for current web application risk categories
- OWASP API Security Top 10: 2023 for API-specific exposure
- OWASP ASVS 5.0.0 for verification depth
- OWASP Proactive Controls for developer-facing implementation posture

It is not a copy of those standards.
It is the reusable baseline that child repos should actually enforce.

## Universal Web And API Security Rules

- broken access control is release-blocking
- cryptographic handling must be explicit, current, and reviewable
- all injection classes must be prevented by construction where possible
- insecure design is a design defect, not only an implementation defect
- security misconfiguration is a product defect, not an ops afterthought
- vulnerable or outdated components are supply-chain defects, not routine noise
- authentication failures must not be masked as ordinary edge cases
- software and data integrity posture must include CI/CD and artifact trust
- logging and monitoring must support detection and forensics without leaking
  secrets
- SSRF and outbound trust abuse must be constrained explicitly

## API-Specific Rules

APIs must defend against at least these recurring risk shapes:

- broken object level authorization
- broken authentication
- broken object property level authorization
- unrestricted resource consumption
- broken function level authorization
- unrestricted access to sensitive business flows
- server-side request forgery
- security misconfiguration
- improper inventory management
- unsafe consumption of upstream APIs

If an API surface exists, the repository should be able to point to controls for
each of those classes.

## Access Control And Object Ownership

- authorization belongs at the owned resource boundary, not only in routing
- access checks must be repeated for every object, collection, and property
  access that depends on caller-controlled identifiers
- deny-by-default is the baseline
- privilege escalation and tenant breakout are `critical` findings
- hidden admin paths, debug paths, and feature flags are access-control
  surfaces too

## Input, Output, And Injection Safety

- validate, normalize, and constrain all untrusted input before use
- use safe parameterization for database, search, template, shell, and query
  construction
- encode output by context for HTML, URLs, headers, scripts, and templates
- deserialization, file parsing, and document processing paths are attack
  surfaces and must be treated as such
- business-flow inputs need both syntactic validation and authorization-aware
  validation

## Session, Token, And Browser Trust Rules

- if browsers attach credentials automatically, CSRF posture must be explicit
- cookies carrying session value should be `Secure`, `HttpOnly`, and have
  intentional `SameSite` posture
- token issuance, expiry, revocation, and audience or scope validation must be
  explicit
- CORS must be restrictive and environment-aware; production wildcard posture
  is generally NO-GO for protected APIs
- clickjacking, content-type confusion, and unsafe embedding should be denied by
  default for protected web surfaces

## Security Headers And Client-Side Baseline

Where the delivery model allows it, protected web surfaces should have explicit
policy for:

- Content Security Policy
- frame embedding or clickjacking protection
- content type sniffing prevention
- referrer policy
- transport security posture
- browser storage use for tokens or sensitive data

## Resource And Abuse Controls

- rate limiting, quotas, and concurrency limits should exist where abuse cost
  is real
- file upload, webhook, and parsing surfaces need size limits and type posture
- pagination, search, and export endpoints need bounded consumption rules
- long-polling, streaming, and websocket-like behavior needs explicit abuse and
  backpressure posture
- bot, replay, and enumeration resistance should be intentional where the
  surface justifies it

## Inventory And Exposure Rules

- every exposed endpoint, webhook, job ingress, callback URL, and admin surface
  should have an owner
- deprecated endpoints must be tracked and retired intentionally
- shadow APIs and forgotten environments are security defects
- upstream API consumption must be verified, bounded, and monitored
- environment parity must not hide weaker production or staging trust policy

## Verification Rule

Serious applications should map security testing depth against OWASP ASVS or an
equivalent verification model.
If the team claims production readiness, it should be able to show which
verification depth is expected and what evidence satisfies it.
