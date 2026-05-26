# How-To System Hardening Report

This report documents the audit and operational readiness of the `how-to` documentation corpus in AvaX.

## Operational Audit Criteria
Every `how-to` document in the `.agents/how-to/` directory has been verified against the following criteria:
1. **Actionable & Action-Oriented**: Defines clear directives, not just passive concepts.
2. **Reviewer & Agent Guidance**: Explains what the agent must provide and what the human reviewer must verify.
3. **No Vague Language**: Avoids generalities; establishes concrete, measurable rules.
4. **Checker & Template Reference**: Direct mapping to the corresponding checker script in `tooling/governance/` and evidence template in `.agents/templates/evidence/`.

## Hardening of Core Governance Domains

### 1. Modeling
- **How-Tos**: `how-to-domain-discovery.md`, `how-to-scenario-input.md`, `how-to-model-flows.md`
- **Scope**: Requirements definition, domain actor/story modeling, and input behavior.
- **Rules**: Production behavior modifications require scenario input files. Non-compliance is HIGH.
- **Checker**: `check-scenario-input.php`
- **Template**: `scenario-input.md`

### 2. Architecture
- **How-Tos**: `how-to-coupling-governance.md`, `how-to-architecture-fitness-functions.md`, `how-to-adr-tradeoff-governance.md`, `how-to-data-correctness.md`, `how-to-enterprise-application-patterns.md`
- **Scope**: Boundary crossing, architectural decisions, fitness function execution, and enterprise patterns.
- **Rules**: Fitness changes require fitness-function validation. Architecture changes require ADR tradeoffs. Boundary-sensitive imports require coupling evidence.
- **Checkers**: `check-coupling-decisions.php`, `check-architecture-fitness-functions.php`, `check-adr-tradeoff-evidence.php`, `check-enterprise-application-boundaries.php`
- **Templates**: `coupling-decision.md`, `architecture-fitness-functions.md`, `adr-tradeoff-decision.md`, `enterprise-application-boundary.md`

### 3. Implementation
- **How-Tos**: `how-to-software-construction.md`, `how-to-refactoring.md`, `how-to-design-patterns.md`, `how-to-clean-code.md`
- **Scope**: Code style, naming, code organization, refactoring preservation, and OOP patterns.
- **Rules**: Construction checklist is mandatory for production edits. Refactorings require regression-free behavior proof.
- **Checkers**: `check-construction-checklist.php`, `check-refactoring-safety.php`, `check-antipatterns.php`
- **Templates**: `construction-checklist.md`, `refactoring-safety.md`, `pattern-decision.md`

### 4. Runtime
- **How-Tos**: `how-to-concurrency-runtime-safety.md`
- **Scope**: Long-lived worker safety, concurrency state, connection pooling.
- **Rules**: Any worker-active files require runtime concurrency evidence. Non-compliance is HIGH.
- **Checker**: `check-runtime-concurrency-safety.php`
- **Template**: `runtime-concurrency-safety.md`

### 5. Verification
- **How-Tos**: `how-to-sdlc-automation.md`, `how-to-sdlc-runners.md`, `how-to-create-ai-code-review-packs.md`
- **Scope**: Validation suite automation, test metrics, and review package packaging.
- **Rules**: Final submissions must generate review packs using the verified wrapper.
- **Checkers**: `validate-changed.php`, `validate-governance.php`, `create-actual-changes-review-pack.php`

## Conclusion
The `how-to` system is operationally ready and aligned with the control plane checkers.
