# Skill: User Feedback Synthesis (`/user-synthesis`)

## Purpose
Process raw interview transcripts, survey results, or customer feedback into structured **User Personas** and **Pain Point** libraries.

## Trigger
- User provides a new interview log or feedback batch.
- User wants to update a persona based on new evidence.

## Inputs
1. Raw feedback/transcript.
2. `.agent/context/users/` - Existing personas.

## Sequence
1. **Extract**: Identify Pain Points, Goals, and Direct Quotes from the input.
2. **Deduplicate**: Compare with existing Persona data to avoid overlap.
3. **Synthesis**: Create or Update `USER-PERSONA.md` for the relevant group.
4. **Insight Capture**: Record non-obvious patterns in `context/users/LEARNINGS.md`.

## Output
- Updated or new `USER-PERSONA.md` in `.agent/context/users/`.
- Summary of new insights extracted.
