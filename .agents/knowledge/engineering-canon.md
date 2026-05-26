# Engineering Canon

Status: CANONICAL_SUPPORT

## Purpose

The engineering canon maps durable software engineering principles into AvaX governance that agents can execute.

Every durable principle must map to at least one of:

- governance rule
- how-to document
- skill
- checker
- review question
- evidence requirement
- accepted manual review

If a principle has no execution path, it is educational context only and must not be claimed as enforced.

## Source Families

| Source Family | Governance Use |
|---|---|
| Scenario and Requirements Discovery | Prevent vague implementation from task titles. |
| Domain and Business Modeling | Keep folders and names grounded in business meaning. |
| Coupling and Modularity | Make dependency decisions visible and justified. |
| Architecture Decisions and Fitness Functions | Turn architecture intent into executable or reviewable guardrails. |
| Data, State, and Distributed Correctness | Require consistency, mutation, lifecycle, and failure semantics. |
| Software Construction | Make code readable, cohesive, and behavior-oriented. |
| Patterns and AntiPatterns | Use patterns for fit, detect recurring failure shapes. |
| Pragmatic Automation | Automate repeated checks without hiding judgment. |

## Canonical Agent Laws

| Law | Meaning | Enforcement |
|---|---|---|
| No Scenario, No Implementation | Behavior work needs actor, goal, guarantees, and failure paths. | `how-to-scenario-input.md`, `check-scenario-input.php` |
| No Architecture Without Trade-Offs | Architecture decisions must state forces and consequences. | architecture how-to docs, fitness evidence |
| No Boundary Without Coupling Reasoning | Boundaries must explain what coupling is accepted and why. | `how-to-coupling-governance.md`, `check-coupling-decisions.php` |
| No Data Mutation Without Correctness Semantics | State changes require consistency, failure, and recovery semantics. | data/security/performance how-to docs |
| No Pattern Without Problem Fit | Patterns are tools, not decoration. | `how-to-design-patterns.md`, anti-pattern review |
| No Refactor Without Behavioral Safety | Refactors need characterization and regression proof. | `how-to-refactoring.md` |
| No Green Without Evidence | Status claims require current command output. | AGENTS.md, SDLC runners |
| No Baseline Excuse for New Code | Legacy baselines do not excuse changed-scope violations. | gate adoption policy |
| No Documentation Theater | Docs must own decisions or local explanation, not repeat code. | self-explaining architecture governance |
| No AI Autonomy Without Stop Conditions | Autonomous work must know when to continue and when to stop. | AGENTS.md, SDLC automation |

## Enforcement Mapping

| Principle Area | How-To | Skill | Checker | Evidence |
|---|---|---|---|---|
| Scenario input | `.agents/how-to/modeling/how-to-scenario-input.md` | `engineering-canon` | `check-scenario-input.php` | `scenario-input.md` |
| Domain discovery | `.agents/how-to/modeling/how-to-domain-discovery.md` | `engineering-canon` | manual review | `domain-discovery.md` |
| Coupling | `.agents/how-to/architecture/how-to-coupling-governance.md` | `engineering-canon` | `check-coupling-decisions.php` | `coupling-decision.md` |
| Fitness functions | `.agents/how-to/architecture/how-to-architecture-fitness-functions.md` | `sdlc-automation` | `check-architecture-fitness-functions.php` | `architecture-fitness-functions.md` |
| Construction | `.agents/how-to/implementation/how-to-software-construction.md` | `engineering-canon` | `check-antipatterns.php` | `construction-checklist.md` |
| Refactoring | `.agents/how-to/implementation/how-to-refactoring.md` | `engineering-canon` | changed-scope validation | `refactoring-safety.md` |
| Patterns | `.agents/how-to/implementation/how-to-design-patterns.md` | `engineering-canon` | anti-pattern review | `pattern-decision.md` |
| Anti-patterns | `.agents/how-to/verification/how-to-antipattern-detection.md` | `sdlc-automation` | `check-antipatterns.php` | `antipattern-review.md` |

## Source Principle Links

- `.agents/knowledge/source-principles/software-architecture-hard-parts.md`
- `.agents/knowledge/source-principles/designing-data-intensive-applications.md`
- `.agents/knowledge/source-principles/clean-code-code-complete.md`
- `.agents/knowledge/source-principles/pragmatic-programmer.md`
- `.agents/knowledge/source-principles/domain-driven-design.md`
- `.agents/knowledge/source-principles/use-cases-domain-storytelling.md`
- `.agents/knowledge/source-principles/antipatterns-refactoring-patterns.md`

