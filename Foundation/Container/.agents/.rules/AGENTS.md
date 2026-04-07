# .agents/AGENTS.md — Global Agent Harness Master Contract

Version: 2.4.0
Status: Normative / Universal
Scope: `./**`

This file is the reusable base contract for any repository that adopts the
**Agent Harness** operating system scaffold.

It must stay generic. Product-specific, team-specific, or repository-specific
rules belong in the adopting repo's root `AGENTS.md` or in child-repo
placeholders after installation.

## 0) Order Of Precedence (Universal Model)

In any project using the `.agents` OS, agents MUST follow this order:

1. **`AGENTS.md`** (project-specific local overrides)
2. **`.agents/AGENTS.md`** (this file - global shared rules)
3. `.agents/governance/core/quality/quality-gates.md`
4. `.agents/governance/core/resolution/profile-resolution-algorithm.md`
5. `.agents/governance/profiles/**`
6. `.agents/governance/architecture/**`
7. `.agents/governance/security/**`
8. `.agents/governance/execution/policy/execution-policy.md`
9. `.agents/governance/execution/routing/prompt-to-governance-flow.md`
10. `.agents/governance/execution/hooks/hooks-policy.md`
11. `.agents/governance/execution/approvals/approval-policy.md`
12. `.agents/governance/core/flags/feature-flags.md`
13. `.agents/governance/standards/review/how-to-code-review.md`
14. `.agents/governance/standards/review/how-to-strict-review.md`
15. `.agents/governance/standards/coding/how-to-coding-standards.md`
16. `.agents/governance/standards/coding/naming-standard.md`
17. `.agents/governance/standards/documentation/how-to-document-flow.md`
18. `.agents/governance/standards/documentation/how-to-document.md`
19. `.agents/governance/standards/governance/governance-authoring-standard.md`
20. `.agents/governance/standards/governance/governance-evolution-policy.md`
21. `.agents/governance/delivery/release/release-and-rollback-policy.md`
22. `.agents/governance/intelligence/memory/memory-lifecycle.md`
23. `.agents/governance/skills/contract/skill-contract.md`
24. `.agents/governance/agents/roles/agent-roles.md`
25. `.agents/governance/delivery/workflows/workflow-pipelines.md`
26. `.agents/governance/intelligence/context/context-management.md`
27. `.agents/governance/intelligence/learning/continuous-learning.md`
28. `.agents/governance/intelligence/learning/instincts-policy.md`
29. `.agents/governance/integrations/platforms/platform-compatibility.md`
30. `.agents/governance/integrations/mcp/mcp-integration-policy.md`
31. `.agents/governance/execution/sandbox/sandbox-boundary-policy.md`
32. `.agents/governance/agents/orchestration/society-of-mind-pattern.md`
33. `.agents/governance/delivery/operations/**`
34. `.agents/management/TODO.md` | `.agents/management/BUGS.md`
35. `README.md`

## 1) Agent OS Repository Structure

Any project using this OS is divided into specialized domains within the
`.agents/` folder:

| Domain | Responsibility | Reusable |
|:---|:---|:---|
| **`governance/`** | Feature-first reusable rule system grouped into core, architecture, execution, standards, intelligence, integrations, delivery, product, profiles, and security. | **Yes** |
| **`business-logic/`** | Placeholder for child-repo domain behavior and product-specific rules. | Template only |
| **`language-specific/`** | Placeholder for child-repo local stack rules when reusable profiles are insufficient. | Template only |
| **`management/`** | Active planning, task tracking, and delivery evidence. | **Yes** |
| **`templates/`** | Standardized blueprints for reviews, planning, and ADRs. | **Yes** |
| **`review/`** | Canonical review findings and archive. | **Yes** |
| **`glossary/`** | Shared vocabulary, naming dictionaries, and term definitions. | **Yes** |
| **`onboarding/`** | Guided flows for safe project adoption and contributor entry. | **Yes** |
| **`hooks/`** | Reusable runtime hook entrypoints for session bootstrap, trust checks, and observation capture. | **Yes** |
| **`skills/`** | Reusable agent-facing command and workflow definitions. | **Yes** |

## 2) Non-Negotiable Rules (Shared)

1. **Execution mode is strict by default**: implement and validate; no silent redesign.
2. **One responsibility, one implementation**: duplicate truth is forbidden.
3. **Security first**: do not weaken runtime, dependency, or secret handling.
4. **Backlog ownership**: all tasks belong in `.agents/management/TODO.md` or `.agents/management/BUGS.md` with timestamped status updates.
5. **Evidence is mandatory and timestamped**: no evidence means incomplete work.
6. **DoD = implementation + validation + evidence + backlog update + ceremony.**
7. **What can be automated MUST be automated or gated.**
8. **Production-ready claims require proof**: review, validation, rollback, and operational evidence must agree.
9. **Codex is not the default agent for all tasks**: Use specialized sub-agents (Mapper, Docs Researcher) before involving Codex for execution.
10. **Explore mode vs Execute mode**: Maintain a clear boundary between read-only discovery (Explore) and code-modifying implementation (Execute).
11. **Every agent MUST return an artifact**: No raw conversational logs as the primary output; all sub-agents must emit a structured result.

## 3) Common Operating Flow

1. Classify the task lane.
2. Resolve the governance stack and profile pack.
3. Map it to the correct queue.
4. Implement the minimal safe delta.
5. Run validation.
6. Record evidence.
7. Perform the completion ceremony required by the adopting repository.

## 4) Required Project Definitions (Must Be In Root `AGENTS.md`)

Each adopting repository MUST specify:

1. canonical validation entrypoint
2. canonical local development entrypoint
3. canonical release or publish entrypoint
4. project-specific architecture boundaries
5. applied governance stack: repository profiles, languages, frameworks,
   architecture overlays, required SDLC lanes, and runtime obligations
