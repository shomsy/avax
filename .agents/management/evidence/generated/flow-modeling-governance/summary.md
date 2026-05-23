# Evidence: Add AvaX Flow Modeling and Public String Selector Governance

## Stage
Governance documentation creation

## Status
GREEN

## Files Changed

| File | Action | Reason |
|------|--------|--------|
| `.agents/how-to/modeling/how-to-model-flows.md` | Created | New flow modeling governance document with 16 sections |
| `.agents/how-to/00-reading-order.md` | Modified | Removed MISSING status for how-to-model-flows.md, updated modeling folder note |
| `.agents/how-to/README.md` | Modified | Added modeling/ folder entry to governance map |

## Document Structure

Created `how-to-model-flows.md` with all 16 required sections:

1. **Purpose** — Defines flow modeling as use-case/command/application-level behavior
2. **Core Flow Rule** (BLOCKER) — Flows answer "What happens from start to finish?"
3. **Runtime API Rule** (BLOCKER) — Public APIs must be short, intention-revealing, use-case oriented
4. **Fluent API Rule** (HIGH) — Fluent means natural at correct abstraction, not chain length
5. **Flow Mapping Rule** (BLOCKER) — Every Flow must map to real behavior with trigger/input/output
6. **Complex Named Flow Rule** (HIGH) — Multi-step Flows named for complete action, not internal phases
7. **Public String Selector Rule** (BLOCKER) — String selectors are first-class DX with 6 requirements
8. **Flow Method Naming Rule** (HIGH) — Domain verbs over generic execute/handle/process
9. **Input Rule** (HIGH) — Single input object for complex data, variadic forbidden for domain inputs
10. **Configuration Rule** (BLOCKER) — String selector config belongs in Configuration/Builders/ServiceProvider
11. **Design Pattern Rule** (HIGH) — Behavioral mapping over technical pattern mapping
12. **PublicSurface Rule** (BLOCKER) — PublicSurface receives, delegates, accepts natural input
13. **Classification Rules** — BLOCKER/HIGH/MEDIUM/LOW severity classification with specific findings
14. **Component Examples** — Auth/Identity, Tokens, Access, Storage, ExternalIdentity concrete examples
15. **Source Principles** — Why Flows over UseCases, Strings over Enums, Verbs over execute(), Config over Runtime
16. **Relationship to Other Governance** — Cross-references to clean-code.md, design-components.md, runtime-composition.md

Plus Appendices A (Quick Reference table) and B (Decision Test).

## Design Decisions

### String Selectors as First-Class
- Explicitly declared first-class DX, not second-class citizens
- Six mandatory requirements: registry validation, fail-fast, observable errors, no service locator, explicit registration, test coverage
- Container builds/resolves object graph but is NOT the semantic selector registry
- Forbidden patterns documented with GOOD/BAD examples

### Intent-First Fluent API
- Aligned with existing clean-code.md §5.4.1 Intent-First Fluent API Rule
- Fluent means "natural at correct abstraction", not "short chain length"
- Three-step fluent chain reading like intent beats one-step call exposing internals
- String selectors naturally compose with fluent APIs

### Flow Naming Discipline
- Flow names as verbs (RegisterUser, ChangePassword), not nouns (UserFlow, RequestHandler)
- Method names as domain verbs (login, dispatch, publish), not generic (execute, handle, process)
- Technical pattern folders forbidden (Commands/, Queries/, Handlers/, Services/)

### Severity Classification
- BLOCKER: Flow named as technical category, generic public API methods, service locator string selectors, silent unknown selectors, PublicSurface owning machinery
- HIGH: Variadic domain inputs, unregistered selectors, no fail-fast validation, exposed fluent internals
- MEDIUM: Poor error messages, missing flow documentation, no input normalization
- LOW: Line count, naming inconsistency, missing examples

## Public API Impact
NONE — This is governance documentation only. No production code changed.

## Validation

### Structural Validation
- [x] Document exists at canonical location: `.agents/how-to/modeling/how-to-model-flows.md`
- [x] All 16 required sections present
- [x] Reading order updated: `00-reading-order.md` no longer shows MISSING for this file
- [x] Governance map updated: `README.md` includes modeling/ folder entry
- [x] Cross-references established: §16 references clean-code.md, design-components.md, runtime-composition.md
- [x] Examples cover required components: Auth/Identity, Tokens, Access, Storage, ExternalIdentity
- [x] Severity classification present: BLOCKER/HIGH/MEDIUM/LOW with specific findings
- [x] Source principles documented: 6 rationale sections explaining design choices

### Governance Consistency
- [x] Aligns with AGENTS.md §12: Folder = Flow or Capability, Unit = Responsibility, Function = Exact Action
- [x] Aligns with AGENTS.md §16: Flow and Capability Rule, UseCases forbidden
- [x] Aligns with AGENTS.md §17: PublicSurface receives and delegates
- [x] Aligns with clean-code.md §5.4.1: Intent-First Fluent API Rule
- [x] Aligns with design-components.md §6.3-6.4: Flows/ and Capabilities/ rules
- [x] Aligns with runtime-composition.md §1-2: No runtime assembly, configuration-time registration
- [x] Normative language consistent: MUST/MUST NOT/BLOCKER/HIGH/MEDIUM/LOW used correctly

### File Hygiene
- [x] No forbidden folder names in examples (Services, Helpers, Utils, Common, etc.)
- [x] All code examples use PHP syntax
- [x] GOOD/BAD pattern consistent with existing governance documents
- [x] No emojis in document
- [x] No stale references to non-existent files

## Remaining Risks

| Risk | Severity | Notes |
|------|----------|-------|
| how-to-domain-discovery.md still MISSING | N/A | Planned future document |
| how-to-scenario-input.md still MISSING | N/A | Planned future document |
| No automated governance validation for flow naming | MEDIUM | Would require static analysis tooling not yet built |
| String selector registry pattern not yet proven in production code | YELLOW | Document defines the rule; production evidence will come when components implement it |

## Skills Applied
- avax-enterprise-codecraft: Applied design evidence requirements before creating document
- avax-api-compatibility-contract: Documented NONE public API impact
- avax-component-dogfooding: Examples reference existing Auth component structure patterns
- avax-security-threat-model: String selector security requirements (no dynamic class discovery, fail-fast, no service locator)

## Next Allowed Action
Commit governance documentation. No production code changes require validation commands.
