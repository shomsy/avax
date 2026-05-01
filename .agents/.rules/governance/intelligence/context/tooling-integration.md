# Tooling Integration — Cline, Blackbox, and Cursor

Version: 1.0.0
Status: Normative
Scope: `.agent/**`, `.agents/**`

This guide defines how to use the **Agent Harness** in conjunction with popular
VSCode AI extensions to reduce token costs and improve task focus.

---

## 0) Client Profiles — Choosing the Right Engine

The harness produces standardized sub-agent briefs (pruned context), which can
be mapped to different clients depending on the task shape:

| Client | Best Fit | Harness Use |
|:---|:---|:---|
| **Cline** | Read-only exploration, long research tasks, context trimming | Use subagents for file mapping, inventory, and discovery. |
| **OpenCode** | Primary/subagent orchestration, task permissions, hidden helper agents | Use briefs as inputs for restricted `mode: subagent` agents. |
| **Blackbox** | Remote multi-agent execution and branch-level parallel work | Use for branch-level autonomous execution. |
| **Cursor** | High-precision coding and in-line refactoring | Use `@` file references to the generated briefs. |

---

## 1) The "Handoff" Workflow

When the main session (Supervisor) is cluttered, use this workflow to delegate
a task to a VSCode extension:

1.  **Generate Pruned Context**:
    Run `./.agents/hooks/subagent-dispatch.sh` with specific files.
    *Result: A new folder in `.agent/sessions/<ID>/tasks/`.*

2.  **Open the Pruned Context**:
    Open `pruned-context.md` in VSCode.

3.  **Start the Extension**:
    - **Cline**: Open a new Cline session and paste the content of
      `pruned-context.md` as the first message.
    - **OpenCode**: Map the brief to a `mode: subagent` agent. Ensure
      `permission.task` is isolated to the sub-agent task directory.
    - **Blackbox/Cursor**: Highlight the content and ask the agent to
      "Solve this sub-task based on the context below."

4.  **Merge Result**:
    Once the extension modifies the files, the Supervisor (Antigravity/Codex)
    detects the changes and continues with the main task.

---

## 2) Extension-Specific Tips

### Cline (Recursive Delegation)
Cline is particularly good at "Recursive Delegation". If you use Cline as your
main agent, it can use the `subagent-dispatch.sh` tool to spawn *its own*
sub-agents, keeping its own context window clean.

### Blackbox (Autonomous Parallelism)
Use Blackbox when you want branch-level autonomous execution outside the local context loop.

### OpenCode (Hidden Helper Agents)
OpenCode is ideal for "Background" sub-tasks where permissions are restricted
(e.g., restricted to specific directory or `permission.task` flags).

---

## 3) Integrating with VSCode Tasks

We provide a `.vscode/tasks.json` that adds a "Dispatch Sub-Agent" action.

**To run it**:
1.  Press `Ctrl + Shift + P`.
2.  Type `Tasks: Run Task`.
3.  Select `Dispatch Sub-Agent (Pruned Context)`.

---

## 4) Troubleshooting

- **Context too small**: If the sub-agent lacks info, add more files to the
  `--files` argument in the dispatcher command.
- **Lost Governance**: Ensure the sub-agent is told to look at `AGENTS.md`
  for the master contract.
