# Architecture (Template For Child Repos)

This document is a template that binds the reusable standard from
`.agents/governance/architecture/architecture-standard.md` to a concrete repository.

In `agent-harness` it stays generic so that child projects can copy and specialize it.

## Scope and Authority

- `AGENTS.md` in the child repo governs execution, delivery discipline, and collaboration.
- `.agents/governance/architecture/architecture-standard.md` defines the reusable baseline.
- The child repo's copy of this file binds that baseline to its actual folders and flows.

## How To Use In a Child Repo

When copying `.agents` into a project:

1. Replace this template with a repo-specific architecture document.
2. Make the root `AGENTS.md` declare the applied governance stack.
3. Choose the relevant architecture overlays from
   `.agents/governance/architecture/profiles/**`.
4. Describe real top-level boundaries (for example `product/`, `system/`, `foundation/`).
5. Show a representative tree that matches that repo.
6. Explain how vertical slices work in that codebase.
7. Keep naming rules and anti-patterns consistent with the standard.

## Minimal Required Sections For Child Repos

Each child repo should define at least:

- scope and authority
- migration posture (where new work is allowed)
- vertical slice intent
- agreement with the applied governance stack from root `AGENTS.md`
- applied language or framework architecture profiles
- repo shape and representative tree
- naming rules for folders, files, and functions
- change policy for architecture documents

## Change Policy (For This Template)

- Do not specialize this file to any one project inside `agent-harness`.
- Changes here should improve clarity for all child repos.
