# How To Define Architecture Fitness Functions

## Purpose

Architecture rules must become executable or reviewable guardrails.

## What A Fitness Function Is

A fitness function is a command, static check, test, review procedure, or evidence rule that detects whether an architecture rule still holds.

## When Required

Required when adding or changing architecture governance, checkers, SDLC automation, reading order, root contracts, project overlay, or architecture north-star rules.

## Evidence Path

`.agents/management/evidence/generated/<task-name>/architecture-fitness-functions.md`

## Template Sections

- Architecture Rule
- Why This Rule Exists
- Failure Mode Prevented
- Fitness Function Type
- Command / Review Procedure
- Scope
- Baseline Mode
- Changed-Scope Mode
- Full Mode
- Expected Pass Signal
- Expected Fail Signal
- Evidence Path
- Owner
- Review Date

## Modes

- full: fail on all findings
- baseline: fail on new findings or baseline growth
- changed: fail on changed-scope violations

## Rules

- ADR link rule: architecture decisions should link ADR/evidence when significant.
- Failure mode rule: every guardrail states the failure it prevents.

## Severity

Missing fitness function for major governance change is HIGH.
Missing evidence for minor governance text is MEDIUM.

## Final Report Requirement

Final reports must name the guardrails added, changed, or intentionally skipped.

## Fitness Function Taxonomy

- static: scans source, paths, names, dependencies, or metadata;
- dynamic: executes runtime behavior or lifecycle checks;
- test: proves behavior, regression, security, or contract;
- performance: measures latency, throughput, memory, or hot-path work;
- security: detects fail-open, exposure, injection, or bypass risk;
- documentation: verifies required local docs, ADRs, dictionaries, or diagrams;
- evidence: verifies reports, manifests, timestamps, and validation outputs;
- manual-review: structured human review with explicit checklist and owner.

## Converting ADR Consequences Into Fitness Functions

For every ADR consequence, ask:

```text
What drift would make this decision false?
Can a command detect the drift?
Can a test prove the behavior?
If not, what exact review checklist catches it?
Who owns rechecking it?
```

## When Manual Review Is Acceptable

Manual review is acceptable only when:

- the rule depends on judgment that cannot be automated safely yet;
- the checklist is explicit;
- the reviewer role is named;
- the review date or trigger is recorded;
- the final status is not fake GREEN without evidence.

## Mode Semantics

- changed: hard fail on new changed-scope architecture violations;
- baseline: allow documented legacy findings, fail on new or increased findings;
- full: scan the complete repository and fail on every finding.

If a tool does not implement a mode, it must fail explicitly rather than silently pass.

## Fitness Function Examples

| Rule | Fitness Path |
|---|---|
| PublicSurface receives and delegates. | Static scan plus public contract tests. |
| Service locator forbidden in runtime code. | `check-antipatterns.php --mode=changed`. |
| Runtime-specific API must not leak into core. | Static scan for forbidden imports and review. |
| Review pack metadata must match ZIP contents. | `validate-review-pack-integrity.php`. |
| Self-explaining architecture required. | changed/full self-explaining checker. |
| Shallow tests forbidden for readiness. | changed/full shallow-test checker. |
| Coupling decision required for boundary changes. | `check-coupling-decisions.php --mode=changed`. |

## Hard Rule

An architecture rule without an executable or reviewable fitness path is YELLOW or RED. It is never pure GREEN.

## Accepted Exception Format

```text
rule:
reason automation is not available:
manual review procedure:
owner:
risk:
mitigation:
review_date:
```
