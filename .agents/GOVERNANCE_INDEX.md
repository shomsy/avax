# AvaX Governance Index

## Purpose

This file tells agents what to read, in what order, and why.

**AGENTS.md is the root contract.**
**This file is the navigation map.**

## Required Reading Order for Any Code Work

1. `AGENTS.md` - root contract
2. `.agents/GOVERNANCE_INDEX.md` - this navigation map
3. `CURRENT_TRUTH.md` - project state
4. `EVIDENCE/EXECUTION.md` - active task
5. `.agents/management/ACTIVE.md` - active stage
6. relevant `.agents/how-to/*.md` - governance rules
7. relevant `.agents/skills/**` - task playbook
8. relevant `.agents/business-logic/**` - domain meaning
9. relevant source files
10. relevant tests
11. latest relevant reports in `EVIDENCE/recovery-reports/`

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

| Task Type                  | Must Read                                                                       |
|----------------------------|---------------------------------------------------------------------------------|
| Architecture/refactor      | how-to-architecture, how-to-design-components, DDD extension, advanced patterns |
| Component design           | how-to-design-components, security, performance, production readiness           |
| PHP code                   | coding standards, code style, clean code                                        |
| Modern PHP / attributes / DI | how-to-modern-php-attributes-di                                               |
| Unit tests                 | how-to-unit-test                                                                |
| Documentation              | how-to-document                                                                 |
| Security-sensitive code    | how-to-system-security                                                          |
| Performance-sensitive code | how-to-system-performance                                                       |
| Review                     | how-to-code-review plus all applicable how-to docs                              |
| Recovery                   | recovery skill, business-logic, memories, reports                               |

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

## Document Scope

| Document                                     | What It Covers                           |
|----------------------------------------------|------------------------------------------|
| how-to-architecture.md                       | Folder structure, screaming architecture |
| how-to-design-components.md                  | Component lifecycle, canonical shape     |
| how-to-use-advanced-architecture-patterns.md | Advanced patterns                        |
| how-to-clean-code.md                         | Clean code                               |
| how-to-coding-standards.md                   | Coding standards                         |
| how-to-code-style.md                         | Code style                               |
| how-to-unit-test.md                          | Testing                                  |
| how-to-system-security.md                    | Security                                 |
| how-to-system-performance.md                 | Performance                              |
| how-to-document.md                           | Documentation                            |
| how-to-production-readiness.md               | Production gates                         |
| how-to-code-review.md                        | Review process                           |
| how-to-modern-php-attributes-di.md           | Modern PHP 8.x, attributes, DI, compiled metadata |

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