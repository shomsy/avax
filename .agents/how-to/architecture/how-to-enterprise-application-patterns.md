# How to Govern Enterprise Application Patterns (PoEAA)

## Purpose

This document operationalizes Patterns of Enterprise Application Architecture (PoEAA) governance within the framework. It ensures that application/domain/data boundary decisions are deliberate, evidence-backed, and reviewable.

## Role in the Governance

The codebase operates as a runtime-agnostic environment. Enterprise application pattern decisions affect:

- Application layer vs domain layer vs infrastructure layer clarity
- Transaction boundary safety
- Persistence boundary correctness
- Long-lived worker safety
- Public API stability

Pattern misuse leads to hidden coupling, untestable code, and architecture drift.

## Application Layer vs Domain Layer vs Infrastructure Layer

| Layer | Responsibility | Project Location |
|-------|---------------|---------------|
| Application | Orchestrate use cases, coordinate domain and infrastructure | Flows, PublicSurface delegates |
| Domain | Business rules, invariants, state transitions | Capabilities, domain models |
| Infrastructure | Persistence, external services, runtime adapters | Adapters, Gateways |

## Application Flow vs Domain Rule

- **Application flow**: orchestrates steps, delegates to domain/infrastructure. Transaction Script or Service Layer.
- **Domain rule**: enforces invariant, guards state, computes business value. Domain Model.

When the domain rule is trivial, Transaction Script is acceptable. When domain complexity grows, Domain Model is required.

## Transaction Script

Use when:
- The operation is procedural and simple
- There are few business rules
- Cross-cutting domain invariants are absent

Avoid when:
- Business logic is duplicated across scripts
- Multiple scripts share the same domain concepts
- Invariants are scattered

## Domain Model

Use when:
- Business rules are complex and interrelated
- Invariants must be enforced consistently
- Behavior and state are coupled meaningfully

Avoid:
- Anemic domain models (models with only getters/setters, no behavior)
- Domain models with persistence logic

## Service Layer

A thin orchestration layer between application flow and domain/infrastructure.

- Must not contain business rules
- Must not contain persistence logic
- Must delegate to domain objects and infrastructure adapters

## Data Mapper

Separates domain model from persistence schema. Required when:
- Domain model is non-trivial
- Schema evolution must be independent of domain evolution
- Multiple persistence backends are possible

## Table Data Gateway / Row Data Gateway

Use for simple CRUD without domain complexity. Do not combine with rich domain models.

## Repository Caution

Repository is allowed only when:
- It wraps a specific aggregate or entity
- It does not become a generic database vending machine
- It does not leak query builders or SQL into domain code

## Unit of Work

Track changes to multiple objects and commit them atomically. Required when:
- Multiple aggregates change in a single transaction
- Ordering of writes matters

## Identity Map

Prevents duplicate object loading within a unit of work scope. Required in long-lived workers to prevent memory leaks.

## Session State

Session state must have clear ownership and scope boundaries. In long-lived workers, session state must be reset between requests.

## Transaction Boundary Placement

- Transaction boundaries must be explicit
- Transaction start and commit must be in the application layer, not hidden in domain or infrastructure
- Nested transactions must be documented

## Persistence Ignorance vs Pragmatic Persistence

- Domain objects should be persistence-ignorant when feasible
- Pragmatic persistence coupling is acceptable when documented and bounded
- Active Record is acceptable for simple models but requires evidence

## DTO / Data Carrier Discipline

- DTOs carry data across boundaries without behavior
- DTOs must not leak persistence-specific types
- DTOs must be immutable or explicitly documented as mutable

## Boundary Decision Matrix

| Complexity | Pattern | Evidence Required |
|-----------|---------|-------------------|
| Trivial CRUD | Transaction Script + direct PDO/query builder | LOW |
| Simple domain rules | Service Layer + Repository | MEDIUM |
| Complex domain rules | Domain Model + Data Mapper + Unit of Work | HIGH |
| Multiple aggregates | Domain Model + Unit of Work + explicit transaction boundary | HIGH |

## Anti-Patterns

### Everything in Service Layer
Service layer becomes the new God Object. Business logic lives in services instead of domain objects.

### Anemic Domain Model Theater
Domain objects exist but contain no behavior. All logic is in services.

### Repository as Generic Database Vending Machine
Repository accepts arbitrary queries. No aggregate boundary enforcement.

### Transaction Boundary Hidden in Random Method
Transaction starts in a helper method deep in the call stack.

### Persistence Model Leaking into Public Surface
Persistence-specific types (entities, query builders, PDO statements) appear in PublicSurface APIs.

## Evidence Requirements

Changed production files with persistence/application-boundary signals require `enterprise-application-boundary.md` evidence.

Template: `.agents/templates/evidence/enterprise-application-boundary.md`

## Review Questions

1. What pattern was chosen and why?
2. Was a simpler alternative considered?
3. Where does the transaction boundary live?
4. Is persistence logic separated from domain logic?
5. Are DTOs used at boundaries?
6. Is the pattern safe for long-lived workers?

## Severity Rules

| Finding | Severity |
|---------|----------|
| Transaction boundary hidden or unclear | HIGH |
| Persistence model leaked into public API | HIGH |
| Anemic domain model with rich service layer | MEDIUM |
| Missing evidence for complex boundary | MEDIUM |
| Simple CRUD without evidence | LOW |

## Stop Conditions

- BLOCKER: Transaction boundary causes data corruption
- BLOCKER: Persistence model leaked into PublicSurface
- HIGH: Complex domain logic in service layer without domain model
- HIGH: Repository as generic query executor
