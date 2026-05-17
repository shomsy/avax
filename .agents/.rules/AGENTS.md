# .agents/AGENTS.md — Global Agent Harness Master Contract

Version: 3.0.0
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
2. **`.agents/AGENTS.md`** (this file — global shared rules)
3. `.agents/governance/core/quality/quality-gates.md`
4. `.agents/governance/core/bootstrap/agent-bootstrap.md`
5. `.agents/governance/core/resolution/profile-resolution-algorithm.md`
6. `.agents/governance/standards/documentation/evidence-model.md`
7. `.agents/management/TODO.md` | `.agents/management/BUGS.md`
8. `EVIDENCE/**`
9. `README.md`

**Governance directories** (files within these are loaded on demand by profile
resolution, not by precedence order):

- `.agents/governance/profiles/**` — language, framework, project-type profiles
- `.agents/governance/architecture/**` — architecture profiles and standards
- `.agents/governance/security/**` — threat models, abuse cases, security lanes
- `.agents/governance/delivery/operations/**` — runbooks, operational procedures
- `.agents/governance/core/flags/` — feature flag definitions

All other governance paths (execution policy, routing, approvals, coding
standards, review contracts, memory lifecycle, skill contracts, agent roles,
workflows, context management, learning, integrations, sandbox policy,
orchestration patterns) are **optional** and only apply when the adopting
repository creates them. The precedence chain MUST NOT reference files that
do not exist on disk.

## 1) Agent Bootstrap

Every agent MUST follow the bootstrap sequence defined in
`.agents/governance/core/bootstrap/agent-bootstrap.md` before starting work.

The bootstrap sequence is:
1. Read `AGENTS.md`
2. Resolve applicable governance stack
3. Load selected profiles
4. Load local overlays
5. Read management state
6. Execute work
7. Update machine evidence
8. Update human dashboard
9. Run recursive governance review before commit

## 2) Agent OS Repository Structure

Any project using this OS is divided into specialized domains within the
`.agents/` folder:

| Domain | Responsibility | Reusable |
|:---|:---|:---|
| **`governance/`** | Feature-first reusable rule system grouped into core, architecture, execution, standards, intelligence, integrations, delivery, product, profiles, and security. | **Yes** |
| **`config/`** | Machine-readable project configuration for profile resolution. | **Yes** |
| **`business-logic/`** | Placeholder for child-repo domain behavior and product-specific rules. | Template only |
| **`language-specific/`** | Placeholder for child-repo local stack rules when reusable profiles are insufficient. | Template only |
| **`management/`** | Active planning, task tracking, and delivery evidence. | **Yes** |
| **`templates/`** | Standardized blueprints for reviews, planning, and ADRs. | **Yes** |
| **`review/`** | Canonical review findings and archive. | **Yes** |
| **`glossary/`** | Shared vocabulary, naming dictionaries, and term definitions. | **Yes** |
| **`onboarding/`** | Guided flows for safe project adoption and contributor entry. | **Yes** |
| **`hooks/`** | Reusable runtime hook entrypoints for session bootstrap, trust checks, and observation capture. | **Yes** |
| **`skills/`** | Reusable agent-facing command and workflow definitions. | **Yes** |

## 3) Evidence Model (V2)

Evidence is split into two layers:

- **`EVIDENCE/`** — human-readable dashboard at project root. Summaries only.
- **`.agents/management/evidence/`** — machine evidence. Verbose, detailed.

Rules: root dashboard summarizes and links. Raw data stays in machine evidence.
Evidence duplication is forbidden. See `.agents/governance/standards/documentation/evidence-model.md` for full spec.

## 4) Non-Negotiable Rules (Shared)

1. **Execution mode is strict by default**: implement and validate; no silent redesign.
2. **One responsibility, one implementation**: duplicate truth is forbidden.
3. **Security first**: do not weaken runtime, dependency, or secret handling.
4. **Backlog ownership**: all tasks belong in `.agents/management/TODO.md` or `.agents/management/BUGS.md` with timestamped status updates.
5. **Evidence is mandatory and timestamped**: no evidence means incomplete work.
6. **DoD = implementation + validation + evidence + backlog update + ceremony.**
7. **What can be automated MUST be automated or gated.**
8. **Production-ready claims require proof**: review, validation, rollback, and operational evidence must agree.
9. **Explore mode vs Execute mode**: Maintain a clear boundary between read-only discovery (Explore) and code-modifying implementation (Execute).
10. **Every agent MUST return an artifact**: No raw conversational logs as the primary output; all sub-agents must emit a structured result.
11. **Recursive review before commit**: Follow the recursive review contract. Passing tests alone is not commit permission.

## 5) Common Operating Flow

1. Bootstrap (read AGENTS.md, resolve stack, load profiles).
2. Classify the task lane.
3. Map it to the correct queue.
4. Implement the minimal safe delta.
5. Run validation.
6. Record evidence (machine evidence first, then human dashboard).
7. Run recursive governance review.
8. Fix findings, revalidate, re-review until clean.
9. Commit only when the review loop is clean.

## 6) Required Project Definitions (Must Be In Root `AGENTS.md`)

Each adopting repository MUST specify:

1. canonical validation entrypoint
2. canonical local development entrypoint
3. canonical release or publish entrypoint
4. project-specific architecture boundaries
5. applied governance stack: repository profiles, languages, frameworks,
   project types, architecture overlays, required SDLC lanes, and runtime
   obligations
