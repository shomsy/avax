# How To Refactor Safely

## Definition

Refactoring changes structure while preserving externally observable behavior.

## What Refactoring Is Not

It is not feature work, behavior changes, architecture rewrites without safety proof, or test changes that fit broken behavior.

## Evidence Path

`.agents/management/evidence/generated/<task-name>/refactoring-safety.md`

## Rules

- Characterization rule: prove current behavior before changing risky code.
- Small step rule: prefer narrow, reviewable changes.
- Smell rule: name the smell being reduced.
- Behavior safety rule: tests must prove preserved behavior.
- Architecture refactor rule: boundary movement requires coupling evidence.
- Feature/refactor separation: do not mix new behavior and refactor unless explicitly justified.

## Stop Conditions

Stop if behavior cannot be characterized, tests are missing for risk, or the change silently alters public behavior.

## Severity

Unproven behavior change during refactor is HIGH.
Security behavior changed without negative tests is BLOCKER.

## Final Report Requirement

Final reports must state whether behavior was preserved, how it was proven, and where evidence lives.

## Refactoring vs Rewrite vs Migration

| Work Type | Meaning | Required Proof |
|---|---|---|
| Refactoring | Structure changes, behavior preserved. | Characterization and regression tests. |
| Rewrite | Behavior may be reimplemented. | Scenario, compatibility, migration, and risk evidence. |
| Migration | Data, API, or runtime path moves. | Rollback, compatibility, and reconciliation plan. |

## Characterization Test Examples

- public API returns same result for representative inputs;
- invalid input still fails with equivalent safety;
- existing side effects remain ordered;
- security denial path still denies.

## Smell To Refactoring Table

| Smell | Safer Refactoring |
|---|---|
| Long method | extract exact action after tests exist |
| Duplicate validation | extract named validation capability |
| Hidden dependency lookup | inject dependency at assembly boundary |
| Generic bucket | move to flow/capability owner |
| Conditional strategy | extract strategy only when variation is real |

## Safe Sequence Example

```text
1. Add characterization test.
2. Rename for clarity.
3. Extract one behavior-preserving method.
4. Run focused validation.
5. Repeat only if the diff remains reviewable.
```

## Rollback Requirement

Every refactor evidence file must state how to revert safely and which public contracts must remain stable.

## Public API Safety

PublicSurface signatures, response shapes, exceptions, and documented behavior must remain compatible unless the task is explicitly an approved breaking change.

## Data / Security / Runtime Safety

Data refactors require state invariants and rollback. Security refactors require negative tests. Runtime refactors require worker/request lifecycle proof.

## Refactor Plus Feature Mixing Rules

Do not mix feature and refactor unless:

- the feature cannot be safely implemented without the refactor;
- behavior changes are isolated in separate commits or evidence sections;
- tests distinguish preserved behavior from new behavior.

## Evidence Template

Every refactoring safety evidence file must contain these exact headings:

- # Refactoring Safety
- ## Task
- ## Scope
- ## Refactoring Type
- ## Classes / Methods Affected
- ## Behavioral Equivalence Proof
- ## Tests Before
- ## Tests After
- ## Public API Impact
- ## Coupling Impact
- ## Runtime Impact
- ## Rollback Plan
- ## Review Date
