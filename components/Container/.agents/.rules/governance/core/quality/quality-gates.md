# Universal Quality Gates

Version: 1.3.0
Status: Normative / Agnostic

Before any work is marked as **"Production Ready"** or proposed for a final
merge, the agent and developer must be able to defend these quality questions.

## The Quality Filter

1. **Trust**: Does the change fail closed when mandatory config, dependency,
   policy input, or evidence is missing?
2. **Operator Clarity**: Can another human or agent understand the failure,
   recovery path, and artifact location without tribal knowledge?
3. **Rollback Posture**: Is there an explicit path to reverse or contain the
   change when it mutates state, runtime policy, or public behavior?
4. **Contract Stability**: Are public interfaces stable, or is the migration
   path documented and validated?
5. **State Ownership**: Is durable truth stored in an owned boundary instead of
   accidental local memory, cache drift, or UI residue?
6. **Async Containment**: If async or background work exists, are acknowledgement,
   retry, timeout, and quarantine rules explicit and observable?
7. **Deterministic Automation**: Can CI, deployment, or operational tooling
   consume the result without manual interpretation or fuzzy parsing?
8. **Observability Logic**: Do logs, traces, metrics, events, and exit codes
   make the behavior diagnosable through the real execution path?
9. **Runtime Hardening**: Does the change preserve least privilege, secret
   hygiene, and runtime boundary policy such as user identity, writable paths,
   or sandbox posture where relevant?
10. **Performance Posture**: Are new latency, scale, or throughput claims
    measured and recorded instead of asserted?
11. **Source Truth**: Do README, help text, and governance docs still describe
    the shipped system accurately?
12. **Evidence Integrity**: Is validation proof present, machine-readable, and
    tied to this change and its claimed scope?
13. **Self-Healing Loop**: Were findings either fixed, revalidated, or turned
    into explicit tracked backlog items with evidence before closure?

## Production-Ready Amplifiers

For a `Production Ready` or equivalent high-confidence claim, these questions
must also be defensible where relevant:

- has the real runtime or deployment path been exercised, not only unit tests
- has rollback, restore, replay, or compensation been validated instead of
  merely described
- have the touched failure, degraded, and recovery paths been exercised instead
  of only the happy path
- do release, runtime, and docs surfaces agree on what was actually shipped
- are no open high-risk findings left in the claimed scope
- can another operator review the evidence bundle and reach the same GO or
  NO-GO conclusion without improvisation

## Gate Enforcement

- if the answer to any question is **"No"**, the work is not complete
- the **"Production Ready"** label is earned only when all required gates are
  green at the same time
- bypassing a gate requires an explicit, recorded **Safety Exception** in the
  local project's `AGENTS.md`
