# Skill: Sub-Agent Task Delegation

This skill provides instructions for a **Supervisor Agent** (the main AI) on how to
delegate complex or token-heavy sub-tasks to a specialized **Sub-Agent**.

## Purpose
- Reduce main session token count by offloading specific file modifications.
- Focus on a single sub-component with high precision.
- Implement the "Society of Mind" pattern by acting as a manager.

## Delegation Triggers
- You are at > 50% of your context window.
- The task involves modifying a single component or file in isolation.
- The task is repetitive (e.g., "Add logging to these 5 files").
- You need a "second opinion" or a "reviewer" to check your work.

## How to Delegate
1. **Define the Mandate**: Be extremely specific about the GOAL and the EXIT CRITERIA.
2. **Select Target Files**: Only include files that are absolutely necessary.
3. **Select Client Profile**: Choose the best fit (Cline for research, OpenCode for restricted sub-tasks, Blackbox for remote parallelism).
4. **Execute Dispatcher**: Run the `subagent-dispatch.sh` script to prepare the sub-session.
5. **Handoff**: Use the generated `pruned-context.md` as the direct prompt for the sub-agent.
6. **Merge**: Once the sub-agent completes the task, read the artifacts and update the main session.

## Tool Execution Example (Internal)
If you decide to delegate "Refactoring `Navbar.tsx`":
- **Mandate**: "Refactor Navbar.tsx to use the new Theme provider. Do not change any other files."
- **Files**: "src/components/Navbar.tsx"
- **Budget**: "5k tokens"

```bash
/home/shomsy/projects/agent-harness/.agents/hooks/subagent-dispatch.sh \\
  --task "Refactor Navbar.tsx" \\
  --files "src/components/Navbar.tsx" \\
  --budget "5k"
```

## Exit Criteria for Sub-Agent
The sub-agent must return the MODIFIED file content and a brief `result.json`
summarizing the changes made.
