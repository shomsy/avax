# Governance Map

## Purpose

This folder contains executable governance rules for project development.

Documents are grouped by operational governance area, not by source book, author, pattern name, framework, or technology category.

## Loading Rule

Agents and scripts must load governance documents using the reading order in `00-how-to-reading-order.md`.

The loading pattern is:

```text
.agents/how-to/**/*.md
```

Only Markdown governance files are canonical. A local generated `how-to.txt`, if recreated by tooling, is ignored and must not be loaded as governance.

## Canonical Location Rule

ALL governance documents MUST live inside a categorized subfolder.

The root `.agents/how-to/` directory contains ONLY:

- `README.md` — this file
- `00-how-to-reading-order.md` — canonical reading order

`how-to.txt` is retired as a repository file. If a tool regenerates it locally, it remains an ignored non-canonical artifact.

Root-level governance documents are FORBIDDEN. They create duplicates, shadow governance, and AI loading ambiguity.

If a governance document is found at root level, it MUST be migrated to the correct subfolder or deleted if it is a duplicate.

## Governance Precedence

When governance documents disagree, resolve in this order:

```text
PRECEDENCE (highest to lowest):

1. AGENTS.md — root project contract
2. .agents/how-to/README.md — structure rules, loading rules
3. .agents/how-to/00-how-to-reading-order.md — canonical loading order
4. Domain-specific governance (architecture/, modeling/, components/, implementation/, verification/, documentation/, project/)
5. .agents/.rules/governance/ — mounted reusable rules
6. Generated artifacts, evidence, reports (non-authoritative)
```

Within the same precedence level, the document appearing EARLIER in the reading order wins.

## Why This Precedence Exists

- **Junior engineers** need to know which rule wins when two documents disagree. This precedence is the answer.
- **Future maintainers** need to know where to add new rules without creating conflicts. Each domain has a subfolder.
- **AI coding agents** need deterministic rule resolution. Without explicit precedence, AI will guess, and guessing creates architectural drift.

## Forbidden Governance Patterns

```text
- Duplicate governance documents (same concept in multiple files)
- Shadow governance (governance at root level outside categorized subfolders)
- Conflicting canonical ownership (two documents claiming authority over the same rule)
- Stale references to non-existent files
- Generated artifacts treated as authoritative governance
```

## Folders

### architecture/

Structural laws, architecture decisions, DDD architecture extension, pattern translation, runtime composition, event-sourcing/CQRS, advanced architecture patterns, engineering laws/heuristics for review discipline, AI-assisted execution rules, and trade-off rules.

### modeling/

Flow modeling, domain discovery, scenario input design, and public API ergonomics.

### components/

Component design laws, component completion rules, and component dogfooding requirements.

### implementation/

Coding practice, code style, coding standards, PHP language usage, clean code rules, dependency injection discipline, and modern PHP attribute-based DI.

### verification/

Review processes, code review packs, test evidence rules, risk-based testing strategy, production readiness gates, security threat modeling, performance governance, and data correctness validation.

### documentation/

Documentation writing rules, documentation location discipline, and self-explaining architecture standards.

### project/

Project-local rules specific to this repository, including project-specific writing conventions and operating rules.

## Reading Order

See `00-how-to-reading-order.md`.

## Migration Rule

When moving governance files, use `git mv`, update references, update validation scripts, and prove no stale paths remain.
