# AvaX Governance Index

## Purpose

This file tells agents what to read, in what order, and why.

**AGENTS.md is the root contract.**
**This file is the navigation map.**

## Required Reading Order for Any Code Work

1. `AGENTS.md` - root contract
2. `ARCHITECTURE.md` - architecture north star document
3. `.agents/GOVERNANCE_INDEX.md` - this navigation map
4. `.agents/how-to/README.md` - governance map
5. `.agents/how-to/00-how-to-reading-order.md` - canonical loading order
6. all `.agents/how-to/**/*.md` - complete governance rules
7. all `.agents/skills/**/SKILL.md` - complete local skill contracts
8. `.agents/management/**` - active state, evidence, baselines, learning, memory
9. `CURRENT_TRUTH.md` - project state if present and current
10. `.agents/management/ACTIVE.md` - active stage if present and current
11. `.agents/business-logic/**` - domain meaning
12. relevant source files
13. relevant tests
14. latest relevant reports in `.agents/management/evidence/**`

`EVIDENCE/EXECUTION.md` and `EVIDENCE/recovery-reports/**` are legacy/transitional sources. Read them only when current evidence or tooling explicitly activates them.

## Workspace Routing

The `.agents/` folder is the project-local agent workspace.

| Area                             | Purpose                          | When to Read                                         |
|----------------------------------|----------------------------------|------------------------------------------------------|
| `.agents/how-to/`                | local governance rules           | before implementation, refactor, review, docs, tests |
| `.agents/skills/**`              | executable task playbooks        | when task matches a skill                            |
| `.agents/business-logic/`        | project meaning, domain language | before domain, architecture, naming, recovery        |
| `.agents/management/`            | active stage, TODO, evidence     | before any execution                                 |
| `.agents/management/memories/**` | durable project knowledge        | before architecture, recovery, long work             |
| `.agents/management/learning/**` | lessons from past work           | before repeating similar work                        |
| `.agents/review/`                | prior findings                   | before review                                        |
| `.agents/hooks/`                 | automation touchpoints           | when client supports hooks                           |

## Task Routing

| Task Type                               | Must Read                                                                       |
|-----------------------------------------|---------------------------------------------------------------------------------|
| Architecture/refactor                   | ARCHITECTURE.md, how-to-architecture, how-to-design-components, DDD extension, advanced patterns, how-to-enterprise-application-patterns, how-to-data-correctness, how-to-adr-tradeoff-governance, how-to-concurrency-runtime-safety |
| Events, listeners, CQRS, event sourcing | how-to-events-listeners-event-sourcing-cqrs-realtime, advanced patterns         |
| Component design                        | how-to-design-components, security, performance, production readiness           |
| PHP code                                | coding standards, code style, clean code                                        |
| Modern PHP / attributes / DI            | how-to-modern-php-attributes-di                                                 |
| Unit tests                              | how-to-unit-test, how-to-test-risk-based-behavioral-testing (verification/)     |
| Documentation                           | how-to-document, how-to-write-self-explaining-architecture (documentation/)     |
| Self-explaining architecture            | how-to-write-self-explaining-architecture (documentation/), self-explaining-architecture skill |
| Test quality                            | how-to-test-risk-based-behavioral-testing (verification/), check-shallow-tests.php |
| Security-sensitive code                 | how-to-system-security (verification/)                                          |
| Performance-sensitive code              | how-to-system-performance (verification/)                                       |
| Review                                  | how-to-code-review (verification/) plus all applicable how-to docs              |
| AI review pack creation                 | how-to-create-ai-code-review-packs (verification/)                               |
| Recovery                                | recovery skill, business-logic, memories, reports                               |
| Identity component work                 | components/Identity/docs/                                                       |

## Skill Routing

| User Request Contains                          | Skill                                   |
|------------------------------------------------|-----------------------------------------|
| recover, backup, Framework.txt, Components.txt | `.agents/skills/recovery/SKILL.md`      |
| review, audit, evaluate                        | `.agents/skills/review/SKILL.md`        |
| refactor, rename, move                         | `.agents/skills/refactor/SKILL.md`      |
| test, coverage, phpunit                        | `.agents/skills/testing/SKILL.md`       |
| docs, PHPDoc, how-this-works                   | `.agents/skills/documentation/SKILL.md` |
| validation, green, proof                       | `.agents/skills/validation/SKILL.md`    |
| security                                       | `.agents/skills/security/SKILL.md`      |
| performance, benchmark                         | `.agents/skills/performance/SKILL.md`   |

## Memory and Learning Routing

Agents may read:

- `.agents/management/memories/**` - for durable project context
- `.agents/management/learning/**` - for lessons from past work

Memory is context.
Learning guides decisions.
Validation proves truth.

