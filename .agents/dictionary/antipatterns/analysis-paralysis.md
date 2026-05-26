# AntiPattern: Analysis Paralysis

## What It Is

The work keeps expanding analysis, documents, or alternatives without producing a bounded decision, executable guardrail, or safe next slice.

## Symptoms

- Many options are listed with no decision criteria.
- Documentation grows while no validation or evidence changes.
- The next action is unclear after the report.
- Risks are named but not classified with owner and mitigation.

## Why It Is Dangerous

Analysis paralysis wastes review time and can hide the absence of executable progress or stop conditions.

## Common AI Failure Mode

The agent writes broad strategy text to look thoughtful instead of closing a concrete blocker or asking for the missing decision.

## How to Fix

State the smallest safe decision, list trade-offs, add a fitness function or manual review step, and stop only on a real unresolved blocker.

## Allowed Exceptions

Exploration is allowed when the explicit deliverable is research and the output includes decision criteria and a next action.

## Severity

MEDIUM for planning drift. HIGH when it blocks remediation or masks missing evidence for a readiness claim.
