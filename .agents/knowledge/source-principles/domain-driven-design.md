# Source Principles: Domain-Driven Design

## Role in Governance

Use this source family when naming, boundaries, flows, capabilities, dictionaries, or local docs model business meaning.

## Domain First

Start with business capability and user/system goal, not framework folders.

## Subdomains

Classify core, supporting, or generic so design effort matches business importance.

## Core vs Supporting vs Generic

Core domains deserve deeper modeling and local docs. Generic domains should reuse proven capabilities.

## Bounded Contexts

A bounded context owns meaning. The same word can mean different things in different contexts if translation is explicit.

## Ubiquitous Language

Terms in code, docs, tests, and evidence should match local business meaning.

## Context Mapping

State upstream/downstream relationships and where translation happens.

## Anticorruption Layer

Use an anticorruption boundary when external models would pollute local language or invariants.

## External Model Translation

Do not let vendor/API/database terms leak into core local vocabulary without mapping.

## Invariants

Invariants belong with the behavior that protects them and must be testable.

## Aggregates / Consistency Boundaries, only if project uses that concept

Use aggregate language only when a consistency boundary is explicitly modeled. Do not create aggregate folders by habit.

## Domain Events, only when event history matters

Use domain events when event history or decoupled reaction is part of the model, not as decorative indirection.

## Domain Service Caution

Domain service is not a generic `Services/` folder. It is a named behavior that does not naturally belong to one entity/value.

## Avoid DDD Folder Theater

DDD is not permission to create generic folders like `Entities`, `ValueObjects`, `Services`, or `Repositories`.

## Dictionary Governance

Dictionary entries need term, context, meaning, owner, examples, non-examples, and related terms.

## Local Documentation

Local component docs explain local language, flows, decisions, and mistakes. They do not redefine global governance.

## Modeling Investment Rule

Invest more modeling effort where business risk, security, data correctness, or churn is high.

## Agent Review Questions

What capability owns this? Which terms have local meaning? What invariant is protected? What external concept is translated?

## Evidence Requirements

`domain-discovery.md`, dictionary updates, scenario evidence, and local docs for non-trivial contexts.

## Severity Rules

Modeling by technical folder habit is HIGH for new components. Terminology drift is MEDIUM unless it causes incorrect behavior.

## Stop Conditions

Stop when capability, context, invariant, or translation boundary is unknown.

