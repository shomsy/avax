# How To Write Scenario Input

## Purpose

Do not implement behavior from vague task titles.
Scenario input defines actor, goal, boundary, guarantees, failure paths, tests, and out-of-scope decisions before production behavior changes.

## When Required

Required for changed production behavior under `components/`, `framework/`, `src/`, `app/`, or `packages/`.

May be skipped for pure documentation, governance tooling, formatting, generated evidence, or test-only edits that do not change behavior.

## Evidence Path

`.agents/management/evidence/generated/<task-name>/scenario-input.md`

## Template Sections

- Task
- Scope
- System Boundary
- Primary Actor
- Actor Goal
- Stakeholders and Interests
- Preconditions
- Success Guarantees
- Minimal Failure Guarantees
- Main Success Scenario
- Extension / Failure Paths
- Security Sensitivity
- Data / State Mutation Sensitivity
- Runtime / Concurrency Sensitivity
- Observability Requirement
- Acceptance Criteria
- Planned Tests
- Out of Scope

## Rules

- Main success scenario usually has 3-9 steps.
- Extension and failure paths must include invalid input and boundary failure where relevant.
- Security-sensitive scenarios must fail closed.
- Data-sensitive scenarios must state mutation, consistency, rollback, and retry semantics.
- Runtime-sensitive scenarios must state worker, concurrency, lifecycle, or cache risk.
- Domain story language should name actors, work objects, and handoffs.

## Stop Conditions

Stop if actor, goal, boundary, or failure guarantee is unknown for production behavior.

## Severity

Missing scenario input for production behavior is HIGH.
Missing fail-closed security path is BLOCKER.

## Final Report Requirement

Final reports must state whether scenario input was required, written, skipped, and why.

## Required vs Optional Decision Table

| Change Type | Scenario Input | Reason |
|---|---:|---|
| New production behavior | REQUIRED | Actor, goal, guarantees, and failures define the behavior. |
| Security-sensitive behavior change | REQUIRED | Fail-closed paths must be explicit before implementation. |
| Data mutation or retry behavior | REQUIRED | Correctness depends on state, idempotency, and recovery semantics. |
| Runtime, cache, queue, worker, or concurrency behavior | REQUIRED | Lifecycle and stale-state risks must be named first. |
| Pure governance documentation | OPTIONAL | Use architecture fitness evidence instead. |
| Mechanical rename with no behavior change | OPTIONAL | Record why behavior is unchanged. |
| Test-only change for existing behavior | OPTIONAL | Use test-proof evidence unless the test defines new behavior. |

## Mechanical Task Exceptions

Scenario input may be skipped for:

- typo-only documentation edits;
- moving files without behavior change;
- generated evidence or review pack creation;
- checker refactors that preserve semantics and have validation evidence.

The final report must still say `scenario_input: skipped` and explain the exception.

## Scenario Quality Rubric

| Quality | Signal |
|---|---|
| RED | Actor, goal, boundary, or failure guarantee is missing. |
| YELLOW | Main path exists but extension paths or tests are weak. |
| GREEN | Main path, failure paths, guarantees, risks, and tests are concrete. |

## Main Success Scenario Examples

Weak:

```text
1. User logs in.
2. System works.
```

Strong:

```text
1. Visitor submits identifier and secret to the Identity public surface.
2. Input is normalized and passed to the authentication flow.
3. Credentials are checked against the configured credential source.
4. A successful result creates a session token with explicit expiry.
5. The response returns only safe public session data.
```

## Extension Examples By Step Number

```text
2a. Input is malformed: reject with validation error and no state change.
3a. Credential source is unavailable: fail closed and emit safe operational evidence.
4a. Token creation fails: roll back session state and return a safe failure.
```

## Security-Sensitive Example

For authentication, authorization, token, session, secret, tenant, or admin behavior:

- list the protected asset;
- list untrusted inputs;
- define denial path;
- define invalid/expired/replayed path;
- require at least one negative test.

## Data Mutation Example

For writes:

- name the system of record;
- state whether derived data can be rebuilt;
- define retry and duplicate behavior;
- define rollback or reconciliation path.

## Runtime-Sensitive Example

For long-running runtimes:

- state request-scope data ownership;
- state reset lifecycle;
- state cache invalidation or immutability;
- state what must not leak across requests.

## Acceptance Criteria Examples

Good acceptance criteria are externally observable:

- invalid credentials never create a session;
- duplicate message processing is idempotent;
- runtime cache is reset between requests;
- unauthorized actor receives denial without revealing secret state.

## Anti-Examples

Do not write:

- "make auth better";
- "add manager";
- "system validates stuff";
- "happy path works";
- "tests pass".

These are not scenarios. They are vague wishes.

## Evidence Completeness Checklist

- task and scope are bounded;
- primary actor and goal are named;
- boundary owner is named;
- success and minimal failure guarantees are testable;
- extension paths include relevant invalid, denied, unavailable, duplicate, stale, or concurrent cases;
- security, data, runtime, and observability sensitivity are explicitly YES/NO;
- acceptance criteria map to planned tests.

## Final Report Schema

```text
scenario_input: required|skipped
evidence: <path or reason>
main_steps: <count>
failure_paths: <count>
security_sensitive: yes|no
data_mutation_sensitive: yes|no
runtime_sensitive: yes|no
remaining_risk: <classification>
```
