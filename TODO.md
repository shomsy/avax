# TODO

Current Source of Truth:

- CURRENT_TRUTH.md
- Code-Review-And-ToDo/EXECUTION.md
- Code-Review-And-ToDo/truth-reconciliation/truth-reconciliation-report.md

## Active Stage

Stage: Resolve V1 Blockers
Status: YELLOW (major blockers fixed, static analysis and refs remain)
Goal: Achieve V1 Kernel Green

## Current Tasks

[ ] Fix PHPStan static analysis errors
[ ] Fix broken internal references
[ ] Achieve static analysis GREEN
[ ] Prove V1 Kernel Green
[ ] Unlock V2 implementation

## Locked

### V2

Locked until:

- V1 Kernel Green proven

### V3

Locked until:

- V1 Kernel Green proven
- V2 platform baseline at least YELLOW/GREEN
- labs/SystemDesignKit MVP approved

## Forbidden

[ ] no production code changes outside active stage
[ ] no V2 implementation while locked
[ ] no V3 implementation while locked
[ ] no placeholders
[ ] no broad refactor

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
