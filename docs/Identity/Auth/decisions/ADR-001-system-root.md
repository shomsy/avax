# ADR-001: `System/` Is The Canonical System Root

Status: accepted

`System/` is the only canonical production root for this package.

Reasons:

- it cleanly separates runtime code from docs, tests, examples, and tooling
- it already maps to the PSR-4 package namespace
- it keeps the architecture visible without adding an unnecessary `src/` hallway

Consequence:

- all production ownership rules start inside `System/`
- no second production root may be introduced
