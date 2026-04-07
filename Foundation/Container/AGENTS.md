# AGENTS.md — Local Project Contract

Version: 1.10.0
Status: Normative / Local
Scope: `./**`

This file is the project-specific child contract.
The reusable `.agents` project is mounted in `.agents/.rules/`.
The mounted copy is the source of reusable rules for the child repo.
The visible `.agents/` folders are the project workspace skeleton.

## 0) Order Of Precedence

Agents MUST follow this order:

1. `AGENTS.md`
2. `.agents/.rules/AGENTS.md`
3. `.agents/.rules/governance/core/quality/quality-gates.md`
4. `.agents/.rules/governance/core/resolution/profile-resolution-algorithm.md`
5. `.agents/.rules/governance/profiles/**`
6. `.agents/.rules/governance/architecture/**`
7. `.agents/.rules/governance/security/**`
8. `.agents/.rules/governance/execution/policy/execution-policy.md`
9. `.agents/.rules/governance/execution/routing/prompt-to-governance-flow.md`
10. `.agents/.rules/governance/execution/hooks/hooks-policy.md`
11. `.agents/.rules/governance/execution/approvals/approval-policy.md`
12. `.agents/.rules/governance/core/flags/feature-flags.md`
13. `.agents/.rules/governance/standards/review/how-to-code-review.md`
14. `.agents/.rules/governance/standards/review/how-to-strict-review.md`
15. `.agents/.rules/governance/standards/coding/how-to-coding-standards.md`
16. `.agents/.rules/governance/standards/coding/naming-standard.md`
17. `.agents/.rules/governance/standards/documentation/how-to-document-flow.md`
18. `.agents/.rules/governance/standards/documentation/how-to-document.md`
19. `.agents/.rules/governance/standards/governance/governance-authoring-standard.md`
20. `.agents/.rules/governance/standards/governance/governance-evolution-policy.md`
21. `.agents/.rules/governance/delivery/release/release-and-rollback-policy.md`
22. `.agents/.rules/governance/intelligence/memory/memory-lifecycle.md`
23. `.agents/.rules/governance/skills/contract/skill-contract.md`
24. `.agents/.rules/governance/agents/roles/agent-roles.md`
25. `.agents/.rules/governance/delivery/workflows/workflow-pipelines.md`
26. `.agents/.rules/governance/intelligence/context/context-management.md`
27. `.agents/.rules/governance/intelligence/learning/continuous-learning.md`
28. `.agents/.rules/governance/intelligence/learning/instincts-policy.md`
29. `.agents/.rules/governance/integrations/platforms/platform-compatibility.md`
30. `.agents/.rules/governance/integrations/mcp/mcp-integration-policy.md`
31. `.agents/.rules/governance/execution/sandbox/sandbox-boundary-policy.md`
32. `.agents/.rules/governance/agents/orchestration/society-of-mind-pattern.md`
33. `.agents/.rules/governance/delivery/operations/**`
34. `.agents/skills/**`
35. `.agents/management/ACTIVE.md`
36. `.agents/management/TIMELINE.md`
37. `.agents/management/TODO.md`
38. `.agents/management/BUGS.md`
39. `.agents/review/REVIEWS.md`
40. `README.md`
41. `docs/**`

## 1. Local Definitions

Project-specific truth for this repository:

1. **Canonical Validation Entrypoint**: `find . -name '*.php' -not -path './.agents/*' -print0 | xargs -0 -n1 php -l`
2. **Canonical Local Development Entrypoint**: `None; this is a library component with no standalone runtime.`
3. **Canonical Release or Publish Entrypoint**: `None; release and publish are handled at the parent package or release process level.`
4. **Project-Specific Architecture Boundaries**: `Container.php`, `Config/`, `Core/`, `Features/`, `Guard/`, `Http/`, `Observe/`, `Providers/`, `Tools/`, `docs/`
5. **Applied Governance Stack**:
   - **Delivery Kind**: `library`
   - **Applied Repository Profiles**: `none`
   - **Languages**: `php`
   - **Frameworks Or Runtimes**: `none`
   - **Applied Coding Profiles**: `.agents/.rules/governance/profiles/languages/php.md`
   - **Applied Architecture Profiles**: `.agents/.rules/governance/architecture/profiles/languages/php.md`
   - **Security Lanes Required**: `security/**`
   - **Operations Lanes Required**: `delivery/operations/**`
6. **Project Workspace**:
   - `.agents/business-logic/`
   - `.agents/language-specific/`
   - `.agents/management/`
   - `.agents/hooks/`
   - `.agents/review/`
7. **Project-Specific Exceptions or Forbidden Shortcuts**:
   - Do not add new documentation at the repository root; use `docs/` for new documentation.
   - Treat the existing root `how-to-*.md` files as legacy compatibility docs, not as the pattern to extend.
   - There is no standalone runtime or publish entrypoint from this component root.

Keep this file short. Long procedures belong in governance docs, and active
queues belong in `.agents/management/**`.

---
*No offload recommended for this step.*
