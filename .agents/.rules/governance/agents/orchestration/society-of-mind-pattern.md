---
description: "Society of Mind - Hierarchical Team Encapsulation"
version: 1.0.0
---

# Society of Mind Pattern

As multi-agent orchestration becomes more complex, a "flat" structure where the supervisor manages 15 discrete agents (Planner, Reviewer, Tester, etc.) quickly exhausts the context window and reduces overall task reasoning. The Agent OS introduces the **Society of Mind (SoM)** pattern, utilizing **Sub-Agent Delegation** to optimize token usage.

## 1. What is a Society of Mind?

The **Society of Mind** treats a sub-team of specialized agents as a *singular entity* relative to its supervisor. It encapsulates complexity.

**Example**: Instead of the Supervisor coordinating `FrontendCoder`, `UIReviewer`, and `TesterAgent` independently, the Supervisor interacts exclusively with a `UITeam` Agent. 
Under the hood, `UITeam` is a Society of Mind consisting of its own manager and the three sub-agents.

## 2. Encapsulation Rules

- **Information Hiding**: The Supervisor agent only sees the final structured output from the SoM block, not the internal bickering. For instance, if `FrontendCoder` fails tests and `TesterAgent` yells at them, the Supervisor does not see the error loop, only the final functioning `Component.tsx`.
- **Atomic Tool Representation**: A Society of Mind should expose itself to the broader platform as a standard Tool or Endpoint (e.g., `@UITeam(Task: "Create user avater component")`).
- **Parallel Dispatch**: By using SoM boundaries, the Supervisor can dispatch tasks to the `@UITeam`, the `@BackendTeam`, and the `@DbTeam` simultaneously because their internal contexts are fully decoupled.
- **Token Budgeting**: Each SoM block must operate within a predefined context budget as per the `subagent-delegation-policy.md`.

## 3. Designing a SoM Team

A valid SoM team requires:
1. **Entry Interface**: A lead conversational agent that receives the mandate and can negotiate scope.
2. **Execution Pod**: At minimum, a Creator role (e.g. `Implementer`) and a Validator role (e.g. `Reviewer`).
3. **Structured Exit Point**: The team must not return raw conversational logs. It must return a final artifact (`PRD.json`, passing test output, or a completed file path).

*Reference Note: This model is heavily inspired by AutoGen's `MagenticOne` and `SocietyOfMindAgent` classes, adapted as an OS-level governance policy. For implementation details, see `../../intelligence/context/subagent-delegation-policy.md`.*
