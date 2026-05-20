# Component Dogfooding Skill — Evidence Summary

Date: 2026-05-20
Executor: Qoder
Branch: main
Type: governance/skill authoring

## Files Created

1. `.agents/skills/avax-component-dogfooding/SKILL.md`
   - Component dogfooding enforcement skill
   - Defines core rule: AvaX components must reuse existing AvaX capabilities
   - Allowed dependency paths: PublicSurface, approved Capability APIs, Configuration/Assembly/Provider, adapter boundaries, Foundation primitives
   - Forbidden dependency paths: private internals, circular deps, hidden service locators, global app() shortcuts, raw IO when component should own it, duplicated serializers/parsers/validators, local mini-frameworks
   - Raw PHP Exception Rule with required evidence fields
   - Component Dependency Review required before commit
   - Dogfooding Classification: DOGFOODS_EXISTING_COMPONENTS through NEEDS_COMPONENT_DESIGN_REVIEW
   - Blocks commit on DUPLICATES_EXISTING_CAPABILITY, BYPASSES_COMPONENT_BOUNDARY, CREATES_CIRCULAR_DEPENDENCY, NEEDS_COMPONENT_DESIGN_REVIEW unless documented as YELLOW

## Files Updated

1. `.agents/skills/index.md`
   - Added routing entry for component change, architecture cleanup, dogfooding, AvaX eats AvaX, filesystem/cache/logging/events/config/security/runtime/HTTP work
   - Added entry in Skill Files table

2. `.agents/skills/avax-enterprise-codecraft/SKILL.md`
   - Added Component Dogfooding Gate section
   - Added `avax-component-dogfooding` to required skill loading list
   - Clarified: advanced OOP means high cohesion, low coupling, and first-party component reuse through correct boundaries

3. `.agents/skills/avax-autonomous-backlog-loop/SKILL.md`
   - Updated Per-Slice Flow step 2 to load `avax-component-dogfooding` when component reuse may be involved
   - Updated step 4 to re-run component dogfooding review per slice

4. `.agents/skills/avax-enterprise-remediation/SKILL.md`
   - Updated Bootloader Rule to route component/refactor/architecture tasks to `avax-component-dogfooding`
   - Added mandate: agents must not bypass existing AvaX components with raw PHP unless exception is documented

5. `AGENTS.md`
   - Added section 24.3: Component Dogfooding Rule
   - Establishes "AvaX components must dogfood AvaX capabilities" as root contract
   - Forbids raw PHP, global helpers, duplicated infrastructure outside Foundation/adapter boundaries
   - Requires dependencies through stable PublicSurface, approved Capability APIs, Configuration/Assembly/Provider boundaries
   - Declares circular component dependencies as blocking findings
   - Requires documentation of component reuse, bypasses, raw primitives, and dependency direction

## New Routing Rules

- Skills index routes: component change, architecture cleanup, dogfooding, AvaX eats AvaX, filesystem/cache/logging/events/config/security/runtime/HTTP work to `avax-component-dogfooding`
- Codecraft skill requires dogfooding review for all production code
- Autonomous loop re-runs dogfooding review per slice
- Remediation bootloader routes component/refactor/architecture tasks to dogfooding skill

## New Dogfooding Gate

- Core Rule: 10 pre-implementation questions about existing capabilities, bypasses, duplication, raw PHP, Foundation/adapter boundaries, dependency direction, coupling, stability, boundary type
- Allowed Paths: 6 approved dependency routes
- Forbidden Paths: 12 forbidden dependency patterns
- Raw PHP Exception: 5 conditions with required evidence (RAW_PRIMITIVE_ALLOWED, reason, owner, risk, mitigation, future replacement plan)
- Classification: 8 categories, 4 blocking unless YELLOW-documented
- Required Evidence: component-dogfooding-review.md plus dependency-boundary.md, HLD, LLD, validation, governance review

## Validation

```
composer validate --no-check-publish: GREEN (./composer.json is valid)
php tooling/governance/check-governance-index-current.php: GREEN (Governance index is current)
php tooling/governance/check-root-evidence-hygiene.php: GREEN (Root Evidence Hygiene PASSED)
```

All validation GREEN.
