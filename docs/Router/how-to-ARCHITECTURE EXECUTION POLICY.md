# ARCHITECTURE EXECUTION POLICY

**Post-Review Execution Rules for All Systems**

## 1. Purpose

This document defines the mandatory transition from architecture review to execution.

## 2. Review Closure Rule

An enterprise architecture review is considered closed when:

- A final decision is recorded (`Keep and Improve`, `Redesign`, or `Rewrite Candidate`)
- Findings and next steps are explicitly listed
- The system is classified as viable for evolution

## 3. Execution Mode Declaration (Critical)

After review closure, the project enters Execution Mode.

Execution Mode means:

- Work performed is implementation, not analysis
- Code changes are expected
- Architectural intent is already agreed

### Rules

- Each execution pass must result in a compilable, runnable system
- Rolling back to the pre-pass state is not the default
- Rollback is allowed only if explicitly agreed before the pass begins
- "Leaving the repository untouched" is not a neutral action during execution mode

## 4. Iteration Contract (Mandatory for All Refactors)

All execution work must be split into iterations.

Each iteration must define:

- Exactly one primary goal
- Maximum two goals, only if tightly coupled
- Explicit list of what must NOT be changed
- Constraints
- Completion criteria

## 5. Scope Discipline Rule

During an iteration:

- If additional issues are discovered: they must be noted but NOT implemented
- Scope changes require explicit approval BEFORE coding

## 6. Definition of Done

Refaktor is done ONLY when:

- New structure reads as 4 real flows
- Public API is not broken
- All characterization and integration tests pass
- All ownership folders have `how-this-works.md`
- Documentation mirrors source tree
- No generic bucket exists that lies about ownership
- No dead synonymous architecture remains
