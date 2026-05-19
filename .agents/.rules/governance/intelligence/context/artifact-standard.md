# Artifact Contract — Structured Handoff Standard

Version: 1.0.0
Status: Normative
Scope: `.agents/tasks/**`, `.agents/management/artifacts/**`

This document defines the mandatory **9-field schema** that every specialized sub-agent (Mapper, Docs Researcher) MUST return. This structure ensures that the Supervisor and Codex Executor receive optimized, actionable inputs.

---

## 1) The 9-Field Schema

Every sub-task result MUST be formatted as a structured Markdown or JSON body containing:

| Field | Content |
|:---:|:---|
| **`task_type`** | The nature of the sub-task (Mapping, Research, Debug). |
| **`goal`** | The primary objective the sub-agent was pursuing. |
| **`relevant_files`** | Exact file paths needed for the next step (max 5). |
| **`excluded_files`** | Files analyzed but found IRRELEVANT (prevents duplication). |
| **`constraints`** | Any limitations discovered during the task. |
| **`docs_findings`** | Key API/Documentation facts (no raw dumps). |
| **`risks`** | Any potential side effects or blockers identified. |
| **`recommended_next_agent`** | Which role should handle the next stage (e.g., Codex). |
| **`acceptance_criteria`** | Definition of Done for this sub-task. |

## 2) Why Use This Format?

- **Token Efficiency**: Prevents "bottlenecking" Codex with raw chat logs.
- **Traceability**: Simple verification of whether the sub-agent met its goal.
- **Automation**: Allows the Supervisor to parse and route the next step automatically.

---

## 3) Example: Mapper Output

```markdown
### Sub-Task Artifact — [session-123-mapping]

- **task_type**: Mapping
- **goal**: Locate the theme-switching logic.
- **relevant_files**:
  - `src/theme/ThemeContext.tsx`
  - `src/components/ThemeToggle.tsx`
- **excluded_files**:
  - `src/index.css` (global styles only)
- **constraints**: React Context is used, not Redux.
- **docs_findings**: ThemeProvider wraps the root component.
- **risks**: Changing the context may cause jitter in non-memoized children.
- **recommended_next_agent**: Codex Executor
- **acceptance_criteria**: Mapping complete; files identified for refactor.
```

---

## 4) Relationship to Other Standards

| Standard | Relationship |
|:---|:---|
| `agent-roles.md` | Defines the roles that produce these artifacts. |
| `workflow-pipelines.md` | Defines the handoff points for artifacts. |
| `subagent-delegation-policy.md` | Defines how to trigger the production of a brief. |
