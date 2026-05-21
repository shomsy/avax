# Fluent Class API Governance — Evidence Summary

## Status

**GREEN** — Governance-only change. No production PHP code. No tests. No Identity remediation.

## Scope

Added AvaX Fluent Class API, Semantic Property Naming, Technical Dictionary, and Subsystem Size Governance Rules.

## Changes Made

### New File Created

| File | Purpose |
|------|---------|
| `.agents/dictionary/framework-terms.md` | Technical dictionary with 17 initial terms: Runtime, PublicSurface, Capability, Flow, Provider, Builder, Assembly, Graph, DSL, Facade, Policy, Token, Session, Credential, Tenant, Elevation, Risk |

### How-To Documents Updated

| File | Section Added | Content |
|------|---------------|---------|
| `how-to-clean-code.md` | 5.4.2 Fluent Class API Rule | Every class is a fluent API unit, constructor semantic role names, method names serve class API, builder/assembly naming, technical dictionary rule, GREEN/RED criteria |
| `how-to-coding-standards.md` | 12. Fluent Class API and Semantic Property Naming | Semantic property naming, method names serve class API, technical dictionary reference, builder/assembly naming |
| `how-to-code-style.md` | Semantic Constructor Property Naming | Constructor-promoted dependency property naming rule, method naming in classes, technical dictionary reference, GREEN/RED criteria |
| `how-to-design-components.md` | 30. Subsystem Size Governance Rule | Large classes not permanent design, forbidden patterns (dependency bags, fake builders, god objects, service locator wrappers), splitting discipline |
| `how-to-design-components.md` | 31. Rewrite Permission Rule | Rewrite allowed for core subsystems, must be sliced and proven, required evidence |
| `how-to-architecture.md` | 54. Subsystem Size Governance Rule | Architecture-level size governance, review triggers, splitting discipline |
| `how-to-architecture.md` | 55. Rewrite Permission Rule | Architecture-level rewrite permission |
| `how-to-architecture.md` | 56. Fluent Class API Architecture Rule | Constructor property names, method names, builder/assembly naming |
| `how-to-modern-php-attributes-di.md` | 19.1 DI Naming Discipline | Semantic role names for DI, technical dictionary reference |

### Skill Documents Updated

| Skill | Section Added | Content |
|-------|---------------|---------|
| `avax-enterprise-codecraft` | Fluent Class API Gate | Constructor property naming, method naming, call-site readability, technical dictionary |
| `avax-enterprise-codecraft` | Subsystem Size Gate | Large class governance, forbidden patterns |
| `avax-component-dogfooding` | Fluent API Naming in Component Dependencies | Components must use fluent API naming when depending on other components |
| `avax-api-compatibility-contract` | API Compatibility Questions | Added fluent API naming change checks (constructor properties, method names, DSL) |
| `avax-test-evidence-quality` | Required Test Coverage | Added fluent API test requirement |
| `avax-security-threat-model` | Threat Model Questions | Added security naming discipline checks |

## Cross-Reference Map

Canonical placements:

- **Fluent Class API Rule (every class is fluent API unit):** `how-to-clean-code.md` — Section 5.4.2
- **Constructor Semantic Naming:** `how-to-code-style.md` — Semantic Constructor Property Naming
- **Subsystem Size Governance:** `how-to-architecture.md` — Section 54, `how-to-design-components.md` — Section 30
- **Rewrite Permission:** `how-to-architecture.md` — Section 55, `how-to-design-components.md` — Section 31
- **Technical Dictionary:** `.agents/dictionary/framework-terms.md`
- **Builder/Assembly Naming:** `how-to-architecture.md` — Section 13.3, `how-to-dependency-injection.md` — Section 8

Cross-references (avoid duplication):

- `how-to-coding-standards.md` — Section 12 references `how-to-clean-code.md` — 5.4.2
- `how-to-architecture.md` — Section 56 references `how-to-clean-code.md` — 5.4.2
- `how-to-design-components.md` — Section 30 references `how-to-clean-code.md` — 5.4.2
- `how-to-modern-php-attributes-di.md` — 19.1 references `how-to-clean-code.md` — 5.4.2
- All skills reference canonical how-to documents

## Rules Summary

### Rule 1: Every Class Is a Fluent API Unit
Constructor property names, method names, and call-sites must be optimized for readability and intent.

### Rule 2: Constructor Property Names Must Be Semantic Role Names
`$authorization` not `$authorizationEngine`. Short role names when clearer.

### Rule 3: Method Names Must Serve the Class API
Domain action names over mechanical defaults: `requirePermission()`, `allows()`, `denies()` over `execute()`, `build()`, `create()`.

### Rule 4: Builder/Assembly Naming
Folder says technical context. Class says cohesive responsibility/product. Method says exact action/product. Avoid mechanical `Build*`, `*Builder` unless clearer.

### Rule 5: Rewrite Permission
Rewrite-level refactoring allowed for core subsystems when justified. Must be sliced and proven.

### Rule 6: Subsystem/Class Size
Large classes not permanent design. Split by cohesive subsystem. Forbidden: dependency bags, fake builders, god objects, service locator wrappers.

### Rule 7: Technical Dictionary
Technical terms in class names must reference `.agents/dictionary/framework-terms.md` in PHPDoc.

## Validation

- No production PHP code changed
- No tests changed
- No Identity remediation in this pass
- Governance-only change
- All cross-references consistent
- No duplication — canonical placements with cross-references

## Remaining Risk

- No runtime risk (governance-only change)
- Future production-code changes will need to comply with these rules
- Existing code may need future rewrite slices to comply (allowed by Rule 5)

## Next Allowed Action

- Review and commit
- Subsequent task: implement fluent class API rewrite slices for core subsystems (Identity, Auth, etc.)
