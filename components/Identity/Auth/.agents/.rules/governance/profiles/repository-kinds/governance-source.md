# Governance Source Repository Profile

Use this profile for repositories whose primary product is governance,
documentation, scaffolds, installers, adapters, or other reusable control-plane
artifacts.

Signals include:

- large `.agents/governance/` and `scaffolds/` surfaces
- installer or bootstrap scripts that must stay in sync with docs
- merged or generated snapshots used as distribution artifacts
- correctness depending on path hygiene, precedence integrity, and portability

## Primary Surface Rules

- Treat rule documents, scaffolds, adapters, and installers as first-class
  product surfaces.
- Documentation drift is a behavioral bug, not a cosmetic issue.
- Structural changes must preserve portability for child repositories.
- The global contract must stay generic even when this repository dogfoods it.

## Change-Propagation Rules

When changing paths, precedence, or governance structure, update together:

- root and global `AGENTS.md`
- scaffold contracts
- installer substitutions and generated front-door adapters
- onboarding and README indexes
- merged snapshot outputs when they are versioned

## Expansion Rules

- Prefer feature-first folders over flat growth once a domain has multiple docs.
- Add new governance documents only when the responsibility is durable.
- Use repository-kind profiles for repo-class-specific behavior instead of
  polluting the global contract.
- Keep project-only shortcuts in the root `AGENTS.md`, not in shared `.agents`.

## Validation Rules

Changes in this profile class should normally validate:

- script syntax for touched shell helpers
- path and whitespace hygiene
- installer behavior in a temporary directory when path contracts changed
- generated snapshot refresh when versioned outputs are part of the repo