## Evidence Rule

If a claim is important, it must point to:

- test output
- static analysis output
- audit report
- recovery report
- code pointer
- documented governance rule

**No evidence means not proven.**

## Governance Tooling

The following governance tooling is available:

| Tool                                                     | Purpose                                                          | Output Format        |
|----------------------------------------------------------|------------------------------------------------------------------|----------------------|
| `tooling/governance/check-governance-canonical-truth.php` | Verifies canonical truth: no duplicates, no shadow governance, reading order integrity, GOVERNANCE_INDEX.md reference integrity | console |
| `tooling/governance/check-governance-leakage.php`        | Recursively scans generic governance docs for project-specific leakage (AvaX, component paths, tooling paths) | console + severity classification |
| `tooling/governance/check-governance-index-current.php`  | Validates governance index against actual filesystem | console |
| `tooling/governance/check-stage-lock.php`                | Checks stage lock compliance | console |
| `tooling/governance/check-self-explaining-architecture.php` | Validates self-explaining architecture: orphan docs, stale markers, diagram detection | console + report |
| `tooling/validation/check-phpstan-baseline.php`        | Enforces PHPStan in full, baseline, and changed modes during phased legacy adoption | console + baseline JSON |
| `tooling/governance/check-root-evidence-hygiene.php`     | Checks evidence directory hygiene | console |
| `tooling/governance/check-how-to-document-structure.php` | Validates how-to document structure | console |
| `tooling/governance/generate-review-packs.php`           | Generates timestamped AI review packs with TREE.txt, STATS.md, REVIEW_CONTEXT.md, immutable run id, root/ZIP metadata consistency, manifest validation, and template fragment detection | console + ZIP packs |
| `tooling/governance/validate-review-pack-integrity.php`  | Independently validates generated review pack folders against actual ZIP contents, manifest counts, STATS metadata, forbidden entries, and stale/run-id mismatch risk | console |
| `tooling/governance/create-actual-changes-review-pack.php` | Creates focused archives containing changed and untracked files for current working-tree review, excluding old archives and unsafe paths | console + ZIP/TAR packs |
| `tooling/governance/check-intrusive-coupling.php`        | Detects intrusive coupling patterns | console |
| `tooling/governance/check-large-unit-thresholds.php`     | Checks unit size thresholds | console |
| `tooling/governance/check-serviceprovider-governance-consistency.php` | Validates ServiceProvider governance | console |
| `tooling/governance/check-security-commit-block-readiness.php` | Validates security commit block readiness | console |
| `tooling/governance/check-truth-consistency.php`         | Validates truth consistency across sources | console |
| `tooling/governance/check-quality-ratchet.php`           | Validates quality ratchet metrics | console |
| `tooling/governance/check-component-adoption.php`        | Validates component adoption rules | console |
| `tooling/governance/check-semantic-phpdoc.php`           | Validates semantic PHPDoc rules | console |
| `tooling/governance/check-gate-self-tests.php`           | Validates gate self-tests | console |
| `tooling/governance/check-canonical-terms.php`           | Validates canonical term usage | console |
| `tooling/testing/check-shallow-tests.php`                | Detects shallow tests with WHY/RISK/SUGGESTION diagnostics; supports full, baseline, and changed modes | console + report |
| `.agents/management/baselines/phpstan-baseline.json`     | Records pre-existing PHPStan debt for phased adoption            | JSON baseline |
| `.agents/management/baselines/self-explaining-architecture-baseline.json` | Records pre-existing self-explaining architecture debt | JSON baseline |
| `.agents/management/baselines/shallow-tests-baseline.json` | Records pre-existing shallow-test debt                         | JSON baseline |

## Engineering Canon Convergence

`.agents/knowledge/**` is CANONICAL_SUPPORT. It explains source principles and traceability. Binding rules remain in `.agents/how-to/**`, operational playbooks remain in `.agents/skills/**`, and executable enforcement remains in tooling.

<!-- ENGINEERING_CANON_CONVERGENCE_START -->
- `.agents/knowledge/README.md`
- `.agents/knowledge/engineering-canon.md`
- `.agents/knowledge/book-to-rule-traceability.md`
- `.agents/knowledge/source-principles/README.md`
- `.agents/knowledge/source-principles/software-architecture-hard-parts.md`
- `.agents/knowledge/source-principles/designing-data-intensive-applications.md`
- `.agents/knowledge/source-principles/clean-code-code-complete.md`
- `.agents/knowledge/source-principles/pragmatic-programmer.md`
- `.agents/knowledge/source-principles/domain-driven-design.md`
- `.agents/knowledge/source-principles/use-cases-domain-storytelling.md`
- `.agents/knowledge/source-principles/antipatterns-refactoring-patterns.md`
<!-- ENGINEERING_CANON_CONVERGENCE_END -->

