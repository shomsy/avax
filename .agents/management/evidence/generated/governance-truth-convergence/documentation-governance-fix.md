# Documentation Governance Convergence Fix

## Status
- **Finding:** BLOCKER — semantic contradiction between centralized documentation governance and self-explaining architecture
- **File changed:** `.agents/how-to/documentation/how-to-document.md`
- **Governance sources:** AGENTS.md, how-to-document.md, how-to-write-self-explaining-architecture.md

## Problem

`how-to-document.md` §50-78 declared `docs/` as the **single canonical location** for ALL documentation with the rule "No documentation is allowed outside `docs/`".

This directly contradicted:
- Self-explaining architecture philosophy (local README, ADR, dictionary at component boundary)
- AvaX component dogfooding rules (component-local docs in `components/<Area>/<Component>/docs/`)
- Existing practice (`components/Identity/docs/` already exists)

## Resolution

Replaced the single-location HARD RULE with a **Two-Layer Documentation Model**:

### Layer 1 — Global / Canonical (`docs/`, `.agents/`)
System-wide truth, cross-component standards, architecture philosophy.

### Layer 2 — Local / Component (`components/<Area>/<Component>/docs/`)
Local design decisions, component-specific ADRs, dictionary, how-this-works.

### Resolution Rules
- Component-local docs MUST NOT duplicate global docs
- If a concept affects multiple components → `docs/`
- If it explains a single component's internal design → component docs/
- Decision tree added for deterministic location selection
- Negative space defined for both layers (what does NOT belong where)

## Validation
- `check-governance-canonical-truth.php` — GREEN
- No stale references to old rule in any `.md` file
- Generated `how-to.txt` artifact still has old language (expected — generated artifact)

## Classification
- **severity:** BLOCKER (resolved)
- **governance_source:** how-to-document.md §50-78 → replaced by Two-Layer Documentation Model
- **impact:** Eliminated semantic governance drift between centralized and local documentation models
