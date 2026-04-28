# Governance Profiles

This folder holds selective governance overlays that apply only to certain
repository kinds or stack slices.

Core governance remains in:

- `../core/quality/quality-gates.md`
- `../core/resolution/profile-resolution-algorithm.md`
- `../execution/policy/execution-policy.md`
- `../standards/coding/how-to-coding-standards.md`
- `../standards/review/how-to-code-review.md`
- `../standards/documentation/how-to-document-flow.md`
- `../standards/documentation/how-to-document.md`
- `../delivery/release/release-and-rollback-policy.md`

Profiles add specific constraints and defaults for concrete stacks.
For repo shape and architecture overlays, also see
`../architecture/profiles/README.md`.

## Structure

- `languages/` for language-level rules
- `frameworks/` for framework/runtime-level rules
- `roles/` for reviewer or agent persona overlays
- `repository-kinds/` for repositories whose primary maintained surface is not
  a normal application runtime

Language profiles should carry syntax, typing, runtime-safety, interop, and
tooling rules for that language. One project may intentionally combine
multiple language profiles, for example `typescript` + `nodejs` + `css`.

Repository-kind profiles should stay narrower than global governance and should
only exist when a reusable subset of repositories needs extra behavior. They
must not become a dumping ground for local one-off exceptions.

## Priority

1. Core governance files
2. Explicit stack declaration in root `AGENTS.md`
3. Repository-kind profile
4. Language profile
5. Framework profile
6. Project-local exceptions documented explicitly

If profile guidance conflicts with core safety and quality rules, core rules win.

## Resolution Rule

- the root `AGENTS.md` should declare which profiles are applied
- if the root file is incomplete, resolve profiles through
  `../core/resolution/profile-resolution-algorithm.md`
- do not apply a framework profile unless the repo or root contract gives
  strong evidence that the framework is real
