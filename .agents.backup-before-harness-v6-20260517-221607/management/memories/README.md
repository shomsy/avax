# Project Memory

Memory is durable project context.

Memory is not proof.

Memory may guide agent decisions, but validation decides truth.

## Allowed Memory

- stable architecture decisions
- repeated naming decisions
- known recovery lessons
- component ownership decisions
- recurring failure patterns
- accepted exceptions with expiry
- important project constraints

## Forbidden Memory

- temporary logs
- stale assumptions
- unverified claims
- personal notes unrelated to project execution
- old truth that contradicts validation

## Memory Entry Format

```md
## <Title>

Status:
Active / Deprecated / Superseded

Source:
Where this came from.

Decision:
What is remembered.

Reason:
Why it matters.

Applies To:
Where agents should use it.

Validation:
What proves it, if any.

Updated:
YYYY-MM-DD
```

## Available Memory Files

| File                   | Purpose                       |
|------------------------|-------------------------------|
| architecture-memory.md | Stable architecture decisions |
| component-memory.md    | Component ownership           |
| naming-memory.md       | Repeated naming decisions     |
| recovery-memory.md     | Recovery lessons              |

---

Agents may read these files before architecture, naming, recovery, or long-running work.