# Builder Assembly Graph Governance Update — Summary

## Date

2026-05-21

## Files Reviewed

- `.agents/how-to/how-to-architecture.md`
- `.agents/how-to/how-to-dependency-injection.md`
- `.agents/how-to/how-to-design-components.md`
- `.agents/how-to/how-to-clean-code.md`
- `.agents/how-to/how-to-coding-standards.md`
- `.agents/skills/avax-enterprise-codecraft/SKILL.md`
- `.agents/skills/avax-component-dogfooding/SKILL.md`
- `.agents/skills/avax-runtime-performance-cache/SKILL.md`

## Files Updated

- `.agents/how-to/how-to-architecture.md` — Section 13.3 "Builders Rule" extended with 5 new subsections
- `.agents/how-to/how-to-dependency-injection.md` — Section 8 "Fluent API Law" extended with DSL principles, Sections 6.8 extended
- `.agents/how-to/how-to-design-components.md` — Section 6.5.2 extended with builder cross-references
- `.agents/skills/avax-enterprise-codecraft/SKILL.md` — Builder and Assembly Review section added
- `.agents/skills/avax-component-dogfooding/SKILL.md` — Builder and Assembly Dogfooding subsection added
- `.agents/skills/avax-runtime-performance-cache/SKILL.md` — Builder and Assembly Performance Rule section added
- `.agents/how-to/how-to-clean-code.md` — 3 builder anti-patterns added to Section 13
- `.agents/how-to/how-to-coding-standards.md` — 3 builder patterns added to Section 20

## Canonical Rule Locations

**Builder Validity Rule:**
`how-to-architecture.md` Section 13.3.1 — Defines what makes a builder valid vs fake (BLOCKER severity)

**Builder Decision Questions:**
`how-to-architecture.md` Section 13.3.2 — 10 questions agents must answer before creating builders

**Builder Naming Rule:**
`how-to-architecture.md` Section 13.3.3 — Preferred and forbidden builder names

**Builder Method Naming Rule:**
`how-to-architecture.md` Section 13.3.4 — Preferred method names: domain-specific, create(), build(), __invoke()

**Fluent DSL Design Principles:**
`how-to-dependency-injection.md` Section 8.2 — "Public DSLs should remain fluent and human-readable. Internal assembly classes should remain explicit and typed."

**DSL Method Naming Rule:**
`how-to-dependency-injection.md` Section 8.6 — Domain-specific, chainable, self-documenting method names

**Assembly Graph vs Runtime DSL:**
`how-to-dependency-injection.md` Section 8.7 — Distinguishes assembly-time DSLs from runtime DSLs

**Builder Placement Rule:**
`how-to-dependency-injection.md` Section 6.8 — Cross-references and performance considerations

**Builder Review in Codecraft:**
`avax-enterprise-codecraft/SKILL.md` — 10 questions for reviewing builders

**Builder Dogfooding:**
`avax-component-dogfooding/SKILL.md` — Builders must use existing AvaX components

**Builder Performance:**
`avax-runtime-performance-cache/SKILL.md` — Assembly-time builders operate at boot/compile time

**Builder Anti-patterns:**
`how-to-clean-code.md` Section 13 — Fake builder, service-locator builder, god builder bans

**Forbidden Structural Patterns:**
`how-to-coding-standards.md` Section 20 — Builder pattern prohibitions with cross-references

## Cross-Links Added

- `how-to-architecture.md` 13.3.5 cross-references DI, clean code, coding standards, component design
- `how-to-dependency-injection.md` 8.7 cross-references architecture builder rules
- `how-to-dependency-injection.md` 6.8 cross-references architecture builder rules
- `how-to-design-components.md` 6.5.2 cross-references architecture and DI builder rules
- `avax-enterprise-codecraft/SKILL.md` cross-references builder naming and placement rules
- `avax-component-dogfooding/SKILL.md` cross-references builder dogfooding rules
- `avax-runtime-performance-cache/SKILL.md` cross-references builder performance rules
- `how-to-clean-code.md` cross-references architecture builder validity rule
- `how-to-coding-standards.md` cross-references architecture and DI builder rules

## Duplication Avoided

Each rule set lives in exactly one canonical location:

- Builder validity: architecture
- Builder naming: architecture
- Builder method naming: architecture
- Builder decision questions: architecture
- Fluent DSL: DI
- DSL method naming: DI
- Assembly vs runtime DSL: DI
- Builder placement: DI
- Builder review: codecraft skill
- Builder dogfooding: component dogfooding skill
- Builder performance: runtime performance skill
- Builder anti-patterns: clean code
- Forbidden patterns: coding standards

Cross-links ensure agents find all related rules without duplicating content.

## Architecture Rationale

Builders are valid only when they own a real cohesive assembly responsibility. Fake builders that merely hide long constructors, bypass DI gates, or act as service locators are forbidden. God builders that assemble unrelated capabilities violate single responsibility. Builders belong in `Configuration/Builders/` and operate at composition time, not runtime.

## DSL Rationale

Public DSLs must be fluent and human-readable. Internal assembly classes must be explicit and typed. DSL method names should be domain-specific, chainable, and self-documenting. The DSL should read like a sentence describing the assembly intent.

## Runtime/Performance Rationale

Assembly-time builders operate at boot/compile/warmup time, not request time. Builders must not cause object churn, use runtime reflection in hot paths, or repeatedly reconstruct graphs. Compiled assembly plans are preferred over repeated runtime assembly decisions.

## Naming Rationale

Builder names should describe the assembly graph they own: `TokenAuthenticationGraph`, `DatabaseConnectionGraph`, `EventDispatcherAssembly`. Forbidden names include generic terms like `AuthBuilderHelper`, `ServiceFactory`, `Manager`. Method names should be domain-specific first, then fall back to `create()`, `build()`, `__invoke()`.

## Final Governance Decision

All 7 rule sets have been integrated across 9 governance documents with proper cross-referencing. No standalone files were created. Duplication was avoided. Each rule lives in its canonical location. Cross-links ensure discoverability. The builder/assembly graph/DSL governance is now part of the mandatory AvaX governance ecosystem.
