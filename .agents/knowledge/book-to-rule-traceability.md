# Book To Rule Traceability

Status: CANONICAL_SUPPORT

This file maps source-informed engineering principles to local AvaX governance. It is not a book summary. It is the trace from principle to rule, how-to, skill, checker, evidence, automation level, and remaining gap.

## Traceability Matrix

| Source Family | Principle | Concrete Governance Rule | How-To Path | Skill Path | Checker Path | Evidence Template | Automation Level | Severity | Current Status | Remaining Gap |
|---|---|---|---|---|---|---|---|---|---|---|
| Writing Effective Use Cases | Behavior starts with actor goal, boundary, guarantees, and failure paths. | No Scenario, No Implementation for production behavior. | `.agents/how-to/modeling/how-to-scenario-input.md` | `.agents/skills/engineering-canon/SKILL.md` | `tooling/governance/check-scenario-input.php` | `.agents/templates/evidence/scenario-input.md` | AUTOMATED_NOW | HIGH | changed-mode enforced | full/baseline modes intentionally fail until implemented |
| Domain Storytelling | Model actors, activities, work objects, and handoffs before naming structure. | Domain discovery required for new contexts and major flow modeling. | `.agents/how-to/modeling/how-to-domain-discovery.md` | `.agents/skills/engineering-canon/SKILL.md` | manual review, future domain checker | `.agents/templates/evidence/domain-discovery.md` | SEMI_AUTOMATED | HIGH | template and how-to present | no automated domain story parser |
| Learning Domain-Driven Design | Bounded context owns meaning; external models are translated. | No DDD folder theater; contexts require language and invariant evidence. | `.agents/how-to/modeling/how-to-domain-discovery.md` | `.agents/skills/engineering-canon/SKILL.md` | manual review, anti-pattern checker for generic folders | `.agents/templates/evidence/domain-discovery.md` | SEMI_AUTOMATED | HIGH | how-to and dictionary rules present | context-map validation remains manual |
| Balancing Coupling | Coupling is a design choice with trade-offs, direction, and ownership. | Coupling decision evidence required for boundary-sensitive changes. | `.agents/how-to/architecture/how-to-coupling-governance.md` | `.agents/skills/engineering-canon/SKILL.md` | `tooling/governance/check-coupling-decisions.php` | `.agents/templates/evidence/coupling-decision.md` | AUTOMATED_NOW | HIGH | changed-mode enforced | full/baseline modes intentionally fail until implemented |
| Software Architecture: The Hard Parts | Architecture is trade-offs validated by fitness functions. | Governance and architecture changes require fitness evidence. | `.agents/how-to/architecture/how-to-architecture-fitness-functions.md` | `.agents/skills/engineering-canon/SKILL.md` | `tooling/governance/check-architecture-fitness-functions.php` | `.agents/templates/evidence/architecture-fitness-functions.md` | AUTOMATED_NOW | HIGH | strict changed-mode enforced | baseline adoption not implemented |
| Patterns of Enterprise Application Architecture | Patterns solve contextual forces and create consequences. | Pattern use requires problem-fit and simpler-option evidence. | `.agents/how-to/implementation/how-to-design-patterns.md` | `.agents/skills/engineering-canon/SKILL.md` | `tooling/governance/check-enterprise-application-boundaries.php` | `.agents/templates/evidence/enterprise-application-boundary.md` | AUTOMATED_NOW | HIGH | verified | none |
| Designing Data-Intensive Applications | Reliability, retries, ordering, idempotency, and derived data must be explicit. | Data mutation requires correctness semantics and failure evidence. | `.agents/knowledge/source-principles/designing-data-intensive-applications.md` | `.agents/skills/engineering-canon/SKILL.md` | `tooling/governance/check-data-correctness-evidence.php` | `.agents/templates/evidence/data-correctness.md` | AUTOMATED_NOW | HIGH | verified | none |
| Clean Code | Construction quality depends on meaningful names, cohesion, small routines, and clear failures. | Vague construction and hidden dependency lookup are findings. | `.agents/how-to/implementation/how-to-software-construction.md` | `.agents/skills/engineering-canon/SKILL.md` | `tooling/governance/check-antipatterns.php` | `.agents/templates/evidence/construction-checklist.md` | AUTOMATABLE | MEDIUM | naming and service locator signals automated | cohesion still requires manual review |
| Code Complete | Construction is design work with prerequisites, reviews, defensive clarity, and tests. | Construction preflight and self-review required for production code. | `.agents/how-to/implementation/how-to-software-construction.md` | `.agents/skills/engineering-canon/SKILL.md` | `tooling/governance/check-construction-checklist.php` | `.agents/templates/evidence/construction-checklist.md` | AUTOMATED_NOW | MEDIUM | verified | none |
| The Pragmatic Programmer | Automation, reversibility, tracer bullets, and evidence prevent drift. | SDLC runners are canonical; no vague final evidence. | `.agents/how-to/verification/how-to-sdlc-automation.md` | `.agents/skills/sdlc-automation/SKILL.md` | `tooling/sdlc/validate-agent-task.php` | task-specific evidence table | AUTOMATED_NOW | HIGH | runners connected | environment still requires PHP 8.4 command discipline |
| GoF Design Patterns | Pattern intent and consequences matter more than naming. | Pattern names are not decoration; pattern use requires evidence. | `.agents/how-to/implementation/how-to-design-patterns.md` | `.agents/skills/engineering-canon/SKILL.md` | manual review, anti-pattern checker | `.agents/templates/evidence/pattern-decision.md` | MANUAL_REVIEW | MEDIUM | how-to present | judgment remains manual |
| Dive Into Design Patterns | Pattern examples are educational, not authority. | Use patterns only with local problem fit. | `.agents/how-to/implementation/how-to-design-patterns.md` | `.agents/skills/engineering-canon/SKILL.md` | manual review | `.agents/templates/evidence/pattern-decision.md` | EDUCATIONAL | LOW | support material only | no additional automation planned now |
| Dive Into Refactoring | Refactoring preserves behavior through small safe steps. | Refactor requires behavior preservation evidence. | `.agents/how-to/implementation/how-to-refactoring.md` | `.agents/skills/engineering-canon/SKILL.md` | `tooling/governance/check-refactoring-safety.php` | `.agents/templates/evidence/refactoring-safety.md` | AUTOMATED_NOW | HIGH | verified | none |
| AntiPatterns | Recurring bad solutions need dictionary, symptoms, fixes, and severity. | Anti-pattern dictionary complete and changed-scope checker required. | `.agents/how-to/verification/how-to-antipattern-detection.md` | `.agents/skills/engineering-canon/SKILL.md` | `tooling/governance/check-antipatterns.php` | `.agents/templates/evidence/antipattern-review.md` | AUTOMATED_NOW | HIGH | dictionary exact list enforced | semantic smell review remains manual |
| Pro Asynchronous Programming | Runtime/concurrency behavior must name lifecycle, ordering, and cleanup. | Runtime-sensitive scenarios require worker/concurrency evidence. | `.agents/how-to/runtime/how-to-concurrency-runtime-safety.md` | `.agents/skills/engineering-canon/SKILL.md` | `tooling/governance/check-runtime-concurrency-safety.php` | `.agents/templates/evidence/runtime-concurrency-safety.md` | AUTOMATED_NOW | HIGH | verified | none |

## Classification Legend

Rule classification: BLOCKER, HIGH, MEDIUM, LOW, ACCEPTED_EXCEPTION.

Automation classification: AUTOMATED_NOW, AUTOMATABLE, SEMI_AUTOMATED, MANUAL_REVIEW, EDUCATIONAL.

## Current Enforcement Boundary

Changed-scope automation is the hard enforcement boundary for this convergence pass. Full and baseline modes must not silently pass where they are not implemented.

## Remaining Traceability Gaps

- Domain story quality remains manual.
- Pattern fit remains manual beyond obvious anti-pattern signals.
- Full and baseline mode adoption remains future work for the new engineering-canon checkers.

