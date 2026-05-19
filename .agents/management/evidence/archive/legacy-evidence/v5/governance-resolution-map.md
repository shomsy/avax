# Governance Resolution Map — V5-01

Date: 2026-05-10
Stage: V5-01 Whole-Repo Governance Resolution

## Purpose

Map every how-to document, identify purpose, scope, V5 stage applicability, conflicts, and confirm precedence.

## Discovered how-to-*.md Documents

| #  | File                                         | Purpose                                                                                                       | Mandatory Scope                                         |
|----|----------------------------------------------|---------------------------------------------------------------------------------------------------------------|---------------------------------------------------------|
| 1  | how-to-architecture.md                       | Folder structure, screaming architecture, fractal flow, recursive ownership, placement decisions              | All V5 stages involving structure, naming, ownership    |
| 2  | how-to-architecture-extension-with-ddd.md    | DDD application, bounded context, entities, value objects within AvaX architecture                            | V5 stages involving DDD concepts, domain modeling       |
| 3  | how-to-clean-code.md                         | Clean code: correctness, readability, naming, functions, modules, error handling, testing, anti-patterns      | All V5 stages involving code changes                    |
| 4  | how-to-code-review.md                        | Enterprise-grade system code review process, governance compliance gate, finding templates                    | Every final review and stage closure; V5.6 primary      |
| 5  | how-to-code-style.md                         | Project-specific PHP formatting, typing, imports, constructor promotion, nullable style, named arguments      | All V5 stages involving PHP code                        |
| 6  | how-to-coding-standards.md                   | PHP version expectations, security, DevSecOps gates, modern language features, output expectations            | All V5 stages involving PHP code                        |
| 7  | how-to-design-components.md                  | Component lifecycle, canonical shape, forbidden folders, platform planes, completion standard                 | Every new or changed component                          |
| 8  | how-to-document.md                           | Documentation location policy, how-this-works.md rules, mermaid diagrams, debug-first guidance                | Every documentation change                              |
| 9  | how-to-dogfooding.md                         | Internal component reuse, one capability = one owner, dependency direction, adoption matrix                   | V5-03, V5-04, V5-05, V5-10, V5-18, V5-19, V5-20         |
| 10 | how-to-modern-php-attributes-di.md           | PHP 8.0-8.5 features, attribute compilation, DI/autowiring, compiled metadata, hot-path reflection discipline | V5-06, V5-07, V5-08, V5-09, V5-13, V5-20, V5-21         |
| 11 | how-to-production-readiness.md               | Production gates, health checks, doctor, runtime safety, failure handling                                     | V5-23, V5.5-12, all production-facing stages            |
| 12 | how-to-system-performance.md                 | Performance governance, hot paths, hidden I/O, bounding, latency budgets, memory management                   | V5-06, V5-18, V5-19, V5-20, all V5.5 stages             |
| 13 | how-to-system-security.md                    | Security governance, boundaries, auth, secrets, input validation, output encoding, naming                     | V5-02, V5-13, V5-17, V5-22, all security-sensitive work |
| 14 | how-to-unit-test.md                          | Behavior-first tests, naming, Arrange/Act/Assert, one-act rule, assertion precision                           | Every test added or changed                             |
| 15 | how-to-use-advanced-architecture-patterns.md | Advanced patterns, GoF application, event sourcing, CQRS                                                      | V5 stages involving advanced architecture decisions     |

## Conflicts and Overlaps Resolved

| Conflict                                                                    | Resolution                                                                                                                                             | Precedence                                         |
|-----------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------|
| how-to-architecture.md vs how-to-design-components.md on component shape    | how-to-design-components.md wins for component-specific filesystem shape; how-to-architecture.md wins for repo-level and system-level reading model    | AGENTS.md §6, §7                                   |
| how-to-coding-standards.md vs how-to-code-style.md on PHP style details     | how-to-code-style.md wins for project-specific formatting preferences; how-to-coding-standards.md wins for PHP version expectations and security gates | GOVERNANCE_INDEX.md conflict resolution §1         |
| how-to-modern-php-attributes-di.md vs how-to-clean-code.md on modern syntax | how-to-modern-php-attributes-di.md wins for PHP 8.x feature adoption rules; how-to-clean-code.md wins for general readability principles               | V5 plan mandatory stage-to-document mapping        |
| EXECUTION.md vs CURRENT_TRUTH.md on V3 status                               | CURRENT_TRUTH.md wins — V3 is CLOSED/GREEN, SystemDesignKit promoted                                                                                   | AGENTS.md §3 (CURRENT_TRUTH.md is source of truth) |
| TODO.md vs CURRENT_TRUTH.md on V4 status                                    | CURRENT_TRUTH.md wins — all V4 stages GREEN                                                                                                            | AGENTS.md §3                                       |

## AGENTS.md Precedence Confirmed

AGENTS.md §1 (Order of Precedence) confirms:

1. AGENTS.md is root contract
2. .agents/GOVERNANCE_INDEX.md is navigation map
3. .agents/how-to/** are local governance rules
4. Local AvaX naming rules override reusable mounted rules
5. Folder = Flow/Capability, Unit = Responsibility, Function = Exact Action

## Standard Mode and Harness-Full Mode Behavior

**Standard Mode** (default):

- Applies AGENTS.md, GOVERNANCE_INDEX.md, CURRENT_TRUTH.md, EXECUTION.md, relevant how-to documents
- Does not expand into full reusable harness protocols unless required

**Harness-Full Mode** (triggered by "uradi po pravilima .agents"):

- Applies all local rules, all .agents/how-to/**, all .agents/.rules/**
- Local AvaX filesystem shape, naming, stage lock, PublicSurface, Flow vs Capability rules still win

Both modes obey the same precedence order. Harness-Full Mode only expands the rule set, it does not weaken local rules.

## Active Stage Source of Truth

Per AGENTS.md §3, project state is read in this order:

1. CURRENT_TRUTH.md — current project status
2. EVIDENCE/EXECUTION.md — active stage and execution order
3. .agents/management/ACTIVE.md — active stage
4. TODO.md — execution queue
5. Latest validation output

Current active stage: **V5-01 Whole-Repo Governance Resolution** (this stage)
Next active stage after completion: **V5-02 Critical Security Blocker Cleanup** (or early review inventory as requested)

## Deliverables

- `EVIDENCE/v5/governance-resolution-map.md` (this file)
- `EVIDENCE/v5/source-of-truth-order.md`
- `EVIDENCE/v5/mandatory-how-to-governance-matrix.md`
