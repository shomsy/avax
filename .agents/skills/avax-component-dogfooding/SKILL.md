---
name: avax-component-dogfooding
description: Enforces component dogfooding: AvaX components must reuse existing AvaX capabilities through stable boundaries instead of raw PHP primitives, ad-hoc logic, or duplicated infrastructure. Use for new components, refactors, architecture cleanup, dependency boundary, filesystem/cache/logging/events/config/security/runtime/HTTP/persistence/validation/serialization work, dogfooding, components use other components, AvaX eats AvaX, framework uses itself.
---

# AvaX Component Dogfooding

## Purpose

AvaX is not a set of isolated components. It is a coherent internal platform.

AvaX components must dogfood AvaX capabilities.

Components must reuse existing AvaX capabilities where architecturally appropriate:

- Filesystem
- Clock / Time
- Logger / Observability
- Events
- Cache
- Configuration
- Container / DI
- Runtime lifecycle
- Security / Cryptography / Redaction
- HTTP abstractions
- Validation
- Serialization
- Error/failure boundaries
- Testing utilities
- Any other approved first-party component or capability

The goal is:

- less duplication
- stronger architectural coherence
- dogfooding of AvaX itself
- better runtime safety
- better testability
- clearer ownership
- fewer raw PHP shortcuts
- fewer hidden infrastructure leaks

## Activation Triggers

Activate for tasks involving:

- new component
- component refactor
- architecture cleanup
- dependency boundary
- filesystem
- cache
- logging
- events
- configuration
- security
- runtime
- HTTP
- persistence
- validation
- serialization
- public surface
- component design
- "dogfooding"
- "components use other components"
- "AvaX eats AvaX"
- "framework uses itself"

## Core Rule

Before implementing or refactoring a component, the agent must ask:

- Does AvaX already have a component/capability for this concern?
- Am I bypassing an existing first-party capability?
- Am I duplicating logic that already exists elsewhere?
- Am I using raw PHP where an AvaX boundary should be used?
- Am I creating a local workaround instead of improving/reusing a component?
- Is this code in Foundation/adapter boundary where raw PHP is allowed?
- Is the dependency direction allowed?
- Is the component coupling acceptable?
- Is the dependency stable enough to use?
- Do I need PublicSurface, internal Capability, Configuration/Provider, or Foundation boundary?

### Builder and Assembly Dogfooding

Builders and assembly graphs must use existing AvaX components/capabilities through correct boundaries where architecturally appropriate.

Builders must not:

- bypass existing AvaX capabilities
- recreate logic locally that an AvaX component already owns
- create hidden framework duplication through builder assembly
- use raw primitives where an AvaX component boundary should be used

Every builder must document:

- which AvaX components it uses
- which components it bypasses (with reason)
- whether raw primitives are used (with RAW_PRIMITIVE_ALLOWED exception)

See `how-to-architecture.md` — Section 13.3.1 Builder Validity Rule.

## Allowed Dependency Paths

Components may use other components through:

- stable PublicSurface APIs
- approved Capability APIs
- explicit internal component dependency declared by provider/configuration
- Configuration / Assembly / Provider boundaries
- adapter boundaries
- Foundation primitives where appropriate
- test utilities in tests only

## Forbidden Dependency Paths

Forbid:

- reaching into another component's private/internal implementation without approved boundary
- circular component dependency
- hidden service locator calls
- global `app()` shortcuts inside component internals
- raw filesystem IO when Filesystem component should own it
- raw env/config access when Configuration should own it
- raw logging/echo/print when Logger/Observability should own it
- raw cache arrays when Cache component should own it
- raw security-sensitive primitives without Security/Cryptography boundary
- duplicated serializers/parsers/validators when a component already owns that concern
- direct runtime object graph construction where DI/Assembly should own it
- local "mini-frameworks" inside components

## Raw PHP Exception Rule

Raw PHP primitives are allowed only when:

- code is inside Foundation or approved adapter boundary
- no AvaX component exists yet
- performance requires a low-level primitive and the boundary is documented
- the primitive is wrapped behind a first-party capability
- the task is explicitly to create the first implementation of that capability

Every exception must be documented in evidence as:

- RAW_PRIMITIVE_ALLOWED
- reason
- owner
- risk
- mitigation
- whether a future AvaX component should replace it

## Component Dependency Review

Before commit, every production-code change must include a component dependency review:

- components used
- components bypassed
- raw primitives used
- duplicate logic found
- dependency direction
- possible circular dependency
- public/internal boundary used
- dogfooding decision
- accepted exceptions

Write this into:

`component-dogfooding-review.md`

inside the task evidence folder.

## Dogfooding Classification

Classify each implementation:

- DOGFOODS_EXISTING_COMPONENTS
- CREATES_NEW_FIRST_PARTY_CAPABILITY
- ACCEPTABLE_FOUNDATION_PRIMITIVE
- ACCEPTABLE_ADAPTER_PRIMITIVE
- DUPLICATES_EXISTING_CAPABILITY
- BYPASSES_COMPONENT_BOUNDARY
- CREATES_CIRCULAR_DEPENDENCY
- NEEDS_COMPONENT_DESIGN_REVIEW

Commit is forbidden if classification is:

- DUPLICATES_EXISTING_CAPABILITY
- BYPASSES_COMPONENT_BOUNDARY
- CREATES_CIRCULAR_DEPENDENCY
- NEEDS_COMPONENT_DESIGN_REVIEW

unless explicitly accepted as YELLOW with owner, risk, mitigation, and expiry.

## Integration with Other Skills

This skill must be loaded together with:

- `avax-enterprise-remediation`
- `avax-autonomous-backlog-loop` for autonomous sweeps
- `avax-enterprise-codecraft` for production-code changes
- `avax-runtime-performance-cache` when caching or performance primitives are involved
- `review` skill
- `validation` skill
- `testing` skill
- `security` skill when security-sensitive
- `performance` skill when performance-sensitive

It does not replace HLD/LLD/SOLID review.
It extends it with first-party component reuse and platform coherence.

## Evidence Requirement

For every production-code task using this skill, evidence must include:

- `component-dogfooding-review.md`
- `dependency-boundary.md` if dependencies changed
- `high-level-design.md` for architecture-heavy changes
- `low-level-design.md` for non-trivial implementation
- `validation-output.md`
- `governance-review.md`

## Final Rule

No reuse, no platform.

No boundary, no dependency.

No dogfooding review, no GREEN.

No evidence, no commit.

## Object-Oriented Enterprise Architecting Philosophy

Component dogfooding must serve the object-oriented enterprise architecture philosophy.

See:

- `how-to-design-components.md` — Section 30: Object-Oriented Enterprise Architecting Rule
- `how-to-architecture.md` — Section 54: Object-Oriented Enterprise Architecting Rule

Agents must:

- treat component boundaries as handovers — document what crosses, who owns it, what tests prove the contract
- apply data product thinking — expose consumer-oriented data, not internal models
- maintain knowledge backbones — core knowledge must be explicitly named and owned by a component
- respect bounded contexts — each component owns some concepts, consumes others, publishes through stable APIs
- use views for loose coupling — expose consumer-specific interfaces, not internal machinery

Dogfooding that creates tighter coupling or leaks internal models across boundaries is a design failure.
