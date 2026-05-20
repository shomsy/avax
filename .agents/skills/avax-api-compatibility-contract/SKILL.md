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