### Source Principle Loading By Task Type

| Task Type | Source Principle |
|---|---|
| architecture decisions | `.agents/knowledge/source-principles/software-architecture-hard-parts.md` |
| data/state | `.agents/knowledge/source-principles/designing-data-intensive-applications.md` |
| implementation/refactor/tests | `.agents/knowledge/source-principles/clean-code-code-complete.md` |
| automation/tracer/evidence | `.agents/knowledge/source-principles/pragmatic-programmer.md` |
| domain modeling | `.agents/knowledge/source-principles/domain-driven-design.md` |
| scenario/use case | `.agents/knowledge/source-principles/use-cases-domain-storytelling.md` |
| antipattern/pattern/refactor | `.agents/knowledge/source-principles/antipatterns-refactoring-patterns.md` |

<!-- ENGINEERING_CANON_OPERATIONAL_HOWTO_START -->
- `.agents/how-to/modeling/how-to-scenario-input.md`
- `.agents/how-to/modeling/how-to-domain-discovery.md`
- `.agents/how-to/architecture/how-to-coupling-governance.md`
- `.agents/how-to/architecture/how-to-architecture-fitness-functions.md`
- `.agents/how-to/architecture/how-to-enterprise-application-patterns.md`
- `.agents/how-to/architecture/how-to-data-correctness.md`
- `.agents/how-to/architecture/how-to-adr-tradeoff-governance.md`
<!-- ENGINEERING_CANON_OPERATIONAL_HOWTO_END -->

<!-- ENGINEERING_CANON_CONSTRUCTION_HOWTO_START -->
- `.agents/how-to/implementation/how-to-software-construction.md`
- `.agents/how-to/implementation/how-to-refactoring.md`
- `.agents/how-to/implementation/how-to-design-patterns.md`
- `.agents/how-to/verification/how-to-antipattern-detection.md`
- `.agents/how-to/runtime/how-to-concurrency-runtime-safety.md`
- `.agents/dictionary/antipatterns/blob-god-object.md`
- `.agents/dictionary/antipatterns/spaghetti-code.md`
- `.agents/dictionary/antipatterns/fake-abstraction.md`
- `.agents/dictionary/antipatterns/cut-and-paste-programming.md`
- `.agents/dictionary/antipatterns/stovepipe-system.md`
- `.agents/dictionary/antipatterns/analysis-paralysis.md`
- `.agents/dictionary/antipatterns/service-locator.md`
- `.agents/dictionary/antipatterns/architecture-theater.md`
- `.agents/dictionary/antipatterns/golden-hammer.md`
- `.agents/dictionary/antipatterns/shallow-tests.md`
- `.agents/dictionary/antipatterns/generic-bucket.md`
<!-- ENGINEERING_CANON_CONSTRUCTION_HOWTO_END -->

<!-- ENGINEERING_CANON_CHECKERS_START -->
- `tooling/governance/check-engineering-canon-traceability.php`
- `tooling/governance/check-scenario-input.php`
- `tooling/governance/check-coupling-decisions.php`
- `tooling/governance/check-architecture-fitness-functions.php`
- `tooling/governance/check-antipatterns.php`
- `tooling/governance/check-data-correctness-evidence.php`
- `tooling/governance/check-enterprise-application-boundaries.php`
- `tooling/governance/check-adr-tradeoff-evidence.php`
- `tooling/governance/check-runtime-concurrency-safety.php`
- `tooling/governance/check-refactoring-safety.php`
- `tooling/governance/check-construction-checklist.php`
- `tooling/governance/create-actual-changes-review-pack.php`
<!-- ENGINEERING_CANON_CHECKERS_END -->

<!-- ENGINEERING_CANON_SDLC_AUTOMATION_START -->
- `.agents/how-to/verification/how-to-sdlc-automation.md`
- `.agents/skills/engineering-canon/SKILL.md`
- `.agents/skills/sdlc-automation/SKILL.md`
- `.agents/templates/evidence/scenario-input.md`
- `.agents/templates/evidence/domain-discovery.md`
- `.agents/templates/evidence/coupling-decision.md`
- `.agents/templates/evidence/architecture-fitness-functions.md`
- `.agents/templates/evidence/antipattern-review.md`
- `.agents/templates/evidence/construction-checklist.md`
- `.agents/templates/evidence/refactoring-safety.md`
- `.agents/templates/evidence/pattern-decision.md`
- `.agents/templates/evidence/enterprise-application-boundary.md`
- `.agents/templates/evidence/data-correctness.md`
- `.agents/templates/evidence/adr-tradeoff-decision.md`
- `.agents/templates/evidence/runtime-concurrency-safety.md`
- `tooling/sdlc/SdlcRuntime.php`
- `tooling/sdlc/GitChangedFiles.php`
- `tooling/sdlc/validate-changed.php`
<!-- ENGINEERING_CANON_SDLC_AUTOMATION_END -->

