# Skill: Launch Checklist (`/launch-checklist`)

## Purpose
Synthesize a comprehensive Launch and Go-To-Market checklist based on current project maturity, strategic context, and quality gates.

## Trigger
- User asks for a "Launch Prep" or "Final Check".
- A major feature/PRD reaches `Approved` status.

## Inputs
1. `.agents/governance/core/quality/quality-gates.md`
2. `.agent/context/strategy/ROADMAP.md`
3. Current technical completion and testing status.

## Sequence
1. **Verify Quality**: Map current technical evidence to Quality Gates.
2. **Scan Strategy**: Check for remaining Strategic/Business prerequisites.
3. **Analyze Persona**: Determine if launch-day support needs are met (Help, FAQ).
4. **Finalize**: Create the checklist in `.agents/management/evidence/LAUNCH/`.

## Output
- A prioritized **Launch Readiness Checklist**.
- Summary of High-Risk blockers.
