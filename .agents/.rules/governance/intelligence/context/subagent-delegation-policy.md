# Sub-Agent Delegation Policy — Hierarchical Context Management

Version: 1.0.0
Status: Normative
Scope: `.agent/**`, `.agents/**`

This document defines how a **Supervisor Agent** (e.g., the main AI interface)
should delegate sub-tasks to **Sub-Agents** to minimize token usage and
maximize focus within the parent's context window.

---

## 1) Delegation Triggers

A Supervisor should trigger sub-agent delegation when:
- **Component isolation**: A task can be solved by modifying only 1-2 files.
- **Context pressure**: The main session is > 60% of its context window.
- **Spec-to-impl**: Generating detailed boilerplate from a high-level plan.
- **Repetitive tasks**: Applying the same refactor across multiple files.

## 2) Context Pruning Strategy

Before launching a sub-agent, the Supervisor MUST prune the context to the
absolute minimum required for that specific sub-task.

| Context Type | Pruning Rule |
|:---|:---|
| **Governance** | Include ONLY `AGENTS.md` and standard-specific rules. |
| **Code** | Include ONLY the target file(s) and their direct dependencies. |
| **History** | DO NOT include previous session logs; only the current "Mandate". |
| **Rules** | Include only the relevant lane rules (e.g., just `coding` rules). |

## 3) Sub-Agent Token Budgets (Virtual)

When delegating, the Supervisor should define a "Virtual Token Budget":

- **Micro (1-5k tokens)**: Quick fixes, refactors, single file changes.
- **Feature (10-30k tokens)**: New component creation, unit tests.
- **Research (30k+ tokens)**: Large audits, documentation generation.

## 4) Client Profiles

Different clients expose sub-agent capabilities in different ways. The
Supervisor should prefer the client that matches the task shape:

| Client | Best Fit | Harness Use |
|:---|:---|:---|
| **Cline** | Read-only exploration, long research tasks, context trimming | Use subagents for file mapping, inventory, and discovery. |
| **OpenCode** | Primary/subagent orchestration, task permissions, hidden helper agents | Use harness-generated briefs as `mode: subagent` agent inputs. |
| **Blackbox** | Remote multi-agent execution and branch-level parallel work | Use when the task should execute outside the local context loop. |

The harness should not assume one client can do all three efficiently. Instead,
it should emit the same subagent brief structure and let the client adapter
map it to the local execution surface.

## 5) Delegation Workflow (Standard)

1. **Mandatory Checkpoint**: Supervisor saves state.
2. **Context Selection**: Supervisor selects 2-3 specific files.
3. **Mandate Creation**: Supervisor writes `subtask.json` with clear exit criteria.
4. **Sub-Agent Invocation**: Launcher script runs the sub-task.
5. **Result Merging**: Supervisor reads artifacts and integrates changes.

---

## 6) External Tooling Compatibility (Cline, Blackbox, Cursor, OpenCode)

Sub-agents do not need to be internal scripts. External extensions can serve
as highly effective "Execution Pods":

- **Cline**: Use the generated `pruned-context.md` as the initial request or
  feed the task into Cline subagents for read-only exploration.
- **OpenCode**: Map the briefs to `mode: subagent` agents and lock down
  `permission.task` so only the harness-approved subagents can be launched.
- **Blackbox**: Use the generated briefs with remote agent / MCP tasks when you
  want branch-level autonomous execution.
- **Cursor**: Use `Ctrl+L` and reference the `pruned-context.md` file using `@`.

## 7) Relationship to Society of Mind

This policy implements the **Atomic Tool Representation** rule from the
`Society of Mind Pattern`. Every sub-agent delegation should be treated by
the Supervisor as a single "Tool Call" that returns a result.
