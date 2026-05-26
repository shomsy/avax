# Source of Truth Decision

Generated: 2026-05-26

## Task

Governance Gate Adoption and Baseline Pass.

## Current Worktree

- Branch: `architecture/identity-runtime-convergence`
- Scope: governance tooling, baselines, how-to policy, and evidence.
- Forbidden scope: Identity implementation and production feature work.

## Sources Used

- `AGENTS.md`: root execution contract and evidence rules.
- `.agents/GOVERNANCE_INDEX.md`: governance routing.
- `.agents/how-to/verification/how-to-code-review.md`: review and gate policy.
- `.agents/how-to/verification/how-to-test-risk-based-behavioral-testing.md`: shallow-test policy.
- `.agents/how-to/documentation/how-to-write-self-explaining-architecture.md`: self-explaining architecture policy.
- `.agents/how-to/project/how-to-write-avax.md`: AvaX project overlay.
- Current validation output captured under this directory.

## Decision

The failing PHPStan, self-explaining architecture, and shallow-test gates are real findings, but they are legacy adoption debt rather than proof that every future slice must manually fix all historical findings before work can continue.

The correct source-of-truth state is:

- Full mode remains strict.
- Baseline mode allows only recorded pre-existing debt.
- Changed mode blocks new or touched-scope violations.
- FULL_GREEN is still forbidden while full mode is not clean.
- Identity rewrite may proceed only as bounded slices under changed-scope enforcement.

## Contradictions Resolved

- Previous review packs were generated as `governance-final-hardening`, not proof of this adoption pass.
- Prior full validation failures remain true and are not reclassified as GREEN.
- Baselines are not suppressions because they preserve findings with owner, reason, review date, category, and changed-scope hard enforcement.

## Risk of Proceeding

Proceeding to Identity slices is YELLOW, not FULL_GREEN. The risk is contained only if changed-scope gates are mandatory for every Identity slice.
