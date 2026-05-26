# Source Principles: The Pragmatic Programmer

## Role in Governance

Use this source family for automation, evidence, feedback loops, version-control truth, reversibility, and disciplined tool use.

## DRY as Knowledge Duplication

DRY means one authoritative representation of knowledge, not blindly removing similar-looking code.

## Orthogonality

Changes should affect one concern. Hidden coupling between unrelated concerns is a review finding.

## Reversibility

Decisions should keep rollback paths open unless the cost is consciously accepted.

## Tracer Bullets

A tracer bullet is a production-grade thin path through the system with real validation.

## Prototypes vs Tracer Code

A prototype is disposable learning code. It must not be promoted without current tests and architecture review.

## Broken Windows

Do not normalize fake GREEN, stale evidence, unclassified debt, or vague TODOs.

## Design by Contract

Public and internal boundaries should state preconditions, postconditions, invariants, and failure behavior.

## Plain Text

Governance, evidence, and templates should be diffable, reviewable, and tool-readable.

## Automation as Habit

Repeated manual checks should become scripts, checkers, templates, or review-pack validation.

## Version-Control Truth

Current git state is the first source of truth. Reports that contradict git are stale.

## Debugging Without Blame

Classify the failure, reproduce it, fix cause, and write regression evidence.

## Do Not Outrun Your Headlights

Autonomous agents must work in bounded slices and stop on unclear ownership or unsafe validation.

## Shell/Tooling Discipline

Commands used for evidence must be exact, reproducible, and include exit codes.

## Evidence as Automation

Evidence is not prose about proof; it is command output, file lists, findings, and decisions with dates.

## Agent Stop Conditions

Wrong branch, unclassified dirty state, missing mandatory file, failed required gate, or contradictory evidence.

## Review Questions

What is the single source of truth? What can be automated? What is reversible? What did validation prove?

## Severity Rules

Fake evidence is BLOCKER. Vague status is HIGH. Missing automation for repeated critical checks is MEDIUM.

## Checker Mapping

SDLC runners, review pack validator, actual-changes pack rules, and evidence closure reports.

