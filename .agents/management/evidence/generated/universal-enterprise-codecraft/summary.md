# Universal Enterprise Codecraft Philosophy — Evidence Summary

## Mission

Add AvaX Universal Enterprise Codecraft Philosophy and Hard Boundary Rules into governance canon.

Scope: governance/docs/skills only. No production PHP. No Identity refactor.

## Philosophy Summary

Enterprise-grade quality must also remain readable, intuitive, and human-friendly.

Enterprise-grade does NOT mean: complicated, abstract, ceremonial, pattern-heavy, over-engineered, architecturally theatrical.

Enterprise-grade means: explicit responsibility, low cognitive load, strong boundaries, predictable behavior, safe defaults, testable behavior, clear ownership, readable call-sites, discoverable structure, fluent APIs, maintainable evolution, no hidden complexity.

## Canonical Rule Locations

### Primary Canonical Placement

- `how-to-design-components.md` — Section 30: Universal Enterprise Codecraft Rule
  - 30.1 Enterprise-Grade and Human-Readable
  - 30.2 Human-Readable Enterprise Design
  - 30.3 No Technical Theater Rule
  - 30.4 Fluent Class API Rule
  - 30.5 Cognitive Load Minimization Rule
  - 30.6 Structural Honesty Rule
  - 30.7 Recursive Decomposition Rule
  - 30.8 Readability vs Safety Balance
  - 30.9 Hard Boundary Rules (Horizontal Blindness, Boundary Value Objects, Command/Query Clarity, HLD/LLD Mirror, Single Preferred Entry)
  - 30.10 Naming Direction
  - 30.11 GREEN / YELLOW / RED Criteria

- `how-to-clean-code.md` — Section 25: Universal Enterprise Codecraft Rule
  - Compact clean-code summary of all philosophy principles
  - Hard boundary summary with cross-references to canonical

- `how-to-architecture.md` — Section 55: Universal Enterprise Codecraft Rule
  - Architecture-level interpretation of all philosophy principles
  - 55.1-55.11 covering all concepts at architectural scale
  - Hard architectural boundaries
  - GREEN / YELLOW / RED criteria at scale

## Cross-References

### How-To Files Updated

- `how-to-dependency-injection.md` — Section 16.1: Universal Enterprise Codecraft Cross-Reference
  - DI-specific interpretation: no technical theater in DI, fluent API from providers, cognitive load for dependencies, structural honesty in registration, horizontal blindness in wiring, single preferred entry through gateways

- `how-to-code-review.md` — Section 26: Universal Enterprise Codecraft Review Rule
  - Review checklist for codecraft quality: technical theater, cognitive load, structural honesty, fluent API, horizontal blindness, boundary value objects, command/query clarity, single preferred entry, HLD/LLD mirror

