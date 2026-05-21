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

## Hard Enterprise OOP Boundary Security Cross-Reference

**Status:** MANDATORY  
**Severity:** BLOCKER

Security boundaries must respect enterprise OOP rules.

See:

- `how-to-design-components.md` — Section 31: Hard Enterprise OOP Boundary Rules
- `how-to-architecture.md` — Section 54: Hard Enterprise OOP Boundary Rules

Security-relevant rules:

- **Boundary Value Objects:** Raw primitives (strings, ints) crossing security boundaries are a HIGH risk. Credentials, tokens, identifiers, permissions, and roles must be wrapped in typed value objects to prevent injection, tampering, and type confusion.
- **Horizontal Blindness:** Security components must not depend on each other directly. Authentication, authorization, and session management must be coordinated through a parent security gateway to prevent bypass chains.
- **Command/Query Clarity:** Security-critical methods must not mix mutation and return. A method that both validates a token and returns user data is a tampering risk. Split into separate validate (command) and retrieve (query).
- **Single Preferred Entry:** Security subsystems must expose one controlled gateway. Multiple entry points create attack surface for bypass, downgrade, and inconsistent policy enforcement.
- **HLD/LLD Mirror:** The security architecture must match the code. If the threat model says A validates and B enforces, code must not collapse them into one component.

Security violations of these rules are classified as:

- BLOCKER: raw secrets/credentials across boundaries, multiple uncontrolled security entry points, mixed command/query in auth path
- HIGH: horizontal coupling between security components without coordinator
- MEDIUM: value object boundary violations in non-critical paths

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

## Universal Enterprise Codecraft Philosophy

Security boundaries must serve the universal enterprise codecraft philosophy.

See:

- `how-to-design-components.md` — Section 30: Universal Enterprise Codecraft Rule
- `how-to-architecture.md` — Section 55: Universal Enterprise Codecraft Rule

Agents must:

- enforce structural honesty at security boundaries — never hide auth complexity behind opaque facades
- minimize cognitive load for security-critical code — security decisions must be readable and auditable
- reject technical theater in security — do not add security layers that look sophisticated but don't reduce risk
- prefer fluent security APIs — `App::identity()->auth()->requirePermission('admin')` not `AuthExecutor::execute(AuthRequest::from(...))`
- prefer subsystem decomposition — separate credential verification, session management, and policy enforcement into clear units

Security code that is hard to read is hard to audit. Security code that is hard to audit is insecure.

## Final Rule

No threat model, no security change.

No negative test, no GREEN.

No fail-closed proof, no commit.

## Object-Oriented Enterprise Architecting Philosophy

Security boundaries must serve the object-oriented enterprise architecture philosophy.

See:

- `how-to-design-components.md` — Section 30: Object-Oriented Enterprise Architecting Rule
- `how-to-architecture.md` — Section 54: Object-Oriented Enterprise Architecting Rule

Agents must:

- treat security boundaries as handovers — document what credentials/tokens/sessions cross
- apply knowledge backbone thinking — security knowledge (credentials, sessions, tokens, policies) must be explicitly owned
- use ubiquitous language for security concepts — stakeholders must understand auth, risk, elevation
- model security with IRTV — Information (credentials), Roles (authorities), Tasks (verify/issue/revoke), Views (auth APIs)
- consider enterprise reality — security must handle multiple vendors, legacy systems, external IdPs, conflicting interests
- apply EventStorming for complex security flows — discover security events, derive commands, identify aggregate owners

Security architecture that hides credentials or session state inside unnamed services is structurally dishonest.
