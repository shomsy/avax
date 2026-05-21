# Hard Enterprise OOP Boundary Governance — Evidence Summary

## Mission

Add Hard Enterprise OOP Boundary Rules to AvaX governance.

Scope: governance/docs/skills only. No production PHP. No Identity refactor.

## Rules Added

### 1. Horizontal Blindness / Outward-Only Dependency Law

Siblings don't depend on siblings. Coordination flows through a parent orchestrator.

Example: `AuthenticationGateway` coordinates `CredentialAuthority` and `SessionRegistry`. Forbidden: `CredentialAuthority` directly depends on `SessionRegistry`.

### 2. Boundary Value Object Rule

No raw primitives (string, int, array) cross subsystem boundaries. Examples: `EmailAddress`, `PlainPassword`, `TenantId`, `UserId`, `TokenId`, `PermissionName`, `RoleName`, `ClientId`, `SessionId`, `AuthContextId`.

### 3. Command/Query Clarity Rule

No method both mutates state and returns data. Split into separate command and query.

### 4. HLD/LLD Mirror Rule

Architecture map must match code tree. If architecture says A coordinates B and C, code must not wire B directly to C.

### 5. Single Preferred Entry Rule

One gateway per subsystem. Multiple entry points create unstable API and attack surface.

## Files Changed

### Canonical Placement (Primary Rules)

- `.agents/how-to/how-to-design-components.md`
  - ADDED Section 31: Hard Enterprise OOP Boundary Rules
  - All 5 rules with full detail, examples, GREEN/YELLOW/RED criteria

- `.agents/how-to/how-to-architecture.md`
  - ADDED Section 54: Hard Enterprise OOP Boundary Rules
  - Compact architecture-level versions of all 5 rules

### Cross-References

- `.agents/how-to/how-to-architecture-extension-with-ddd.md`
  - ADDED Section 57: Hard Enterprise OOP Boundary Cross-Reference
  - Links to design-components.md Section 31 and architecture.md Section 54
  - Notes complementarity with DDD bounded context and aggregate boundaries

- `.agents/how-to/how-to-clean-code.md`
  - ADDED Section 5.4.2: Hard Enterprise OOP Boundary Cross-Reference
  - Links to canonical placements
  - Lists violations produced by rule breaches

- `.agents/how-to/how-to-dependency-injection.md`
  - ADDED Section 16.1: Hard Enterprise OOP Boundary Cross-Reference
  - Maps each OOP rule to DI governance implications
  - Covers ServiceProvider wiring, object graph assembly, gateway routing

### Skill Updates

- `.agents/skills/avax-enterprise-codecraft/SKILL.md`
  - ADDED Hard Enterprise OOP Boundary Gate
  - Evaluates all 5 rules for every production-code change
  - Classifies violations as BLOCKER/HIGH/ACCEPTED_YELLOW

- `.agents/skills/avax-component-dogfooding/SKILL.md`
  - ADDED Hard Enterprise OOP Boundary Cross-Reference
  - Maps rules to component dependency paths
  - Covers horizontal coupling, value object boundaries, gateway usage

- `.agents/skills/avax-security-threat-model/SKILL.md`
  - ADDED Hard Enterprise OOP Boundary Security Cross-Reference
  - Security-specific interpretation of all 5 rules
  - Covers credential wrapping, auth gateway control, tampering prevention

- `.agents/skills/avax-api-compatibility-contract/SKILL.md`
  - ADDED Hard Enterprise OOP Boundary API Cross-Reference
  - Maps rules to public API stability
  - Covers entry point control, contract clarity, versioning

## Governance Precedence

These rules are placed at:

- design-components.md Section 31 (canonical component-level rules)
- architecture.md Section 54 (canonical architecture-level rules)
- clean-code.md Section 5.4.2 (clean-code summary)
- dependency-injection.md Section 16.1 (DI-specific interpretation)
- architecture-extension-with-ddd.md Section 57 (DDD cross-reference)

They complement existing rules:

- Recursive Subsystem Rule (design-components.md Section 30)
- Builder/Factory/Graph Avoidance
- Fluent Class API Rule
- Class and File Naming Discipline
- Component Dogfooding Rule
- Security Threat Model Rule
- API Compatibility Contract Rule

## Validation

- No production PHP code changed
- No Identity refactor code changed
- All changes are governance documentation only
- Rule placement follows precedence established by prior governance missions
- Cross-references point to canonical placements consistently

## Commit

`docs(governance): add hard enterprise OOP boundary rules`
