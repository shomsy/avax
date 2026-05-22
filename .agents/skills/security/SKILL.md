# Skill: Security

## Purpose

This skill handles security-sensitive code changes.

## Trigger

Use this skill when the user asks for:

- security
- authentication
- authorization
- encryption
- password
- token
- secret
- encryption
- hashing

## Must Read

- `AGENTS.md`
- `.agents/GOVERNANCE_INDEX.md`
- `.agents/how-to/verification/how-to-system-security.md`
- `CURRENT_TRUTH.md`

## Rules

- No Security/Services/Managers/Helpers folders.
- Use concrete names: HashPassword, VerifyPasswordHash, AuthorizeUserAccess.
- Secrets never logged or returned raw.
- Authorization must protect the object, not just the route.
- Input validation required.
- Output encoding required.

## Security Gates

Before marking security work complete:

```text
[ ] boundary identified
[ ] validation present
[ ] authorization present where needed
[ ] secrets redacted
[ ] state does not leak
[ ] security check passes
```

## Output

Must produce:

```text
Security boundary:
Tests required:
Validation:
```