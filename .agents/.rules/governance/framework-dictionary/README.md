# Framework Dictionary

## Purpose

The Framework Dictionary defines **legitimate ecosystem terms** that may appear in directory or class names
without triggering false-positive governance violations.

It is NOT a giant whitelist. It is NOT an excuse system. It is NOT a regex bypass.

## How It Works

When a naming scanner encounters a term that overlaps with the forbidden list:

1. **Check the dictionary** — does this term have an entry?
2. **Verify context** — is the term used in an allowed context (path)?
3. **Verify usage** — is the pattern legitimate (not overextended)?

### Outcomes

| Result | Meaning |
|--------|---------|
| GREEN | Legitimate ecosystem usage in correct context |
| YELLOW | Suspicious usage, ambiguous context, or overextended role |
| RED | Generic abuse, fake abstraction, or meaningless wrapper |

## Entry Structure

Each term has:
- **Term** — the exact name
- **Classification** — what kind of thing it is
- **Purpose** — what it does
- **Why Allowed** — why it is legitimate
- **Allowed Contexts** — where it may appear
- **Forbidden Misuse** — how it must NOT be used
- **Ecosystem References** — authoritative sources
- **Allowed Patterns** — concrete examples of good usage
- **Forbidden Patterns** — concrete examples of bad usage

## Directory Structure

```
framework-dictionary/
  README.md          ← this file
  index.json         ← machine-readable index
  php/               ← PHP/framework ecosystem terms
  javascript/        ← JavaScript/Node ecosystem terms
  infrastructure/    ← Infrastructure/runtime terms
```

## Adding Terms

A term qualifies for the dictionary when:
1. It is a widely established ecosystem convention (not invented locally)
2. It represents a concrete lifecycle role (not a generic bucket)
3. It has authoritative references (docs, standards, specifications)
4. Its misuse patterns are clearly definable

To add a term:
1. Create `<ecosystem>/<term-slug>.md` with all required sections
2. Add entry to `index.json`
3. Run `validate-dictionary.py` to verify integrity
4. Commit with governance review

## What This System Still Rejects

The dictionary does not weaken governance. The system still rejects:

- `UserManager`, `GlobalHelper`, `DataUtil`, `SystemService`
- `AbstractProcessorFactoryManager`
- Any forbidden term used outside its allowed contexts
- Any term not in the dictionary that matches the forbidden list
- Hollow facades, fake abstractions, and meaningless wrappers
