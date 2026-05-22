# Skill: Performance

## Purpose

This skill handles performance-sensitive code changes.

## Trigger

Use this skill when the user asks for:

- performance
- benchmark
- optimization
- speed
- latency
- memory

## Must Read

- `AGENTS.md`
- `.agents/GOVERNANCE_INDEX.md`
- `.agents/how-to/verification/how-to-system-performance.md`
- `CURRENT_TRUTH.md`

## Rules

- No Performance/Services/Managers/Optimizers folders.
- Hot paths must be identified.
- Hidden I/O is forbidden.
- Unbounded work is forbidden.
- Performance claims require evidence.

## Performance Gates

Before marking performance work complete:

```text
[ ] hot path identified
[ ] hidden I/O absent
[ ] bounded work
[ ] performance claims have evidence
[ ] performance check passes
```

## Output

Must produce:

```text
Hot paths:
Bounded work:
Evidence:
```