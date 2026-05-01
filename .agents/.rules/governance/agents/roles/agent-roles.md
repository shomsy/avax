# Agent Roles & Delegation — Specialized Agent Personas

Version: 1.0.0
Status: Normative
Scope: `.agents/governance/**`

This document defines the specialized agent roles available within the Agent
Harness OS. Roles provide structured personas with scoped responsibilities,
enabling focused, high-quality outputs at each stage of the SDLC.

Inspired by MetaGPT's `Code = SOP(Team)` philosophy where software is produced
by materializing Standard Operating Procedures and applying them to teams
of specialized agents, and ECC's 36 specialized agents with YAML-defined
capabilities and permissions.

---

## 1) Core Principle: Code = SOP(Team)

Software quality is maximized when each role:
1. Has a **single, clear responsibility**
2. Produces **structured output** that serves as input to the next role
3. Follows a **defined SOP** (Standard Operating Procedure)
4. Operates within its **declared trust tier**

---

## 2) Core v1 Role Catalog

### 2.1 Supervisor / Router
| Attribute | Value |
|:---|:---|
| **Responsibility** | Task classification, delegating to sub-agents, merging results |
| **Trust Tier** | T0 (ReadOnly) |
| **Constraint** | **NO Deep Research; NO Code Changes** |
| **SOP Output** | Task delegation manifest (`task.json`) |
| **Model** | Fast/Context-efficient model |

### 2.2 Mapper / Researcher
| Attribute | Value |
|:---|:---|
| **Responsibility** | Repository exploration, file discovery, impact mapping |
| **Trust Tier** | T0 (ReadOnly) |
| **Constraint** | **NO Code Changes**; Max 5 files per pass |
| **SOP Output** | Pruned context brief (`mapper-brief.md`) |
| **Model** | "Mini" / Cheap model (gpt-4o-mini / Gemini Flash) |

### 2.3 Docs / API Researcher
| Attribute | Value |
|:---|:---|
| **Responsibility** | Documentation analysis, API verification, MCP source analysis |
| **Trust Tier** | T0 (ReadOnly) |
| **Constraint** | Only returns relevant references and summaries |
| **SOP Output** | API/Docs findings artifact (`docs-brief.md`) |
| **Model** | "Mini" / Cheap model |

### 2.4 Codex Executor
| Attribute | Value |
|:---|:---|
| **Responsibility** | Final implementation, refactoring, complex reasoning |
| **Trust Tier** | T1 (WorkspaceWrite) |
| **Constraint** | Requires a "Compressed Brief" from Mapper/Docs roles |
| **SOP Output** | Verified source code + execution evidence |
| **Model** | Primary/Strong model (gpt-4o / o1 / Codex) |

### 2.5 Reviewer (Optional/Final)
| Attribute | Value |
|:---|:---|
| **Responsibility** | Final diff audit, risk assessment, sanity check |
| **Trust Tier** | T0 (ReadOnly) |
| **SOP Output** | Approval/Rejection report with risk score |
| **Model** | Primary/Strong model |

---

## 3) Role Invocation Protocol

### Declaration
When operating in a specific role, the agent SHOULD declare it:
```
Operating as: reviewer
```

This is **advisory**, not prescriptive — agents may operate in multiple roles
during a single task. The declaration helps with:
- Audit trail (which role produced which output)
- Trust tier enforcement (role constrains available permissions)
- SOP compliance (role-specific checklists apply)

### Role Transitions
An agent may transition between roles within a task:
```
[planner] → Decomposed task into 4 subtasks
[architect] → Designed API contract for auth module
[implementer] → Writing implementation...
[reviewer] → Self-reviewing before submission
```

### Single-Agent Mode
When only one agent instance is available, it cycles through roles
sequentially, following the workflow pipeline defined in
`workflow-pipelines.md`.

### Multi-Agent Mode
When multiple agent instances are available, roles can be parallelized:
- planner + architect can work simultaneously on different tasks
- reviewer MUST NOT be the same instance as implementer (separation of
  concerns)

---

## 4) Sequential Handoff Protocol

Inspired by MetaGPT's structured document passing:

```
planner ──[TODO.md]──► architect ──[design.md]──► implementer
                                                       │
                                                  [source code]
                                                       │
                                              tester ◄─┘
                                                │
                                          [test results]
                                                │
                                         reviewer ◄─┘
                                                │
                                        [review findings]
                                                │
                                        documenter ◄─┘
                                                │
                                          [docs + changelog]
                                                │
                                         releaser ◄─┘
```

### Handoff Requirements
Each handoff MUST include:
1. **Structured artifact**: The output document from the previous role
2. **Completion signal**: Confirmation that the role's SOP is complete
3. **Evidence**: Timestamped proof of work (per `quality-gates.md`)

---

## 5) Custom Roles

Projects MAY define custom roles in their root `AGENTS.md`:

```markdown
## Custom Agent Roles

### Data Analyst
| Attribute | Value |
|:---|:---|
| **Responsibility** | Data analysis, visualization, insight generation |
| **Trust Tier** | T1 |
| **SOP Input** | Data sources, analysis requirements |
| **SOP Output** | Analysis report, visualizations |
```

Custom roles:
- MUST declare a trust tier
- MUST define SOP inputs and outputs
- MUST NOT override core role definitions
- MAY extend the sequential handoff chain

---

## 6) Relationship to Other Standards

| Standard | Relationship |
|:---|:---|
| `approval-policy.md` | Trust tier per role determines approval requirements |
| `workflow-pipelines.md` | Defines the sequence in which roles execute |
| `hooks-policy.md` | `PreTask` hook assigns the initial role |
| `quality-gates.md` | Each role has role-specific quality gates |
| `how-to-code-review.md` | Reviewer role follows these review standards |
| `how-to-strict-review.md` | Security reviewer follows strict review |
| `execution-policy.md` | Task lane maps to starting role |
