# Status State Machine Rule

## Decision

The Status State Machine rule is added to:

- `how-to-production-readiness.md` — as Section 20

## Rule Text

Defines three exact statuses: GREEN, YELLOW, RED. Each has precise criteria that must be met before the status may be
claimed. Forbidden patterns are listed explicitly (e.g., "pre-existing" is not a status).

## Cross-References

This rule replaces vague status definitions previously scattered across multiple documents. All documents should
reference this section as the canonical source.
