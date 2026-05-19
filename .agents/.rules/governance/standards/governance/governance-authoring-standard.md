# Governance Authoring Standard

Version: 1.0.0
Status: Normative
Scope: `AGENTS.md`, `.agents/governance/**`, `README.md`, `scaffolds/**`

This document defines how to write, place, and maintain governance artifacts
without turning the rule system into duplicated prose.

## Core Rule

Add the smallest durable artifact that solves a real repeated problem.

If an existing document can absorb the rule cleanly, update that document
instead of creating a new one.

## Placement Matrix

| Need | Put It In |
|:---|:---|
| global safety floor or precedence rule | `AGENTS.md` or `.agents/AGENTS.md` |
| universal reusable rule | `.agents/governance/core/**`, `standards/**`, `security/**`, `delivery/**` |
| stack- or repo-kind-specific overlay | `.agents/governance/profiles/**` |
| repo shape or slice law | `.agents/governance/architecture/**` |
| runtime adapter or external integration behavior | `.agents/governance/integrations/**` |
| project-local exception | root `AGENTS.md` |
| example, starter, or generated helper | `scaffolds/**` |
| active execution state | `.agents/management/**` |

## Authoring Rules

- Start with purpose and scope.
- Use `MUST`, `SHOULD`, and `MAY` only when the enforcement intent is real.
- Prefer short rules with observable behavior over broad philosophy.
- Reference real paths, artifacts, and commands.
- State what changes when the rule applies.
- State what done means when the rule governs completion.
- Keep examples out of normative sections unless the example is the clearest way
  to remove ambiguity.

## Anti-Duplication Rules

- Do not restate the same law in multiple files with different wording.
- Do not create a profile to compensate for a weak core rule.
- Do not put project-specific trivia into reusable governance.
- Do not put reusable law into `README.md` if it belongs in governance.
- Do not hide a breaking path change inside unrelated wording edits.

## Documentation Rules For Governance Artifacts

- Broken links and stale paths are governance bugs.
- If a path changes, update every precedence chain, index, scaffold, and
  installer that depends on it in the same change.
- If a document claims a runtime helper exists, the helper must exist.
- If a document describes generated artifacts, the authored source and
  regeneration path must be obvious.

## Rule Review Checklist

Before adding or changing governance, verify:

1. Is this reusable across unrelated repositories or clearly local?
2. Could an existing document absorb it without growing incoherent?
3. Is the placement consistent with the feature-first tree?
4. What validation proves the rule is being obeyed?
5. Which other surfaces must change with it: installer, scaffold, adapters,
   README, onboarding, or merged snapshot?
