# AGENTS.md — Local Project Contract

Version: 1.10.0
Status: Normative / Local
Scope: `./**`

This file is the project-specific child contract.
The reusable `.agents` project is mounted in `.agents/.rules/`.
The mounted copy is the source of reusable rules for the child repo.

## 0) Operational Modes

1. **Standard Mode (Default)**:
    - Trigger: Any standard request (e.g., "do a review", "implement this", "refactor").
    - Scope: Strictly follow project-specific rules in `.agents/how-to/**`.
2. **Harness-Full Mode**:
    - Trigger: Explicit phrase **"uradi po pravilima .agents"**.
    - Scope: Include all rules and protocols from the entire `.agents/**` directory.

## 1) Order Of Precedence

Agents MUST follow this order:

1. `AGENTS.md`
2. `.agents/how-to/**`
3. `.agents/.rules/AGENTS.md`
4. `.agents/.rules/governance/core/quality/quality-gates.md`
5. `.agents/.rules/governance/core/resolution/profile-resolution-algorithm.md`
6. `.agents/.rules/governance/profiles/**`
7. `.agents/.rules/governance/architecture/**`
8. `.agents/.rules/governance/security/**`
9. `.agents/.rules/governance/execution/policy/execution-policy.md`
10. `.agents/.rules/governance/execution/routing/prompt-to-governance-flow.md`
11. `.agents/.rules/governance/execution/hooks/hooks-policy.md`
12. `.agents/.rules/governance/execution/approvals/approval-policy.md`
13. `.agents/.rules/governance/core/flags/feature-flags.md`
14. `.agents/.rules/governance/standards/review/how-to-code-review.md`
15. `.agents/.rules/governance/standards/review/how-to-strict-review.md`
16. `.agents/.rules/governance/standards/coding/how-to-coding-standards.md`
17. `.agents/.rules/governance/standards/coding/naming-standard.md`
18. `.agents/.rules/governance/standards/documentation/how-to-document-flow.md`
19. `.agents/.rules/governance/standards/documentation/how-to-document.md`
20. `.agents/.rules/governance/standards/governance/governance-authoring-standard.md`
21. `.agents/.rules/governance/standards/governance/governance-evolution-policy.md`
22. `.agents/.rules/governance/delivery/release/release-and-rollback-policy.md`
23. `.agents/.rules/governance/intelligence/memory/memory-lifecycle.md`
24. `.agents/.rules/governance/skills/contract/skill-contract.md`
25. `.agents/.rules/governance/agents/roles/agent-roles.md`
26. `.agents/.rules/governance/delivery/workflows/workflow-pipelines.md`
27. `.agents/.rules/governance/intelligence/context/context-management.md`
28. `.agents/.rules/governance/intelligence/learning/continuous-learning.md`
29. `.agents/.rules/governance/intelligence/learning/instincts-policy.md`
30. `.agents/.rules/governance/integrations/platforms/platform-compatibility.md`
31. `.agents/.rules/governance/integrations/mcp/mcp-integration-policy.md`
32. `.agents/.rules/governance/execution/sandbox/sandbox-boundary-policy.md`
33. `.agents/.rules/governance/agents/orchestration/society-of-mind-pattern.md`
34. `.agents/.rules/governance/delivery/operations/**`
35. `.agents/skills/**`
36. `.agents/management/ACTIVE.md`
37. `.agents/management/TIMELINE.md`
38. `.agents/management/TODO.md`
39. `.agents/management/BUGS.md`
40. `.agents/review/REVIEWS.md`
41. `README.md`
42. `docs/**`

## 1. Vision & Inspiration

Avax is not a clone; it is a synthesis of modern software engineering excellence. When designing, draw inspiration from:

- **Laravel**: For its legendary Developer Experience (DX).
- **Symfony**: For its robust, decoupled component philosophy.
- **Spring Boot**: For its rigorous, enterprise-grade architectural patterns.
- **ASP.NET Core**: For its high-performance, middleware-driven pipeline.
- **Phoenix (Elixir)**: For its runtime clarity and "Worker-Safety First" approach.
- **Go**: For its simplicity, explicitness, and avoidance of magic.

## 2. The Fundamental Law of Naming

Strictly follow this hierarchy for everything you create:

- **Folder** = Represents a **Flow** or a **Capability** (e.g., `RegisterUser/`, `QueryIntent/`).
- **File/Class** = Represents a specific **Responsibility** within that flow.
- **Method** = Represents a precise **Action**.

## 3. Strict Prohibitions (Zero Tolerance)

Do **NOT** use the following names for directories or namespaces:

- `Services`, `Helpers`, `Utils`, `Common`, `Shared`, `Managers`, `Core`, `Support`.

## 4. Local Definitions

1. **Canonical Validation Entrypoint**: `./vendor/bin/phpunit`
2. **Canonical Local Development Entrypoint**: `php avax serve`
3. **Canonical Release or Publish Entrypoint**: `./bin/release.sh`
4. **Project-Specific Architecture Boundaries**:
    - `components/`: Pure, high-performance logic.
    - `framework/System/Capabilities/`: Internal framework logic.
    - `framework/System/PublicSurface/`: User entry points.
    - `framework/System/Flows/`: Multi-component orchestrations.
5. **Applied Governance Stack**:
    - **Delivery Kind**: `PHP Framework`
    - **Applied Repository Profiles**: `governance-source`
    - **Languages**: `php`
    - **Frameworks Or Runtimes**: `avax`
    - **Applied Coding Profiles**: `.agents/.rules/governance/profiles/languages/php.md`
    - **Applied Architecture Profiles**: [declare explicitly]
    - **Security Lanes Required**: `security/**`
    - **Operations Lanes Required**: `delivery/operations/**`
6. **Project Workspace**:
    - `.agents/business-logic/`
    - `.agents/language-specific/`
    - `.agents/management/`
    - `.agents/hooks/`
    - `.agents/review/`
7. **Project-Specific Exceptions**:
    - None. Follow the Screaming Architecture rules strictly.

## 5. Source of Truth

1. CURRENT_TRUTH.md
2. Code-Review-And-ToDo/EXECUTION.md
3. TODO.md
4. .agents/how-to/*.md
5. Code-Review-And-ToDo/master-plan/*.md
6. Older review/archive files

## 6. Execution Rule

Only one stage may be active at a time.

No V2 implementation before V1 Kernel Green is proven.
No V3 implementation before V1 Kernel Green and V2 platform baseline are proven.

## 7. Required Validation

```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
php tooling/audit_broken_refs.php
php avax runtime:doctor
```

## 8. Agent Output Contract

Every agent execution must end with:

* Stage
* Status
* Files changed
* Validation commands
* Validation summary
* Remaining risks
* Next allowed action
