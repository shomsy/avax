# Skill: Meeting Cleanup (`/meeting-cleanup`)

## Purpose

Convert raw meeting transcripts, scribbles, or snapshots into structured decisions, task items, and context updates.

## Trigger

- User provides a meeting snapshot or raw notes.
- User wants to "close" a meeting with actions.

## Inputs

1. Raw meeting input.
2. `MEETING-NOTES.md` scaffold.

## Sequence

1. **Identify Triggers**: Why was the meeting held?
2. **Extract Decisions**: What was finalized and what is the rationale?
3. **Draft Actions**: Create clear, owned tasks for `.agents/management/TODO.md`.
4. **Update Strategy/Context**: Identify if stakeholders, personas, or strategy need a refresh.
5. **Finalize**: Generate the `MEETING-NOTES.md` for this session.

## Output

- A new `MEETING-NOTES.md` record.
- Added tasks in `TODO.md`.
- Summary of strategic context updates.
