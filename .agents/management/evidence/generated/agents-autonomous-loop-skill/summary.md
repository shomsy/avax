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
