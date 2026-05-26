# Content Depth Review

Task: Engineering Canon Convergence content depth hardening.

Branch: governance/engineering-canon-convergence

## Source Principle Files

| File | Old Weakness Fixed | Sections Added Or Deepened | Remaining Weakness |
|---|---|---|---|
| `.agents/knowledge/source-principles/software-architecture-hard-parts.md` | Executive-summary depth only. | trade-offs, coupling dimensions, modularity drivers, decomposition matrix, distributed boundary risk, ADR criteria, fitness taxonomy, stop conditions | full automation remains outside source doc |
| `.agents/knowledge/source-principles/designing-data-intensive-applications.md` | Data correctness principles were too shallow. | reliability, scalability, system of record, derived data, idempotency, retry, ordering, transactions, consistency, outbox/inbox, reconciliation | dedicated data checker not implemented |
| `.agents/knowledge/source-principles/clean-code-code-complete.md` | Construction guidance lacked operational failure modes. | prerequisites, naming, functions, cohesion, defensive programming, comments, testing, debugging, integration, tuning, agent failure modes | cohesion still requires manual review |
| `.agents/knowledge/source-principles/pragmatic-programmer.md` | Automation and evidence principles were not concrete enough. | DRY as knowledge, orthogonality, tracer bullets, prototypes, broken windows, contract, automation, stop conditions | no additional checker beyond SDLC runners |
| `.agents/knowledge/source-principles/domain-driven-design.md` | DDD could be misread as folder taxonomy. | subdomains, contexts, language, anticorruption, invariants, DDD folder theater warning, local docs, modeling investment | context map quality remains manual |
| `.agents/knowledge/source-principles/use-cases-domain-storytelling.md` | Scenario and story rules lacked examples and rubric. | actors, goals, guarantees, numbered extensions, quality rubric, story basics, Mermaid guidance, examples | parser not implemented |
| `.agents/knowledge/source-principles/antipatterns-refactoring-patterns.md` | Anti-pattern and refactor mapping was incomplete. | full anti-pattern list, pattern intent/consequences/abuse, characterization tests, safe refactoring, severity and checker mapping | semantic smell review remains manual |

## How-To Files

| File | Old Weakness Fixed | Sections Added Or Deepened | Remaining Weakness |
|---|---|---|---|
| `.agents/how-to/modeling/how-to-scenario-input.md` | Structure existed but weak examples/rubrics. | required/optional table, mechanical exceptions, rubric, scenario examples, extension examples, sensitivity examples, checklist, report schema | natural-language scenario quality remains manual |
| `.agents/how-to/modeling/how-to-domain-discovery.md` | Domain modeling rules lacked decision matrix. | bounded context matrix, term conflicts, dictionary format, context map relationships, invariant placement, translation examples, domain story example | automated term conflict detection not implemented |
| `.agents/how-to/architecture/how-to-coupling-governance.md` | Coupling types were listed without decision detail. | coupling matrix, acceptable/unacceptable examples, legitimacy tests, event trade-offs, temporal coupling, accepted debt format | deep dependency graph checker not implemented here |
| `.agents/how-to/architecture/how-to-architecture-fitness-functions.md` | Fitness functions were defined but not operational enough. | taxonomy, ADR conversion, manual-review criteria, mode semantics, examples, hard rule, exception format | full/baseline adoption remains future work |
| `.agents/how-to/implementation/how-to-software-construction.md` | Construction checks lacked rubrics and anti-examples. | prerequisites, before-code checklist, complexity heuristic, cohesion/dependency rubrics, error matrix, test rubric, shallow anti-examples, self-review | semantic construction review remains manual |
| `.agents/how-to/implementation/how-to-refactoring.md` | Refactor rules lacked distinctions and sequences. | refactor/rewrite/migration distinction, characterization examples, smell table, safe sequence, rollback, public API/data/security/runtime safety | no dedicated refactor checker |
| `.agents/how-to/implementation/how-to-design-patterns.md` | Pattern guidance lacked consequence review. | decision rubric, problem-fit checklist, consequence checklist, abuse examples, usage examples, when not to use, naming and exception format | pattern fit remains manual |
| `.agents/how-to/verification/how-to-antipattern-detection.md` | Dictionary and changed-scope enforcement were incomplete. | complete dictionary list, symptom/consequence/refactor table, AI failure modes, enforcement, automated/manual split, exception format | semantic anti-pattern review remains manual |
| `.agents/how-to/verification/how-to-sdlc-automation.md` | Runtime/reporting semantics needed more exactness. | runtime detection, Composer vs PHP guidance, status semantics, failure classification, evidence table, no vague language, actual-changes pack rule | environment risk remains documented |
| `.agents/how-to/verification/how-to-sdlc-runners.md` | Runner status was not strict enough for review. | failure classification, evidence table, actual-changes pack rule, no vague language | local plain-php wrapper remains non-canonical |
