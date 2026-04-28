# Governance Evolution Policy

Version: 1.0.0
Status: Normative
Scope: `AGENTS.md`, `.agents/governance/**`, `README.md`, `scaffolds/**`

This document defines when governance should be extended, split, promoted,
demoted, or removed.

## Preferred Evolution Order

When a governance gap appears, try these moves in order:

1. clarify an existing rule
2. strengthen an existing rule with better validation or examples
3. split an oversized document into feature-first subdocuments
4. add a profile when the rule only applies to a subset of repositories
5. add a new top-level governance document only when the domain is durable

## Expansion Triggers

Add or split governance only when at least one is true:

- the same ambiguity has caused repeated drift or repeated review findings
- the same exception appears often enough that selective adoption is needed
- one document now governs multiple unrelated responsibilities
- installer, scaffold, and docs must repeatedly be updated together for the
  same kind of change
- a new integration surface or delivery surface is now a first-class concern

## Folder And File Growth Rules

- Prefer adding to the existing feature folder before creating a new top-level
  domain.
- Create a new subfolder when the subdomain is stable and will own multiple
  documents or a document family.
- If only one small rule exists, keep it in the nearest existing document.
- Name folders by capability or responsibility, not by chronology.

## Promotion And Demotion Rules

- Move a local rule into reusable governance only when it clearly generalizes.
- Move a reusable rule into a profile when only some repositories should apply it.
- Move explanatory or sample content into `README.md` or `scaffolds/**`.
- Remove dead rules instead of leaving ghost references.

## Structural Change Ceremony

When a governance path, precedence chain, or feature folder changes, update in
the same change:

1. root `AGENTS.md`
2. `.agents/AGENTS.md`
3. `scaffolds/AGENTS.md`
4. relevant indexes and onboarding docs
5. installer and generator paths
6. validation commands
7. merged snapshot artifacts

## Breaking-Change Rule

For this repository class, a governance path change is a breaking operational
change unless the dependent surfaces are updated together.

Treat these as release-blocking bugs:

- stale precedence paths
- stale scaffold paths
- stale installer substitutions
- stale adapter instructions
- generated snapshot drift
