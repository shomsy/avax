# How To Write Avax

## Status

**MANDATORY** — This document defines Avax-specific governance deviations and
extensions on top of the reusable baseline profiles.

A rule without an explicit exception **MUST** be treated as mandatory.

Code review **MUST NOT** mark a scope GREEN when a mandatory rule is violated.

---

## 0. What This File Is

This file defines **Avax local deviations and extensions** from the reusable
governance baseline.

It does NOT duplicate reusable rules.
It does NOT weaken reusable rules.
It extends them where Avax has specific architectural discipline.

### Imports

This file imports and builds upon:

```
L1 Universal:  .agents/.rules/governance/standards/coding/how-to-coding-standards.md
L1 Universal:  .agents/.rules/governance/standards/coding/naming-standard.md
L1 Universal:  .agents/.rules/governance/architecture/architecture-standard.md
L2 Language:   .agents/.rules/governance/profiles/languages/php.md
L3 Framework:  .agents/.rules/governance/profiles/project-types/framework.md
```

When a rule is not mentioned here, the reusable baseline applies.

### Precedence

```
L4 Avax overlay (this file) wins for Avax-specific naming,
filesystem shape, component structure, stage lock, and local architecture rules.

L1-L3 reusable profiles provide the baseline for everything else.
```

---

## 1. Project Identity

### 1.1 Project Name

Avax

### 1.2 Project Type

(e.g., Framework, Web Application, CLI Tool, API Service, Library)

### 1.3 Primary Language

(e.g., TypeScript, Python, Go, Rust, Java)

### 1.4 Runtime

(e.g., node, deno, bun, python, go, compiled binary)

---

## 2. Local Architecture Rules

Describe project-specific architectural constraints, component shapes,
folder semantics, and boundary rules that extend or narrow the baseline.

- Component shape requirements
- Public surface boundaries
- Flow vs capability decisions
- Forbidden folders or namespaces

---

## 3. Local Naming Rules

Describe project-specific naming doctrine: forbidden names, concept word
translations, flow vs capability decisions, etc.

- Concept words that are not folder names
- Forbidden system folders
- Namespace ownership rules
- Test naming conventions

---

## 4. Local Runtime/Execution Rules

Describe project-specific runtime composition rules, worker safety,
state management, and execution constraints.

- Runtime composition requirements
- Long-lived worker safety
- Stage discipline
- External I/O boundaries

---

## 5. Local Evidence Rules

Describe what constitutes proof in this project: validation commands,
evidence locations, required artifacts.

- Canonical validation commands
- Evidence storage location
- Required artifacts per claim

---

## 6. Local Testing/Validation Rules

Describe project-specific testing requirements, test tree structure,
gate self-test rules, and validation canon.

- Test tree structure
- Gate self-test rule
- Required negative tests
- Validation canon

---

## 7. Local Anti-Patterns

Describe project-specific forbidden patterns, collaboration rules,
and structural anti-patterns.

- Forbidden folder names
- Forbidden collaboration patterns
- Forbidden I/O patterns

---

## 8. Local Exceptions

Document explicit exceptions to baseline rules. Each exception must include:
rule, path, reason, risk, owner, expiry, required cleanup, approval, validation.
Temporary exceptions without expiry are forbidden.

Default: None.

---

## 9. Cross-Reference Index

| Document | Covers |
|----------|--------|
| (add as applicable) | |

When this file summarizes a rule, the referenced document is authoritative.

---

## 10. Override Semantics

This file declares Avax-specific overrides. The override behavior is:

1. **This file extends** reusable profiles — it does not replace them.
2. **When this file is silent**, the reusable baseline applies.
3. **When this file speaks**, it wins for Avax.
4. **Reusable profiles cannot be weakened** by this file for safety/security rules.
5. **Reusable profiles can be narrowed** by this file for stricter Avax discipline.
