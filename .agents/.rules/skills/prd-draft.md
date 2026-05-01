# Skill: PRD Draft (`/prd-draft`)

## Purpose
Translate a rough idea, discussion notes, or feature request into a formal **Product Requirements Document (PRD)** that aligns with the project strategy and personas.

## Trigger
- User asks for a PRD draft.
- User provides raw meeting notes or a "brainstorm" document and asks for structure.

## Inputs
1. `.agent/context/strategy/` - High-level vision.
2. `.agent/context/users/` - Relevant personas.
3. Raw input notes or context.

## Sequence
1. **Analyze**: Identify the core problem and target persona from the input.
2. **Consult Strategy**: Ensure the proposed feature aligns with the roadmap.
3. **Draft**: Create a new file in `.agent/context/product/` (or current directory) using the `PRD.md` template.
4. **Self-Review**: Adopt the `engineering-reviewer` persona to flag major feasibility gaps.
5. **Finalize**: Present the draft and list the remaining open questions.

## Output
- A new or updated `PRD.md` artifact.
