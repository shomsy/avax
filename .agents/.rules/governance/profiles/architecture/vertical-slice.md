# Architecture Profile: Vertical Slice

Version: 1.0.0
Status: Normative / Optional
Applies when: project `AGENTS.md` declares `vertical-slice` architecture.

## What This Profile Adds

This overlay adds vertical-slice-specific rules on top of the universal
architecture baseline. It does NOT apply to all projects.

## Vertical Slice Rules

- organize code by feature or domain capability, not by technical layer
- each slice owns its own models, handlers, persistence, and tests
- cross-slice communication is explicit — no implicit shared state
- shared infrastructure lives in a clearly named foundation or kernel layer
- a new feature should be addable by touching one slice, not N layers
- slices may be independently deployed if the runtime supports it

## Folder Convention (Example — Replace in Project)

```
src/
  feature-auth/
  feature-billing/
  feature-notifications/
  kernel/           ← shared infrastructure only
```

## Anti-Patterns

- global services that grow to own every feature's logic
- shared "utils" that accumulate all cross-slice complexity
- feature code that imports directly from another feature's internals
