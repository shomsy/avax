# .agents

This folder is the reusable agent operating system project.

In the `agent-harness` source repository, every file under `.agents/` must
stay agnostic across unrelated projects. Child repos copy this entire tree into
`.agents/.rules/`, then fill the visible `.agents/` scaffold with project-local
workspace files.

## Domains

| Domain | Responsibility | Reusable |
|--------|----------------|----------|
| `governance/` | Feature-first reusable rules grouped into core, architecture, execution, standards, intelligence, integrations, delivery, product, profiles, and security | Yes |
| `language-specific/` | Placeholder for child-repo local stack rules when reusable profiles are not enough | Template only |
| `business-logic/` | Placeholder for child-repo domain meaning and product rules | Template only |
| `management/` | Active planning, defects, decisions, timeline policy, risks, evidence, and test records | Yes |
| `review/` | Review logs and archive | Yes |
| `templates/` | Reusable templates for tasking, planning, review, ADR, and DoD | Yes |
| `glossary/` | Shared vocabulary for terms and naming | Yes |
| `onboarding/` | Bootstrap and contributor operating flow | Yes |
| `hooks/` | Reusable runtime hook entrypoints that connect policy to execution | Yes |

## Required Questions

Every idea, plan, architecture decision, and release-impacting change must answer:

1. What outcome should the user or operator achieve?
2. What should the system do to make that outcome reliable?
3. What should be visible at the interface, API, or artifact boundary?
4. How will correctness, risk, and recovery be verified?

## Reading Order

1. `../AGENTS.md`
2. `governance/README.md`
3. `governance/core/quality/quality-gates.md`
4. `governance/core/resolution/profile-resolution-algorithm.md`
5. `governance/architecture/README.md`
6. `governance/security/README.md`
7. `governance/architecture/ARCHITECTURE.md`
8. `business-logic/README.md`
9. `management/README.md`
10. `management/TIMELINE.md`
11. `review/REVIEWS.md`
12. `templates/`

## Governance Index

- `governance/README.md`
- `governance/core/`
- `governance/execution/`
- `governance/standards/`
- `governance/standards/governance/`
- `governance/intelligence/`
- `governance/agents/`
- `governance/integrations/`
- `governance/delivery/`
- `governance/skills/`
- `governance/product/`
- `governance/architecture/`
- `governance/profiles/`
- `governance/profiles/repository-kinds/`
- `governance/security/`

## Rule

- If a rule is not reusable across unrelated repositories, it does not belong in `.agents/`.
- Write outcome first, then technical detail.
- If an item cannot answer the required questions, it is not ready.
- Prefer enforceable contracts and templates over long prose without gates.
- Child repos should declare their applied governance stack in root `AGENTS.md`
  so agents can resolve the correct SDLC lane and profile pack deterministically.
