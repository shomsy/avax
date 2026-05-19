# Architecture Profile: Package / Library

Version: 1.0.0
Status: Normative / Optional
Applies when: project `AGENTS.md` declares `package-library` architecture.

## What This Profile Adds

Rules for repositories that publish reusable packages or libraries,
not runnable applications.

## Package / Library Rules

- public API surface is minimal, explicit, and stable across minor versions
- internal implementation details are not exported
- the package must have no dependency on application-level frameworks unless optional
- peer dependencies are explicit and documented
- the package must be independently testable without a host application
- versioning follows semantic versioning; breaking changes require major bump
- changelog is mandatory for every release

## Anti-Patterns

- exporting internal implementation classes as part of the public API
- depending on a full framework when only one utility is needed
- mixing application business logic into a reusable library
