---
name: avax-security-threat-model
description: Security-sensitive changes require explicit threat modeling and fail-closed proof. Covers auth, session, CSRF, tokens, cryptography, redaction, secrets, logging, serialization, SQL, CSV, filesystem, redirects, headers, cookies, cache with user data, runtime state, dependency loading. Use for every security-relevant change.
---

# AvaX Security Threat Model

## Purpose

Security-sensitive changes require explicit threat modeling and fail-closed proof.

This skill ensures security changes are analyzed through structured threat models, not just code review.

## Activation Triggers

Activate for tasks involving:

- auth, authentication, authorization
- session management
- CSRF
- tokens (JWT, access, refresh, API)
- cryptography, encryption, hashing
- redaction, secret handling
- secrets, credentials, API keys
- logging sensitive data
- request signing
- serialization, deserialization
- SQL, query building
- CSV import/export
- filesystem access, file upload
- redirects
- headers, cookies
- cache containing user/tenant data
- runtime state leakage
- dependency loading, dynamic class loading
- admin/control plane access
- AI-generated security code

## Threat Model Questions

The agent must answer:

- What asset is protected?
- Who is the attacker? (external user, authenticated user, admin, insider, automated script)
- What are trusted vs untrusted inputs?
- What threat classes apply:
  - injection
  - tampering
  - replay attacks
  - data leakage
  - stale state exploitation
  - privilege escalation
  - SSRF
  - path traversal
  - deserialization attacks
  - cache poisoning
  - timing attacks
  - downgrade attacks
- What must fail closed?
- What negative tests prove fail-closed behavior?
- What logs/observability are safe?
- What secrets are redacted?
- What worker-state risk exists?

## Required Evidence

Every security-sensitive task must include:

- `threat-analysis.md`
- `negative-test-proof.md`
- `security-review.md`

## Threat Classification

Classify each threat:

- BLOCKER: exploitable in production, no mitigation
- HIGH: exploitable under specific conditions, mitigation partial
- MEDIUM: requires unlikely conditions, mitigation exists
- LOW: theoretical, defense-in-depth improvement
- ACCEPTED_YELLOW: known trade-off with owner, risk, mitigation, expiry

## Security Gates

Before commit:

- [ ] boundary identified
- [ ] validation present on all untrusted inputs
- [ ] authorization present where needed
- [ ] secrets redacted in all outputs
- [ ] state does not leak between requests/workers
- [ ] fail-closed behavior proven
- [ ] negative tests cover attack vectors
- [ ] logs do not contain sensitive data
- [ ] worker-state risk assessed

## Security HIGH/BLOCKER Rule

Security HIGH/BLOCKER cannot be downgraded without evidence.

Downgrade requires:

- proof of mitigating control
- negative test proving mitigation
- owner acceptance
- risk documentation
- expiry or review trigger

## Long-Lived Worker Security

For Swoole/RoadRunner/FrankenPHP/worker runtimes:

- static mutable security state must be reset
- per-request security context must be scoped
- cached security decisions must include tenant/user scope
- tokens/cookies must not leak between requests

## Integration with Other Skills

This skill must be loaded together with:

- `avax-enterprise-remediation`
- `avax-enterprise-codecraft` for production-code changes
- `avax-runtime-performance-cache` when cache contains security-sensitive data
- `avax-observability-failure-semantics` for failure behavior
- `testing` skill for negative tests
- `validation` skill

## Separation of Concern in Security

Security is a cross-cutting concern that must be explicit, not scattered.

Threat model analysis must verify:

- security decisions are owned by security capabilities, not inline in business logic
- authentication is separated from authorization
- validation is separated from sanitization
- secrets handling is separated from general configuration
- security-sensitive logging is separated from operational logging

Security logic scattered as inline code across multiple units is a SoC violation and a security risk.

See `how-to-architecture.md` — Section 57.6 (Cross-Cutting Concern Rule).

## Final Rule

No threat model, no security change.

No negative test, no GREEN.

No fail-closed proof, no commit.
