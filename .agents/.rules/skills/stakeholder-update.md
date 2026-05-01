# Skill: Stakeholder Update (`/stakeholder-update`)

## Purpose
Generate tailored progress updates for different stakeholder groups (Executive, Engineering, Customer) based on current implementation and strategic context.

## Trigger
- User asks for a "weekly update" or "status report".
- A major milestone/PRD completion.

## Inputs
1. `.agent/context/stakeholders/STAKEHOLDER-MAP.md`
2. `.agents/management/TIMELINE.md` and `TODO.md`
3. Current project state (last 7 days).

## Sequence
1. **Analyze Audience**: Resolve the influence and interest profile from the Stakeholder Map.
2. **Filter Progress**: Select the technical vs strategic facts for the target group.
3. **Format**: Create the update (Email, Slack, or MD Report) following the **Style Guide**.
4. **Draft**: Create the report in `.agents/management/evidence/STAKEHOLDERS/`.

## Output
- A tailored Stakeholder update artifact.
- Summary of next-milestone confidence.
