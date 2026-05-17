# Skill: Strategy Review (`/strategy-review`)

## Purpose
Evaluate the current technical and project state against the strategic **Context Library** (Vision, Milestones, Personas).

## Trigger
- User asks for a "Progress Review" or "Strategy Alignment".
- A major milestone/PRD is completed.

## Inputs
1. `.agent/context/strategy/` - High-level goals.
2. `.agents/management/TIMELINE.md` - Recent progress.
3. Relevant PRDs and technical plans.

## Sequence
1. **Compare**: Measure progress against current milestones.
2. **Consult Personas**: Determine if recent changes still align with target needs.
3. **Scenario Analysis**: Identify risks to upcoming roadmap items.
4. **Adoption Review**: Check for strategy-drift in recent implementation choices.

## Output
- A **Strategy Alignment Report** in `context/strategy/REVIEWS/`.
- Summary of risks or needed Roadmap adjustments.
