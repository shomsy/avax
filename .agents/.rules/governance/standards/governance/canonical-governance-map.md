# Canonical Governance Map

Version: 3.0.0
Status: Normative

This is the single canonical map of the AI-Native Enterprise SDLC Operating System governance structure.

## Core Governance (`.agents/governance/core/`)
- `quality-gates.md`: The universal filter for safety and correctness.
- `bootstrap/agent-bootstrap.md`: The mandatory agent startup sequence.
- `resolution/profile-resolution-algorithm.md`: Deterministic rule loading.
- `flags/feature-flags.md`: Feature enablement.

## Profiles (`.agents/governance/profiles/`)
- `languages/`: Syntax, build, test rules per language (PHP, JS, TS, Go).
- `frameworks/`: Framework-specific conventions (React, Laravel).
- `project-types/`: Shape assumptions (web-app, library, cli, monorepo).
- `repository-kinds/`: Repo-level assumptions.
- `roles/`: Reviewer/Persona overlays.

## Architecture (`.agents/governance/architecture/`)
- `architecture-standard.md`: Reusable structural baseline.
- `profiles/`: Framework-level structural overlays.

## Execution (`.agents/governance/execution/`)
- `policy/execution-policy.md`: Explore vs Execute modes.
- `routing/prompt-to-governance-flow.md`: Orchestration routing.
- `sandbox/sandbox-boundary-policy.md`: Execution isolation.
- `approvals/approval-policy.md`: Human escalation boundaries.
- `hooks/hooks-policy.md`: Runtime hooks for enforcement.

## Standards (`.agents/governance/standards/`)
- `coding/how-to-coding-standards.md`: Universal code style & quality.
- `review/recursive-review-contract.md`: The loop to achieve FULL_GREEN.
- `documentation/evidence-model.md`: V3 Human/Machine evidence split.

## Security (`.agents/governance/security/`)
- `owasp-web-and-api-baseline.md`, `ci-cd-and-supply-chain-security.md`, etc.
- Defines HIGH/BLOCKER boundaries and OWASP escalation limits.

## Delivery (`.agents/governance/delivery/`)
- `operations/management-model.md`: V3 Management (CURRENT, TODO, RISKS, etc.).
- `release/release-and-rollback-policy.md`: Release governance.
- `workflows/workflow-pipelines.md`: CI/CD flow integration.

## Intelligence (`.agents/governance/intelligence/`)
- `memory/`, `context/`, `learning/`: Long-term AI knowledge retention.

## Agents (`.agents/governance/agents/`)
- `roles/agent-roles.md`, `orchestration/society-of-mind-pattern.md`: Multi-agent definitions.
