# How To Strict Review

Version: 1.1.0
Status: Normative

This document defines the cold independent review path.
When used inside a remediation loop, strict review findings must be fixed,
revalidated, and rechecked before the review can be considered closed.

Strict review exists to answer one question:

- if a strong engineer saw this repository for the first time and had no
  loyalty to its current shape, would they keep it, redesign it, or start over

Strict review is intentionally outside the normal local-governance compliance
lens. Existing contracts, naming, and process density are context, not proof.

## Purpose

Use strict review to test reality against first principles:

- shipped behavior over repository self-description
- cold operator experience over internal familiarity
- maintainability over sunk-cost defense
- rewrite honesty over incremental optimism

## Reviewer Posture

Strict review must be:

- cold
- impartial
- first-contact
- rewrite-capable
- evidence-first

Reviewer prohibitions:

- do not defer to `AGENTS.md` as proof of quality
- do not accept old intent as defense against current bad shape
- do not soften findings to preserve narrative comfort
- do not confuse green local gates with actual product truth

## When To Run

Strict review is required when any of these are true:

1. a `Production Ready` claim is being prepared or defended
2. a release claims major convergence or architectural closure
3. a large migration, restructuring wave, or boundary rewrite has just landed
4. the system feels internally disciplined but externally unconvincing
5. a user explicitly asks for an independent first-principles review

## Inputs And Non-Inputs

Use as primary inputs:

- shipped command paths
- install and first-run behavior
- runtime behavior
- gates and test evidence
- release and rollback behavior
- code structure and ownership seams
- active docs that claim current truth

Use as secondary inputs:

- `AGENTS.md`
- `README.md`
- `.agents/management/TODO.md`
- `.agents/management/BUGS.md`
- older evidence reports

Treat as non-inputs:

- project pride
- legacy intent
- labels such as `enterprise-grade`, `stable`, or `production ready`
- promises not backed by a shipped path

## Strict Review Flow

Run five passes:

### Pass 1: Cold Start And Product Truth

Check:

- install path
- first-run help and version trust
- mismatch between promised workflow and shipped behavior
- whether a new operator would believe the product after the first ten minutes

### Pass 2: Architecture And Ownership Honesty

Check:

- whether ownership boundaries match the real implementation path
- whether wrappers are real seams or decorative shells
- whether architecture claims reduce or increase cognitive load

### Pass 3: Trust, Safety, And Fail-Closed Behavior

Check:

- secret handling
- trust downgrade paths
- placeholder-success behavior
- unsafe defaults
- policy bypasses

### Pass 4: Release And Operator Reality

Check:

- evidence quality
- rollback clarity
- diagnostics
- machine-readable outputs
- reproducibility of release claims

### Pass 5: From-Scratch Rewrite Test

Check:

- what should be kept
- what should be collapsed
- what should be deleted
- what is too expensive to defend long-term

## Output Contract

Every strict review must produce:

1. overall quality score from `0.0` to `10.0`
2. final decision:
   - `Keep and Improve`
   - `Redesign`
   - `Rewrite Candidate`
3. findings ordered by severity
4. evidence for every `high` or `critical` claim
5. from-scratch rewrite priorities
6. keep / collapse / delete recommendations
7. clear distinction between:
   - governance strength
   - actual shipped quality

## Severity Standard

- `critical`: trust, safety, release, or outage-class flaw that would block
  serious adoption
- `high`: productization, correctness, or architectural honesty flaw that makes
  the system materially weaker than its narrative
- `medium`: meaningful debt or inconsistency, but not a release blocker alone
- `low`: useful improvement or cleanup
