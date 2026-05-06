# AvaX Governance Index

## Required Reading Order

Before any implementation work, agents must read these documents in order:

1. **how-to-architecture.md** - Foundation: folder structure, naming, screaming architecture
2. **how-to-design-components.md** - Component design, canonical shape, filesystem law
3. **how-to-architecture-extension-with-ddd.md** - DDD extension, bounded context
4. **how-to-use-advanced-architecture-patterns.md** - Advanced patterns, GoF application
5. **how-to-clean-code.md** - Clean code, object-oriented principles
6. **how-to-coding-standards.md** - Coding standards, type safety
7. **how-to-code-style.md** - Code style, formatting
8. **how-to-unit-test.md** - Unit testing, test patterns
9. **how-to-system-security.md** - Security governance
10. **how-to-system-performance.md** - Performance governance
11. **how-to-document.md** - Documentation rules
12. **how-to-production-readiness.md** - Production readiness gates
13. **how-to-code-review.md** - Code review process

## Document Scope

| Document                                     | What It Covers                                                           |
|----------------------------------------------|--------------------------------------------------------------------------|
| how-to-architecture.md                       | Folder structure, namespace, naming hierarchy, screaming architecture    |
| how-to-design-components.md                  | Component lifecycle, canonical shape, PublicSurface, Flows, Capabilities |
| how-to-architecture-extension-with-ddd.md    | DDD concepts, bounded context, entities, value objects                   |
| how-to-use-advanced-architecture-patterns.md | Advanced patterns, GoF, event sourcing, CQRS application                 |
| how-to-clean-code.md                         | Clean code, object orientation, canonical rules                          |
| how-to-coding-standards.md                   | Type safety, validation, error handling                                  |
| how-to-code-style.md                         | Code style, formatting, naming                                           |
| how-to-unit-test.md                          | Test patterns, contract tests, mocks                                     |
| how-to-system-security.md                    | Security boundaries, authentication, authorization                       |
| how-to-system-performance.md                 | Performance, hidden I/O, bounding                                        |
| how-to-document.md                           | Documentation, docs mirror rule                                          |
| how-to-production-readiness.md               | Production gates, health checks, doctor                                  |
| how-to-code-review.md                        | Review process, checklist enforcement                                    |

## Conflict Resolution

When rules from different documents conflict, resolve in this priority order:

1. **correctness and safety** - Code must work and be safe
2. **security** - Security boundaries must be protected
3. **production readiness** - Production gates must pass
4. **architecture ownership** - Clear ownership beats convenience
5. **component filesystem law** - Canonical shape is mandatory
6. **DDD / advanced pattern rules** - Domain concepts beat patterns
7. **clean code** - Readability beats cleverness
8. **coding standards** - Standards are non-negotiable
9. **code style** - Style is automatic
10. **documentation rules** - Docs must mirror source
11. **local preference** - Only when nothing else applies

## Document Type Mapping

| Work Type          | Primary Document                                     |
|--------------------|------------------------------------------------------|
| New component      | how-to-design-components.md                          |
| New feature        | how-to-architecture.md + how-to-design-components.md |
| Security work      | how-to-system-security.md                            |
| Performance work   | how-to-system-performance.md                         |
| Refactor           | how-to-clean-code.md                                 |
| New developer      | how-to-architecture.md + how-to-design-components.md |
| Code review        | how-to-code-review.md                                |
| Production release | how-to-production-readiness.md                       |
| Documentation      | how-to-document.md                                   |

## Governance 10/10 Criteria

Governance is 10/10 only when:

- [x] all how-to documents are indexed
- [x] conflict priority is explicit
- [x] documentation location policy is resolved
- [x] code review checklist includes all governance docs
- [ ] every mandatory rule has checker or manual review mapping
- [ ] canonical component shape checker exists
- [ ] forbidden folder checker exists
- [ ] security governance checker exists
- [ ] performance governance checker exists
- [ ] advanced pattern folder checker exists
- [x] production readiness references security/performance docs
- [ ] stage lock checker exists
- [ ] component promotion checklist exists
- [ ] AI preflight exists
- [ ] exception policy exists

## Mandatory Rule

No agent may start implementation before reading this index.

Before any code edit, the agent must state:

- Active stage (V1, V2, V3)
- Forbidden scope
- Next allowed action
- Validation commands that must pass

---

This index is the single source of governance routing.