# Governance Map

## Purpose

This folder contains executable governance rules for AvaX development.

Documents are grouped by operational governance area, not by source book, author, pattern name, framework, or technology category.

## Loading Rule

Agents and scripts must load governance documents recursively from:

```
.agents/how-to/**/*.md
```

They must not assume governance lives only in:

```
.agents/how-to/*.md
```

The root `.agents/how-to/` directory contains only this README, `00-reading-order.md`, and `how-to.txt` (a generated artifact that must not be staged per AGENTS.md).

Canonical document location is by the reading order and folder map, not by flat root scan.

## Reading Order

See `00-reading-order.md`.

## Folders

### architecture/

Structural laws, architecture decisions, DDD architecture extension, pattern translation, runtime composition, event-sourcing/CQRS, advanced architecture patterns, engineering laws/heuristics for review discipline, AI-assisted execution rules, and trade-off rules.

These documents define how the system is structured, how domains map to code, and how architectural patterns are translated into AvaX conventions.

### modeling/

Flow modeling, domain discovery, scenario input design, and public API ergonomics.

These documents define how AvaX models Flows as use-case behavior, establishes intention-revealing public APIs, and treats string selectors as first-class developer experience.

### components/

Component design laws, component completion rules, and component dogfooding requirements.

These documents define how AvaX components are designed, what makes a component complete, and how components must reuse existing AvaX capabilities through stable boundaries.

### implementation/

Coding practice, code style, coding standards, PHP language usage, clean code rules, dependency injection discipline, and modern PHP attribute-based DI.

These documents define how production code is written, formatted, and structured within AvaX components and flows.

### verification/

Review processes, test evidence rules, production readiness gates, security threat modeling, performance governance, data correctness validation, and quality gates.

These documents define how work is verified, reviewed, and proven before being accepted as production-ready.

### documentation/

Documentation writing rules and documentation location discipline.

These documents define how documentation is written, where it belongs, and what it must explain.

### project/

Project-local rules specific to AvaX operation, including AvaX-specific writing conventions and project operating rules.

These documents contain rules that are specific to this repository and do not generalize to other projects.

## Canonical Rule

Documents are grouped by when and how the rule is applied, not by book, author, pattern, framework, or technology category.

## Migration Rule

When moving governance files, use `git mv`, update references, update validation scripts, and prove no stale paths remain.
