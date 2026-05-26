# Source Principles: Use Cases and Domain Storytelling

## Role in Governance

Use this source family before behavior implementation, especially when task wording is vague.

## Scope

Scope states the system boundary and what is intentionally excluded.

## Actors

Actors can be users, systems, workers, admins, or external services.

## Actor Goals

The goal must describe completed value, not an implementation step.

## Stakeholders and Interests

List who cares and which guarantee each stakeholder needs.

## Preconditions

Preconditions are facts that must already be true before the scenario starts.

## Minimal Guarantees

Minimal guarantees state what remains true even when the scenario fails.

## Success Guarantees

Success guarantees state the durable result after completion.

## Trigger

The trigger is the event or request that starts the scenario.

## Main Success Scenario

Use 3-9 numbered steps. Each step should be actor intent or system responsibility.

## Extensions by Step Number

Failure/alternate paths reference the main step they extend.

## Extension Handling

Extensions must describe result and guarantee, not only "throw error".

## Failure Exits

Every sensitive failure exit must be fail-closed and observable.

## Scenario Quality Rubric

Strong scenario: actor goal, boundary, guarantees, failure paths, tests. Weak scenario: task title plus implementation guesses.

## Domain Storytelling Basics

Domain stories show actors, activities, work objects, and handoffs in plain language.

## Actors, Activities, Work Objects

Actor does activity using or producing a work object.

## Sequence Numbers

Use sequence numbers to make extension paths precise.

## As-Is vs To-Be Story

Use as-is for current behavior and to-be for target behavior when replacing legacy flows.

## Mermaid Diagram Guidance

Small sequence diagrams are useful when handoffs, async, or failure paths matter.

## Acceptance Criteria

Acceptance criteria must be observable and testable.

## Agent Review Questions

Who acts? What goal completes? What can fail? What must remain true? Which tests prove it?

## Evidence Requirements

`scenario-input.md`; `domain-discovery.md` when language or boundary changes.

## Severity Rules

Missing scenario evidence for production behavior is HIGH. Missing security failure path is BLOCKER.

## Stop Conditions

Stop when actor, goal, boundary, guarantee, or failure path cannot be stated.

## Examples

Weak step: "Validate token." Strong step: "System rejects an expired access token and records a safe authentication failure."

Security-sensitive: invalid credentials return denial without revealing which field was valid.

Data mutation: account email changes only after verification and old pending changes are invalidated.

Runtime-sensitive: worker clears request scope after each processed job.

