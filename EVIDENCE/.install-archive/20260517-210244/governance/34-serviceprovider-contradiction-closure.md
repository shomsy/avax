# ServiceProvider Contradiction Closure

## Problem

The canonical rule in `how-to-dependency-injection.md` §4.0 says:

> *"Every ACTIVE production component with runtime behavior, public API, dependencies, replaceable services, state, I/O,
configuration, or lifecycle ownership MUST have exactly one real ServiceProvider."*

With explicit exempt statuses:

- ROADMAP, SCAFFOLD, LABS_ONLY, EVIDENCE_ONLY, TEST_ONLY, PURE_FOUNDATION, inactive DEPRECATED

But `how-to-coding-standards.md` (line 1157) says the broader:

> *"every component MUST have a ServiceProvider"*

This omits the ACTIVE qualification and exempt statuses, creating ambiguity.

## Resolution Table

| File                                                | Old wording                                                     | Risk                                                                                                                                                    | New wording                                                                                                                                                                                                                                                                                                     | Decision                             |
|-----------------------------------------------------|-----------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|--------------------------------------|
| `.agents/how-to/how-to-coding-standards.md:1157`    | `- every component MUST have a ServiceProvider`                 | Agents may create ServiceProviders for ROADMAP/SCAFFOLD/LABS/EVIDENCE/TEST/PURE_FOUNDATION components. Creates shell ServiceProviders to silence gates. | `- every ACTIVE production component with runtime behavior, public API, dependencies, replaceable services, state, I/O, configuration, or lifecycle ownership MUST have exactly one real ServiceProvider. ROADMAP/SCAFFOLD/LABS_ONLY/EVIDENCE_ONLY/TEST_ONLY/PURE_FOUNDATION/DEPRECATED components are exempt.` | Fixed to match canonical DI doc §4.0 |
| `.agents/how-to/how-to-dependency-injection.md:302` | `Every ACTIVE component MUST have exactly one ServiceProvider.` | Ambiguous — "component" could include area roots.                                                                                                       | No change — canonical rule is correct. Cross-reference §4.0 for full exempt list.                                                                                                                                                                                                                               | No change needed                     |
| `.agents/how-to/how-to-design-components.md`        | No direct ServiceProvider requirement rule (cross-refs DI doc)  | N/A — already consistent                                                                                                                                | No change needed                                                                                                                                                                                                                                                                                                | No change needed                     |
| `.agents/how-to/how-to-production-readiness.md`     | No direct ServiceProvider rule (only test repair mention)       | N/A — already consistent                                                                                                                                | No change needed                                                                                                                                                                                                                                                                                                | No change needed                     |

## Verification

- All documents now agree: ServiceProvider is required for ACTIVE production components only
- Exempt statuses listed in canonical DI doc §4.0
- Gates must check component status, not just ServiceProvider file existence

## Decision

Accepted. This resolves the contradiction without changing the substantive rule.
