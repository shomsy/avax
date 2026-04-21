# Architecture Profiles

These overlays narrow the universal architecture standard for specific
languages, runtimes, and frameworks.

Use them when the repo needs concrete guidance for:

- top-level shape
- feature-module boundaries
- ingress owners and public surfaces
- allowed interior layers inside a slice
- package, namespace, or module organization

## Universal Law

Every profile in this folder still inherits the same architecture law:

- folder says flow or capability
- file says responsibility
- function says exact action
- root of the slice says where the full owned flow begins

Profiles narrow the shape.
They do not weaken the simplicity target.

## Structure

- `languages/` for architecture overlays tied to one language family
- `frameworks/` for architecture overlays tied to framework or runtime
  expectations

## Stacking Model

1. `../../architecture-standard.md`
2. relevant language architecture profile
3. relevant framework architecture profile
4. child-repo `../../ARCHITECTURE.md`
5. repo-local exceptions in root `AGENTS.md`

## Important Boundary

These files are about architecture shape and ownership.
They do not replace runtime, typing, security, or coding-law profiles in
`../../../profiles/**`.
