# Autonomous Backlog Loop Skill — Evidence Summary

Date: 2026-05-20
Executor: Qoder
Branch: main
Type: governance/skill authoring

## Files Created

1. `.agents/skills/avax-autonomous-backlog-loop/SKILL.md`
   - Autonomous task-by-task backlog execution skill
   - Defines activation triggers, boot order, context harvest, loop rules, stop conditions, branch discipline, per-slice flow, handoff

## Files Updated

1. `.agents/skills/index.md`
   - Added routing entry for `avax-autonomous-backlog-loop` in Task to Skill Mapping table
   - Added entry in Skill Files table

2. `.agents/skills/avax-enterprise-remediation/SKILL.md`
   - Added Bootloader Rule section clarifying this is a bootloader, not the whole OS
   - Added routing directive to `avax-autonomous-backlog-loop` for autonomous execution
   - Added mandate to discover and apply all task-relevant `.agents` resources

3. `AGENTS.md`
   - Added section 24.2: Full `.agents` Potential Rule
   - Clarifies Enterprise Remediation is bootloader only
   - Mandates discovery of task-relevant skills, how-to, learning, memory, evidence
   - Mandates use of `avax-autonomous-backlog-loop` for autonomous work
   - Clarifies PARTIAL is not a stop condition

## New Rules

- Autonomous loop must boot via `avax-enterprise-remediation` first
- Full `.agents` context harvest is mandatory before any backlog execution
- Source precedence: git state > AGENTS.md > skills > TODO.md > fix-this.md > evidence > CURRENT_TRUTH.md > how-to > learning/memory > archive
- PARTIAL is not STOP; continue until TODO_CLOSED or HARD_BLOCKER
- 10 hard stop conditions defined
- One slice = one branch/worktree/evidence/review/merge candidate
- QODER_HANDOFF.md required before stopping

## Routing Behavior

- Triggers: "radi sto vise", "nastavi sam", "bez dodatnih promptova", "zatvori listu", "maximum sweep", "autonomous backlog loop", "task po task", "dok ima tokena"
- Routes from `avax-enterprise-remediation` to `avax-autonomous-backlog-loop` for autonomous execution
- Index updated with new skill mapping

## Validation

```
composer validate --no-check-publish: GREEN (./composer.json is valid)
php tooling/governance/check-governance-index-current.php: GREEN (Governance index is current)
php tooling/governance/check-root-evidence-hygiene.php: GREEN (Root Evidence Hygiene PASSED)
```

All validation GREEN.

---

## Phase 2: AvaX Enterprise Codecraft Skill

### Files Created

1. `.agents/skills/avax-enterprise-codecraft/SKILL.md`
   - Full software design quality gate (HLD + LLD + Codecraft/OOP)
   - Covers system design, component design, SOLID, cohesion, coupling, NFR evaluation, trade-off analysis
   - Defines three design gates: HLD, LLD, SOLID/Cohesion/Coupling
   - Requires design-before-code.md, high-level-design.md, low-level-design.md for production changes
   - Defines design classification: HLD_SOUND through NEEDS_REDESIGN
   - Blocks commit on DEGRADED_BLOCKER NFR or TOO_MECHANICAL/FAKE_OOP/ARCHITECTURE_THEATER/NEEDS_REDESIGN

### Files Updated

1. `.agents/skills/index.md`
   - Added routing entry for production-code changes, architecture, refactor, OOP, SOLID, cohesion, coupling, HLD, LLD, system design, 11++, enterprise quality
   - Added entry in Skill Files table

### New Design Gates

- HLD Gate: system capability, bounded area, upstream/downstream, lifecycle phase, runtime modes, NFR impact, failure modes, trade-offs
- LLD Gate: classes/files, responsibilities, call flow, dependency injection/assembly, invariants, error classification, test proof
- SOLID/Cohesion/Coupling Gate: all seven principles plus coupling direction rules
- System Design Principles Gate: 22 NFR dimensions classified as IMPROVED/UNCHANGED/ACCEPTED_YELLOW/DEGRADED_BLOCKER/NOT_APPLICABLE
- Design Classification: HLD_SOUND, LLD_SOUND, REAL_OBJECT_MODEL, ACCEPTABLE_SIMPLE_PROCEDURAL_BOUNDARY, TOO_MECHANICAL, FAKE_OOP, ARCHITECTURE_THEATER, NEEDS_REDESIGN

### Required Evidence Artifacts

- design-before-code.md (always)
- high-level-design.md (architecture-heavy tasks)
- low-level-design.md (non-trivial implementation)
- ownership-boundary.md, implementation-summary.md, test-proof.md, validation-output.md, governance-review.md, final-decision.md
- threat-analysis.md, negative-test-proof.md (security-sensitive)
- runtime-safety-proof.md, dependency-boundary.md (runtime/worker/DI/PublicSurface)

### Routing Behavior

- Triggers: production-code change, architecture change, refactor, OOP, SOLID, cohesion, coupling, HLD, LLD, system design, 11++, enterprise quality
- Must load together with: avax-enterprise-remediation, avax-autonomous-backlog-loop (autonomous sweeps), review, testing, validation, security (when relevant), performance (when relevant)
