# Recursive Governance Review Contract

Version: 3.0.0
Status: Normative

## Purpose

Define the mandatory review loop that must complete before any commit.
Passing tests alone is not commit permission. Passing static analysis alone is
not commit permission. Passing gates alone is not commit permission.

## Required Loop

```
implement/fix → validate → run gates → recursive governance review →
fix BLOCKER/HIGH/MEDIUM → rerun validation → rerun review → commit
```

### Step 1: Implement or Fix

Make the code or governance change.

### Step 2: Validate

Run the project's canonical validation entrypoint.
Record results in `.agents/management/evidence/validation/`.

### Step 3: Run Gates

Verify quality gates from `quality-gates.md` are satisfied for the touched
scope.

### Step 4: Recursive Governance Review

Review the change against the applicable governance stack:

1. Does the change obey the applicable profiles?
2. Does the change maintain the architecture standard?
3. Does the change preserve security posture?
4. Is the evidence recorded?
5. Is the management state updated?
6. Are docs still accurate?

Record review findings in `.agents/management/evidence/reviews/`.

### Step 5: Fix Findings

Fix all BLOCKER, HIGH, and MEDIUM findings.
Fixing a finding invalidates the previous validation — you must revalidate.

### Step 6: Rerun Validation

After fixes, rerun validation. Record new results.

### Step 7: Rerun Review

After fixes and revalidation, rerun the governance review.
Only a clean review (no BLOCKER/HIGH/MEDIUM) allows proceeding to commit.

### Step 8: Commit & Release Discipline

Only after the loop is clean:

- validation passes
- gates pass
- review shows no BLOCKER/HIGH/MEDIUM
- evidence is recorded
- management state is updated

#### Explicit Blocker Boundaries:

1. **Commit**: Blocked by unresolved `BLOCKER`, `HIGH`, or unaccepted `MEDIUM` findings. Blocked by missing validation evidence.
2. **Publish (Internal)**: Blocked by any `BLOCKER` or `HIGH`. `MEDIUM` (YELLOW debt) is allowed if explicitly accepted in `RISKS.md`.
3. **Release (Production)**: Blocked by `BLOCKER`, `HIGH`, and any unmitigated security/architectural `MEDIUM` debt.
4. **Deployment**: Blocked by missing release evidence, missing rollback evidence, or unapproved `HIGH` risk escalation.

## Review Replayability & Evidence Contracts

- **Replayability**: A review must cite specific commit SHAs, file hashes, or deterministic timestamps so it can be replayed and verified.
- **Evidence Contracts**: All review output MUST be serialized into `.agents/management/evidence/reviews/` in a format matching `review.schema.json`.

## FULL_GREEN Definition

FULL_GREEN requires:

- zero unresolved findings at any severity above LOW
- all validation passing
- all gates satisfied
- evidence recorded
- human dashboard updated (if significant)

## Accepted YELLOW Debt

YELLOW debt (accepted MEDIUM findings that are deferred) requires ALL of:

- `owner`: who owns the resolution
- `target`: what the resolution looks like
- `risk`: what happens if not resolved
- `expiry`: when it must be resolved by
- `evidence`: link to the finding
- `blocking_decision`: explicit decision to accept, recorded in DECISIONS.md

YELLOW debt without all six fields is not accepted — it is unresolved.

## Finding Severity & Escalation Lifecycle

- `BLOCKER`: Must fix before commit. Ownership: Author. Escalation: None allowed.
- `HIGH`: Must fix before commit. Ownership: Author. Escalation: Allowed to Lead Architect if disputed.
- `MEDIUM`: Must fix or explicitly accept as YELLOW debt before commit. Ownership: Team. Escalation: Must be logged in `RISKS.md`.
- `LOW`: May be deferred to backlog without ceremony. Ownership: Triage.

## Unresolved Finding Lifecycle

Any finding that is not fixed and not formally accepted into `RISKS.md` is **unresolved**. Unresolved findings automatically escalate to `HIGH` after 14 days, blocking future releases until addressed.

## Rule

Fixing validation invalidates previous review. The loop must restart from
Step 2 after any fix. This prevents "reviewed once, fixed silently, committed
without re-review."
