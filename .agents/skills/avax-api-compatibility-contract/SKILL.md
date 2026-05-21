---
name: avax-api-compatibility-contract
description: Protects AvaX public API, PublicSurface, DSL, facade behavior, and backward compatibility. Use for PublicSurface, Facade, DSL, App::/Route::/Auth::/Responses:: APIs, Builders, configuration public APIs, package exports, contract tests, deprecations, or any public interface change.
---

# AvaX API Compatibility Contract

## Purpose

Protect AvaX public API, PublicSurface, DSL, facade behavior, and backward compatibility.

Public API changes affect downstream users. Breaking changes require explicit analysis, migration paths, and approval.

## Activation Triggers

Activate for tasks involving:

- PublicSurface changes
- Facade behavior changes
- DSL syntax or semantics
- App::, Route::, Auth::, Responses:: APIs
- Builder public methods
- configuration public APIs
- package exports
- contract tests
- deprecations
- method signature changes on public classes
- removed public methods
- changed return types
- changed exception contracts
- changed default behavior visible to users

## API Compatibility Questions

The agent must answer:

- public API changed: YES/NO
- backward compatible: YES/NO
- migration needed: YES/NO
- contract tests updated: YES/NO
- old behavior preserved: YES/NO
- deprecation path if changed
- semantic version impact (major/minor/patch)
- user-facing examples still work: YES/NO
- documentation updated: YES/NO
- changelog entry needed: YES/NO

## Compatibility Gates

Before commit:

- [ ] public API inventory complete
- [ ] backward compatibility assessed
- [ ] breaking changes documented
- [ ] migration path defined if breaking
- [ ] contract tests pass
- [ ] deprecation warnings added if applicable
- [ ] documentation updated
- [ ] user-facing examples verified

## Required Evidence

Every public API change must include:

- `api-compatibility.md`
- `public-contract-test-proof.md`

## Breaking Change Classification

- MAJOR_BREAK: removes or fundamentally changes public behavior
- MINOR_BREAK: deprecates but preserves old behavior with warning
- PATCH_SAFE: internal change with no public impact
- BEHAVIOR_EXTENSION: adds new capability without changing existing behavior

## Public API Break Rule

Public API break without explicit approval = BLOCKER.

Approval requires:

- documented reason
- migration path
- deprecation timeline
- affected user inventory
- changelog entry

## DSL Stability

DSL changes require extra scrutiny:

- syntax changes break user code
- semantics changes create silent behavior bugs
- removal of DSL features requires deprecation cycle
- new DSL features must be opt-in or backward compatible

## Facade Behavior

Facades must:

- delegate to correct internal owner
- not accumulate ad-hoc behavior
- remain thin and stable
- log deprecation when forwarding to changed internals

## Hard Enterprise OOP Boundary API Cross-Reference

**Status:** MANDATORY  
**Severity:** BLOCKER

Public API changes must respect enterprise OOP boundaries.

See:

- `how-to-design-components.md` — Section 31: Hard Enterprise OOP Boundary Rules
- `how-to-architecture.md` — Section 54: Hard Enterprise OOP Boundary Rules

API-relevant rules:

- **Single Preferred Entry:** PublicSurface must expose one controlled gateway per subsystem. Multiple public entry points create API surface that is hard to version, deprecate, and test.
- **Boundary Value Objects:** Public API must accept and return value objects at subsystem boundaries, not raw primitives. This protects backward compatibility by making the contract explicit.
- **Command/Query Clarity:** Public API methods must not mix mutation and return. A public method that both creates a resource and returns its representation creates confusion for consumers and breaks idempotency expectations.
- **Horizontal Blindness:** Public API must not expose internal sibling-component dependencies. If component A needs component B, the public API should expose a coordinated facade, not force consumers to wire them together.
- **HLD/LLD Mirror:** The public API surface must match the documented architecture. If the API docs say A is the entry point, code must not allow B to be called directly.

API violations of these rules are classified as:

- MAJOR_BREAK: exposing multiple uncontrolled entry points, leaking internal primitives, mixing command/query in public API
- MINOR_BREAK: boundary violations with deprecation path
- PATCH_SAFE: internal boundary cleanup with no public impact

## Integration with Other Skills

This skill must be loaded together with:

- `avax-enterprise-remediation`
- `avax-enterprise-codecraft` for production-code changes
- `avax-test-evidence-quality` for contract tests
- `review` skill

## Final Rule

No API inventory, no public change.

No contract test proof, no GREEN.

No migration path for breaking changes, no commit.
