# Codex Usage Policy — Specialized Execution Standard

Version: 1.0.0
Status: Normative
Scope: `.agents/**`

This document defines the strategic boundaries for using **Codex** (or comparable high-reasoning models) within the Agent OS. The goal is to maximize the value per token by reserving Codex for complex implementation while offloading research to cheaper "Mapper" agents.

---

## 1) Core Philosophy: Specialist, Not Secretary

Codex is a highly skilled, expensive specialist. It should not be used for tasks that do not require deep reasoning or wide-scale repository modification.

## 2) When to Use Codex (High-Value)

Codex MUST be used for:
- **Feature Implementation**: Writing new modules or complex logic.
- **Deep Refactoring**: Structural changes across multiple files.
- **Repo-Wide Debugging**: Issues requiring reasoning across disconnected components.
- **Final Integration**: Merging inputs from multiple sub-agents into a cohesive whole.
- **Architectural Review**: Providing a technical judgment on complex designs.

## 3) When NOT to Use Codex (Offload Target)

Codex SHOULD NOT be used for:
- **Repository Mapping**: Finding file paths or explaining directory structures.
- **Initial Documentation Read**: Summarizing API docs or READMEs.
- **Log Triage**: Parsing large CI/CD logs or build summaries.
- **Task Classification**: Deciding which agent should start the task.
- **"Exploration" Queries**: "What does this file do?" without an intent to change code.

**Rule**: If a task can be solved in **Explore Mode** (Read-only), it should be handled by a **Mapper** or **Docs Researcher** using a mini/cheap model.

## 4) Prerequisite: The Compressed Brief

Codex Executor should **never** start a complex task with raw user prompts and the whole repository in context. It requires:
1.  **A Clear Mandate**: Specific goal and non-goals.
2.  **A Pruned Context**: Only the 2-5 files identified by the Mapper.
3.  **API/Docs Summary**: Key findings from the Docs Researcher.
4.  **Acceptance Criteria**: Concrete definitions of success.

---

## 5) Relationship to Other Standards

| Standard | Relationship |
|:---|:---|
| `agent-roles.md` | Codex maps to the **Codex Executor** and **Reviewer** roles. |
| `subagent-delegation-policy.md` | Defines the mechanism for preparing the brief. |
| `execution-policy.md` | Defines the **Execute Mode** boundary. |
