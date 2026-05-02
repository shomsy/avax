# AvaX TODO

Status: active execution queue  
Purpose: tell agents what to do next without letting them jump across the master roadmap.  
Mandatory reading: `EXECUTION.md`

---

## 0. Mandatory Rule

Before doing any work, read:

```text
1. CURRENT_TRUTH.md
2. EXECUTION.md
3. TODO.md
4. .agents/how-to/*.md
```

All software design, refactoring, implementation, testing, documentation, and review work must comply with every
applicable `how-to-*.md` document located in:

```text
.agents/how-to/
```

Do not cherry-pick rules.

Do not ignore naming, architecture, testing, PublicSurface, component design, documentation, or review rules.

If a rule conflicts with the active stage, stop and report the conflict.

---

## 1. Current Active Stage

```text
Stage 00: Current Truth Lock
```

Only Stage 00 is active.

Everything else is read-only context.

Do not implement V2 or V3 features.

Do not move files.

Do not repair namespaces.

Do not repair tests.

Do not add new components.

Start by reading `EXECUTION.md` and executing Stage 00 exactly.

---

## 2. Stage 00 TODO: Current Truth Lock

Goal:

```text
Establish one trusted repository truth before any code movement, taxonomy repair, namespace repair, test repair, or feature work.
```

Tasks:

```text
[ ] Read CURRENT_TRUTH.md if it exists.
[ ] Read AGENTS.md if it exists.
[ ] Read EXECUTION.md.
[ ] Read this TODO.md.
[ ] Discover every .agents/how-to/*.md document.
[ ] Check whether current reports/plans disagree.
[ ] Update or create CURRENT_TRUTH.md with current date and status.
[ ] Record architecture status.
[ ] Record taxonomy status.
[ ] Record autoload status.
[ ] Record namespace integrity status.
[ ] Record test status.
[ ] Record static analysis status.
[ ] Record runtime safety status.
[ ] Record public surface integrity status.
[ ] Record production readiness status.
[ ] List blockers.
[ ] List forbidden work while RED.
[ ] List next 5 allowed actions.
[ ] Ensure CURRENT_TRUTH.md points to EXECUTION.md.
[ ] Ensure TODO.md points to EXECUTION.md.
[ ] Ensure AGENTS.md, if present, tells agents to read CURRENT_TRUTH.md first.
[ ] Produce Stage 00 report.
```

Allowed files:

```text
CURRENT_TRUTH.md
TODO.md
EXECUTION.md
AGENTS.md
Code-Review-And-ToDo/management or equivalent report folders
```

Forbidden:

```text
[ ] production code changes
[ ] namespace changes
[ ] file moves
[ ] feature work
[ ] test repair
[ ] V2 implementation
[ ] V3 implementation
[ ] compatibility bridge changes
[ ] placeholder classes
```

Recommended validation:

```bash
git status --short
composer validate --no-check-publish
find . -path '*/.agents/how-to/*' -name 'how-to-*.md' -print
find . -name 'CURRENT_TRUTH.md' -o -name 'AGENTS.md' -o -name 'TODO.md' -o -name 'EXECUTION.md'
```

Done when:

```text
[ ] CURRENT_TRUTH.md is current.
[ ] CURRENT_TRUTH.md defines RED/YELLOW/GREEN status.
[ ] EXECUTION.md exists.
[ ] TODO.md exists and points to EXECUTION.md.
[ ] .agents/how-to/*.md documents are discovered.
[ ] Next allowed stage is named.
[ ] Stage 00 report is written.
```

---

## 3. Next Stage Queue

Do not start these until Stage 00 is GREEN.

```text
[ ] Stage 01: Final Project Tree Freeze
[ ] Stage 02: Taxonomy Integrity Green
[ ] Stage 03: API Classification and Evolution Rules
[ ] Stage 04: Component Completion
[ ] Stage 05: Canonical Class Map
[ ] Stage 06: Autoload and Namespace Repair
[ ] Stage 07: Test Layer Repair
[ ] Stage 08: Static Analysis Green
[ ] Stage 09: AvaX Kernel Green
[ ] Stage 10: Production Readiness Baseline
[ ] Stage 11: Golden Path App
```

---

## 4. Locked Planning Sections

These may be planned, reviewed, or documented.

They must not be implemented until V1 Kernel Green.

### V2 Locked

```text
[ ] API Contract Engine
[ ] Integration Engine
[ ] Reliability Engine
[ ] Operations Engine
[ ] Observability Engine
[ ] Security / Identity / Tenancy Engine
[ ] Delivery Engine
[ ] Runtime Supervision Engine
[ ] Memory Lifecycle Engine
[ ] Developer Experience Engine
```

### V3 Locked

```text
[ ] SystemDesignKit
[ ] Capacity model
[ ] Load model
[ ] Latency budget
[ ] Availability target
[ ] Consistency model
[ ] Partitioning model
[ ] Sharding model
[ ] Replication model
[ ] Messaging model
[ ] Projection model
[ ] Failure model
[ ] Simulation runner
[ ] Architecture tests
[ ] Reference architectures
[ ] Tradeoff reports
```

---

## 5. Component Work Rule

When component work becomes allowed, every component must follow:

```text
.agents/how-to/how-to-design-components.md
```

If the file exists, it is mandatory.

No component may be marked complete unless it has:

```text
[ ] public contract
[ ] internal runtime behavior
[ ] fake/local adapter
[ ] production adapter boundary
[ ] configuration schema
[ ] health/doctor check
[ ] failure model
[ ] retry/timeout/circuit/backoff policy when external I/O exists
[ ] observability events
[ ] contract tests
[ ] failure tests
[ ] runtime-safety rules
[ ] example usage
[ ] documentation
[ ] operator diagnostics
```

If these are missing, mark the component as:

```text
draft
experimental
internal
planned
partial
```

Never mark it production-ready.

---

## 6. Agent Completion Report Template

Every agent must finish work with:

```text
Stage:
Status:
Files changed:
Files intentionally not touched:
Validation commands run:
Validation summary:
Remaining risks:
Next allowed action:
```

No evidence means incomplete.

No validation means incomplete.

No TODO update means incomplete.

---

## 7. Current Instruction For The Next Agent

```text
Read EXECUTION.md.
Read this TODO.md.
Discover .agents/how-to/*.md.
Execute Stage 00 only.
Do not implement any V2/V3 feature.
Do not touch production code.
Return a Stage 00 report with evidence.
```
