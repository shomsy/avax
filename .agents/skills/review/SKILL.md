# Skill: Review

## Purpose

This skill performs systematic code review against all governance rules.

## Trigger

Use this skill when the user asks for:

- review
- audit
- evaluate
- analyze
- critique

## Must Read

- `AGENTS.md`
- `.agents/GOVERNANCE_INDEX.md`
- `.agents/how-to/how-to-code-review.md`
- All `.agents/how-to/how-to-*.md` documents
- `CURRENT_TRUTH.md`

## Required Inventory

For every `how-to-*.md` document that applies to the codebase, inventory:

```text
document:
rules that apply:
rules not applicable:
compliance status:
violations:
severity:
required action:
```

## Checklist

| Document                       | Rule                        | Status    | Evidence |
|--------------------------------|-----------------------------|-----------|----------|
| how-to-architecture.md         | folder says flow/capability | Pass/Fail | pointer  |
| how-to-design-components.md    | canonical shape             | Pass/Fail | pointer  |
| how-to-clean-code.md           | clean code principles       | Pass/Fail | pointer  |
| how-to-coding-standards.md     | PHP standards               | Pass/Fail | pointer  |
| how-to-code-style.md           | code formatting             | Pass/Fail | pointer  |
| how-to-unit-test.md            | test patterns               | Pass/Fail | pointer  |
| how-to-document.md             | docs location               | Pass/Fail | pointer  |
| how-to-system-security.md      | security rules              | Pass/Fail | pointer  |
| how-to-system-performance.md   | performance rules           | Pass/Fail | pointer  |
| how-to-production-readiness.md | prod gates                  | Pass/Fail | pointer  |

## Output

Must produce:

```text
Stage:
Status:              (GREEN/YELLOW/RED/BLOCKER)
Governance inventory:
Governance findings:
Violations by severity:
Required actions:
Next allowed action:
```

## Hard Rule

A code review that does not explicitly check all applicable `how-to-*.md` documents is incomplete.