- `how-to-runtime-composition.md` — Section 15: Universal Enterprise Codecraft Cross-Reference
  - Runtime-specific: no theater in runtime, structural honesty, cognitive load (execute don't assemble), fluent runtime entrypoints

### Dictionary Created

- `dictionary/framework-terms.md` — CREATED with 26 terms:
  - Existing terms (18): Runtime, PublicSurface, Capability, Flow, Provider, Builder, Factory, Assembly, Graph, DSL, Facade, Policy, Token, Session, Credential, Tenant, Elevation, Risk
  - New terms (8): Technical Theater, Cognitive Load, Structural Honesty, Fluent API, Gateway, Subsystem, Recursive Decomposition, (existing terms updated with cross-references)

Each term includes: simple explanation, AvaX meaning, allowed usage, forbidden misuse, example names, learning references.

### Skill Updates

- `avax-enterprise-codecraft/SKILL.md`
  - ADDED Universal Enterprise Codecraft Philosophy Gate
  - Agents must: prefer subsystem decomposition, optimize readability/DX, minimize cognitive load, reject technical theater, enforce structural honesty, prefer fluent APIs, avoid fake abstractions
  - Evaluation checklist: technical theater, cognitive load, structural honesty, fluent API, recursive decomposition, readability vs safety
  - GREEN/YELLOW/RED classification

- `avax-component-dogfooding/SKILL.md`
  - ADDED Universal Enterprise Codecraft Philosophy
  - Component reuse must reduce cognitive load, not increase it
  - "Component dogfooding that creates more cognitive load than raw PHP is a design failure"

- `avax-api-compatibility-contract/SKILL.md`
  - ADDED Universal Enterprise Codecraft Philosophy
  - Public API must be fluent, low cognitive load, no technical theater, structurally honest
  - "Public API that requires the consumer to understand internal machinery is a design failure"

- `avax-test-evidence-quality/SKILL.md`
  - ADDED Universal Enterprise Codecraft Philosophy
  - Tests must prove codecraft quality: fluent APIs, thin public surfaces, no horizontal coupling, boundary value objects, command/query separation, single entry point
  - Tests proving only construction trivia are TESTS_TOO_SHALLOW_BLOCKER

- `avax-security-threat-model/SKILL.md`
  - ADDED Universal Enterprise Codecraft Philosophy
  - Security code must be readable to be auditable; auditable to be secure
  - "Security code that is hard to read is hard to audit. Security code that is hard to audit is insecure."

## Hard Boundary Rules Integrated

All five hard boundary rules are present in:

1. **Horizontal Blindness** — design-components.md 30.9.1, architecture.md 55.9.1
2. **Boundary Value Objects** — design-components.md 30.9.2, architecture.md 55.9.2
3. **Command/Query Clarity** — design-components.md 30.9.3, architecture.md 55.9.3
4. **HLD/LLD Mirror** — design-components.md 30.9.4, architecture.md 55.9.4
5. **Single Preferred Entry** — design-components.md 30.9.5, architecture.md 55.9.5

Plus cross-references in: clean-code.md 25.9, code-review.md 26, dependency-injection.md 16.1

## Naming Direction

Avoid: Builder, Factory, Graph, Assembly, DSL, Wiring, Setup, Manager, Service, Helper, Util, Support, AvaX/Avax branding.

Prefer: subsystem names, capability names, product names, domain names, fluent call-sites.

Examples: AuthenticationGateway, CredentialAuthority, SessionRegistry, MfaProtection, PasskeyAccess, ExternalLogin, TenantAccess, PolicyEnforcement, RiskAssessment, TokenAuthority.

Public fluent: App::flow(...), App::identity()->auth(), App::identity()->access()

## Files Reviewed

- AGENTS.md
- .agents/how-to/how-to-design-components.md
- .agents/how-to/how-to-clean-code.md
- .agents/how-to/how-to-architecture.md
- .agents/how-to/how-to-code-style.md
- .agents/how-to/how-to-coding-standards.md
- .agents/how-to/how-to-runtime-composition.md
- .agents/how-to/how-to-dependency-injection.md
- .agents/how-to/how-to-code-review.md
- .agents/how-to/how-to-architecture-extension-with-ddd.md
- .agents/dictionary/framework-terms.md (from recursive-subsystems worktree)
- All 5 target skill files

## Files Updated (13)

1. `.agents/how-to/how-to-design-components.md` — Section 30 (canonical)
2. `.agents/how-to/how-to-clean-code.md` — Section 25
3. `.agents/how-to/how-to-architecture.md` — Section 55
4. `.agents/how-to/how-to-dependency-injection.md` — Section 16.1
5. `.agents/how-to/how-to-code-review.md` — Section 26
6. `.agents/how-to/how-to-runtime-composition.md` — Section 15
7. `.agents/dictionary/framework-terms.md` — CREATED (26 terms)
8. `.agents/skills/avax-enterprise-codecraft/SKILL.md` — Philosophy Gate
9. `.agents/skills/avax-component-dogfooding/SKILL.md` — Philosophy
10. `.agents/skills/avax-api-compatibility-contract/SKILL.md` — Philosophy
11. `.agents/skills/avax-test-evidence-quality/SKILL.md` — Philosophy
12. `.agents/skills/avax-security-threat-model/SKILL.md` — Philosophy
13. `.agents/management/evidence/generated/universal-enterprise-codecraft/summary.md` — This file

## Validation Commands

```bash
composer validate --no-check-publish
php tooling/governance/check-governance-index-current.php
php tooling/governance/check-root-evidence-hygiene.php
git diff --check
```

## Final Decision

Scope: governance/docs/skills only. Zero production PHP changed.

All canonical rules placed. All cross-references consistent. All skills updated. Dictionary created with all required terms.

Status: GREEN — ready for commit.
