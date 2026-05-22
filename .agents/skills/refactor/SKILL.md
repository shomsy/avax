# Skill: Refactor

## Purpose

This skill handles renaming, moving, splitting, merging, and structural changes.

## Trigger

Use this skill when the user asks for:

- rename
- move
- split
- merge
- refactor
- reorganize
- restructure

## Must Read

- `AGENTS.md`
- `.agents/GOVERNANCE_INDEX.md`
- `.agents/how-to/components/how-to-design-components.md`
- `.agents/how-to/architecture/how-to-architecture.md`
- `.agents/how-to/implementation/how-to-clean-code.md`
- `CURRENT_TRUTH.md`

## Rules

- Folder still says flow or capability.
- Unit still says responsibility.
- Function still says exact action.
- No forbidden folders.
- No skeleton classes without behavior.
- No silent stage expansion.

## Scope

State explicitly:

```text
in scope:
out of scope:
files to rename/move:
files to create:
tests to update:
```

## Output

Must produce:

```text
Changes:
Validation commands:
Status:
```