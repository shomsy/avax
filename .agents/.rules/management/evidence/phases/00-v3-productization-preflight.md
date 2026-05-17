# V3 Productization Pre-Flight & Truth Reconciliation

Date: 2026-05-16
Phase: 00
Status: Complete

## 1. Current Architecture Map
The harness is currently structured in three layers:
- **Local Project Layer**: `AGENTS.md` and `.agents/` overrides
- **Profile Layer**: Optional overlays (`.agents/governance/profiles/`)
- **Parent / Core Governance**: Universal rules (`.agents/governance/core/`, `execution/`, `standards/`, etc.)

Directories:
- `.agents/config/` (project.json)
- `.agents/governance/` (core, architecture, delivery, execution, integrations, intelligence, product, profiles, security, skills, standards, agents)
- `.agents/management/` (CURRENT, ACTIVE, TODO, BUGS, DECISIONS, RISKS, STATUS, evidence/)
- `EVIDENCE/` (human dashboard)
- `scaffolds/` (templates for new projects)
- `tests/` (smoke tests)
- `projects/` (legacy vacuumed data)

## 2. Current Governance Map
Governance is categorized into 12 domains inside `.agents/governance/`:
1. `core/` (bootstrap, flags, quality, resolution)
2. `architecture/` (standards, profiles)
3. `execution/` (approvals, hooks, policy, routing, sandbox)
4. `standards/` (coding, documentation, governance, review)
5. `security/` (OWASP, incident response, etc.)
6. `delivery/` (operations, release, workflows)
7. `intelligence/` (context, learning, memory)
8. `profiles/` (languages, frameworks, project-types, repository-kinds, roles)
9. `agents/` (orchestration, roles)
10. `integrations/` (mcp, platforms)
11. `skills/` (contract)
12. `product/` (product management standard)

## 3. Current Profile System
- **Languages**: `php`, `javascript`, `typescript`, `css`, `nodejs`, `go` (stub)
- **Frameworks**: `laravel`, `express`, `react`, `nextjs`, `v-web-components`
- **Project Types**: `web-app`, `library`, `cli`, `api-service`, `monorepo`
- **Repository Kinds**: `governance-source`

## 4. Current Evidence Lifecycle
- **Human Dashboard**: `EVIDENCE/` with `CURRENT.md`, `ACTIVE_PLAN.md`, `FLOW.md`, `CHANGELOG.md`, `DONE.md`, `LINKS.md`.
- **Machine Evidence**: `.agents/management/evidence/` with `phases/`, `reviews/`, `raw/`, `validation/`, `security/`, `performance/`, `releases/`, `truth/`.

## 5. Current Management Lifecycle
- `CURRENT.md`: Operational truth
- `ACTIVE.md`: Active work board
- `TODO.md`: Planned queue
- `BUGS.md`: Defect queue
- `DECISIONS.md`: ADR-lite
- `RISKS.md`: Accepted debt/risks
- `STATUS.md`: GREEN/YELLOW/RED snapshot

## 6. Current Orchestration Model
- Resides in `.agents/governance/agents/orchestration/society-of-mind-pattern.md`.
- Very basic routing based on prompt-to-governance flow. Under-specified operationally.

## 7. Current Trust Model
- Basic sandbox boundary and approval policy.
- Lacks enterprise-grade trust tiers and explicit operational boundaries for dangerous classes.

## 8. Current Release Model
- Basic release and rollback policy (`.agents/governance/delivery/release/`).
- Missing staged rollout, canary lifecycle, freeze states, production verification.

## 9. Current Installer Model
- Shell script (`install-os.sh`).
- Missing idempotent migration, dry-run mode, deep profile selection during init.

## 10. Gap Analysis & Reconciliations
### Duplication Analysis
- Minor duplications between `AGENTS.md` expectations and local `project.json` config.
- `PARENT-AGENTS` vs `.agents` V3 models need full reconciliation to eliminate split-brain issues.

### Contradiction Analysis
- Execution mode relies heavily on inference, contradicting the "Execute mode requires explicit profile declaration" enterprise standard.

### Missing SDLC Lifecycle Analysis
- Incident management, rollback management, hotfix flows, postmortem lifecycle, audit readiness.

### Operational Gaps
- Sub-agent replayability, failure recovery lifecycle, orchestration artifact contracts.

### Anti-Entropy Gaps
- No mechanisms to detect stale governance, orphan rules, contradictory rules, or dead evidence.

### Enterprise-Readiness Gaps
- Missing capability maturity model.
- Security escalation model is not strict enough (needs explicit OWASP-class escalation).

### AI-Native Workflow Gaps
- Knowledge retention / memory lifecycle is incomplete. Context poisoning prevention is missing.

### Scalability Gaps
- Multi-agent orchestration needs strict routing rules and aggregation evidence.

### Maintainability Gaps
- Long-term repository entropy control is missing.