<!-- ENGINEERING_CANON_SDLC_RUNNERS_START -->
- `.agents/how-to/verification/how-to-sdlc-runners.md`
- `tooling/sdlc/preflight.php`
- `tooling/sdlc/validate-governance.php`
- `tooling/sdlc/validate-agent-task.php`
- `composer sdlc:preflight`
- `composer sdlc:changed`
- `composer sdlc:governance`
- `composer sdlc:agent-task`
<!-- ENGINEERING_CANON_SDLC_RUNNERS_END -->

### Enhanced Capabilities

**check-shallow-tests.php:**
- WHY: explains why a detected pattern is shallow
- RISK: classifies the risk of the shallow test (BLOCKER/HIGH/MEDIUM)
- SUGGESTION: provides actionable remediation for each finding

**check-self-explaining-architecture.php:**
- Orphan doc detection: finds documentation without corresponding source ownership
- Stale marker detection: finds outdated ADRs, diagrams, or READMEs
- Diagram detection: verifies diagrams match current source structure

## Governance 10/10 Criteria

- [x] all how-to documents indexed
- [x] conflict priority explicit
- [x] documentation location policy resolved
- [x] code review checklist includes all docs
- [x] every mandatory rule has checker
- [x] canonical component shape checker exists
- [x] forbidden folder checker exists
- [x] security governance checker exists
- [x] performance governance checker exists
- [x] advanced pattern folder checker exists
- [x] production readiness references docs
- [x] stage lock checker exists
- [x] component promotion checklist exists
- [x] AI preflight exists
- [x] exception policy exists
- [x] self-explaining architecture checker exists (check-self-explaining-architecture.php)
- [x] shallow test detector exists (check-shallow-tests.php)
- [x] documentation layering model resolved (how-to-document.md)

## Document Scope

| Document                                                | What It Covers                                                                   |
|---------------------------------------------------------|----------------------------------------------------------------------------------|
| ARCHITECTURE.md                                         | Root architecture north star: system shape, component map, runtime model         |
| how-to-architecture.md                                  | Folder structure, screaming architecture                                         |
| how-to-design-components.md                             | Component lifecycle, canonical shape                                             |
| how-to-use-advanced-architecture-patterns.md            | Advanced patterns                                                                |
| how-to-clean-code.md                                    | Clean code                                                                       |
| how-to-coding-standards.md                              | Coding standards                                                                 |
| how-to-code-style.md                                    | Code style                                                                       |
| how-to-unit-test.md                                     | Testing                                                                          |
| how-to-system-security.md                               | Security                                                                         |
| how-to-system-performance.md                            | Performance                                                                      |
| how-to-document.md (documentation/)                     | Documentation layering model: central vs local docs                          |
| how-to-write-self-explaining-architecture.md (documentation/) | Self-explaining architecture: local READMEs, dictionaries, ADRs, diagrams    |
| how-to-test-risk-based-behavioral-testing.md (verification/) | Risk-based testing strategy: V1/V2/V3 phases, test types, forbidden patterns |
| how-to-production-readiness.md (verification/)          | Production gates                                                             |
| how-to-code-review.md (verification/)                   | Review process                                                               |
| how-to-modern-php-attributes-di.md (implementation/)    | Modern PHP 8.x, attributes, DI, compiled metadata                                |
| how-to-events-listeners-event-sourcing-cqrs-realtime.md (architecture/) | Events DSL, listeners, event sourcing, CQRS, realtime, governance event sourcing |
| how-to-create-ai-code-review-packs.md (verification/)   | AI review pack creation, ZIP export, manifest validation, cleanup rules          |
| components/Identity/docs/                               | Identity component documentation skeleton: ownership, APIs, flows, security model  |

## Evidence Integrity Expectations

Generated review packs must be validated by independent ZIP inspection, not only manifest self-reporting.

Required agreement:

- root `README.md`
- root `MANIFEST.md`
- root `manifest.json`
- each ZIP `REVIEW_CONTEXT.md`
- each ZIP `STATS.md`

All must share the same generated timestamp, purpose, and run id. `manifest.json` `zip_entries` must match actual ZIP file-entry counts.

## Conflict Resolution

When rules conflict, resolve in this priority:

1. correctness and safety
2. security
3. production readiness
4. architecture ownership
5. component filesystem law
6. DDD / patterns
7. clean code
8. coding standards
9. code style
10. documentation rules
11. local preference

---

This index is the single source of governance routing.
