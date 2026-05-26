# How to Govern ADR / Trade-off Decisions

## Purpose

This document operationalizes Architecture Decision Records (ADRs) and trade-off governance within the codebase. It ensures architecture decisions are deliberate, compared against alternatives, and traceable.

## Role in Governance

Architecture decisions have long-lasting consequences. Undocumented decisions become folklore. ADRs capture the reasoning at the time of decision, enabling future review and reversal.

## When ADR/Trade-off Evidence Is Required

- Introducing a new architecture pattern
- Changing a durable governance rule
- Choosing between competing design approaches
- Adding a new external dependency
- Changing transaction, persistence, or concurrency strategy
- Any decision with significant coupling, security, or performance impact

## Decision Context

Every ADR must document the context: what constraints, requirements, and forces led to the decision point.

## Forces

Document the competing concerns:
- Performance vs simplicity
- Coupling vs cohesion
- Flexibility vs predictability
- Security vs usability
- Consistency vs availability

## Options Considered

List every serious option. Never present a decision as the only option.

## Option Comparison Matrix

Use a structured comparison across relevant dimensions:
- Complexity
- Performance
- Coupling
- Testability
- Reversibility
- Security
- Operational cost

## Chosen Decision

State the chosen option and the primary reason for choosing it.

## Consequences

Document both positive and negative consequences. Every decision has trade-offs.

## Reversibility

How reversible is this decision? What would reversal cost in time, code, and risk?

| Reversibility | Meaning |
|---------------|---------|
| Easy | Can be changed in a sprint with low risk |
| Medium | Requires significant refactoring but no data migration |
| Hard | Requires data migration, API changes, or breaking changes |
| Irreversible | Cannot be undone without starting over |

## Fitness Function Link

What automated check or test will detect drift from this decision?

## Coupling Link

How does this decision affect coupling between components?

## Data Correctness Link

How does this decision affect data correctness, consistency, or schema evolution?

## Security/Runtime Impact

Any security implications? Any runtime performance implications?

## Review Date

When should this decision be reviewed?

## Accepted Decision Debt

If a non-ideal option is chosen deliberately, document:
- What debt is accepted
- Why it is acceptable now
- What triggers review/remediation
- Owner

## Superseding/Replacing Decisions

When a new ADR supersedes an old one, link to the old ADR and explain what changed.

## Anti-Patterns

### Architecture by Impulse
Decisions made without documented reasoning or alternatives.

### Decision Without Alternatives
Presenting a single option as inevitable. Every non-trivial decision has alternatives.

### ADR with No Consequences
An ADR that only lists the decision without trade-offs or risks.

### ADR with No Validation
An ADR without a fitness function or test to detect drift.

### Permanent Temporary Decision
A "temporary" decision that is never reviewed or revisited.

## Evidence Requirements

Governance-sensitive architecture changes can require `adr-tradeoff-decision.md` evidence.

Template: `.agents/templates/evidence/adr-tradeoff-decision.md`

## Severity Rules

| Finding | Severity |
|---------|----------|
| Architecture decision without alternatives | HIGH |
| Decision without consequences | MEDIUM |
| Decision without fitness function | MEDIUM |
| Superseded decision not linked | LOW |
| Missing review date | LOW |

## Stop Conditions

- BLOCKER: Irreversible architecture decision without documented alternatives
- HIGH: Architecture pattern introduced without ADR
- MEDIUM: ADR without fitness function
