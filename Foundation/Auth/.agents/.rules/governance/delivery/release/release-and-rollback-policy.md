# Release And Rollback Policy

Version: 2.1.0
Status: Normative

## Promotion Path

Use this order unless explicitly overridden:

1. local proof
2. stage proof
3. production release

Promotion must be sequential and evidence-backed.

## GO / NO-GO Contract

GO requires all of the following:

- required validations are green
- release evidence is recorded
- rollback path is defined
- smoke or critical-path verification is present for the shipped surface
- security posture for the touched release surface is still valid
- observability or operator signals are sufficient to diagnose failure
- failure, degraded-mode, or refusal behavior has been checked for the touched
  surface when that surface can fail in more than one meaningful way

Any failed mandatory check is NO-GO.

## Release Evidence Contract

Release evidence should capture:

1. scope of change
2. exact validation commands or gate references
3. test or smoke results
4. known risks and accepted residual risks
5. rollback trigger, workflow, and expected recovery signal
6. links or paths to logs, reports, artifacts, or tickets that prove the claim
7. failure-mode probes or degraded-path checks for the touched public or
   operator-facing surface when applicable

## Stateful Release Contract

If the release mutates durable state, also require:

1. migration or mutation plan
2. backup or recovery posture
3. restore verification path
4. impact containment plan if rollback is not instantaneous

Stateful release without recovery proof is NO-GO.

## External Runtime Or Platform Handoff

If the release depends on another platform or team, define:

1. runtime start contract
2. required environment variables or bound configuration
3. health or readiness checks
4. pre-handoff validation
5. post-handoff acceptance criteria

## Rollback Contract

Every release-impacting change should define:

1. rollback trigger
2. rollback command or workflow
3. expected recovery signal
4. evidence location
5. operator owner or escalation path when rollback needs human judgment
6. failure-mode checkpoints that tell the operator whether rollback is required
   or the system is within acceptable degraded behavior

## Rollback Quality Rules

- Rollback should restore service faster than root-cause analysis.
- Rollback should fail closed when required dependencies or artifacts are missing.
- Rollback evidence should be recorded with the release evidence, not remembered later.
