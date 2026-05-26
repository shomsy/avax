# Source Principles: AntiPatterns, Refactoring, and Patterns

## Role in Governance

Use this source family when detecting recurring failure shapes, refactoring safely, or selecting a design pattern.

## AntiPattern Definition

An anti-pattern is a recurring bad solution with recognizable symptoms, consequences, and a refactored solution.

## Refactored Solution

Every anti-pattern finding needs a correction path, not only a label.

## Code Smells

Large class, long method, duplicate logic, primitive obsession, hidden dependencies, vague names.

## Architecture Smells

Service locator, fake abstraction, generic buckets, boundary without owner, runtime leakage, circular dependency.

## Project/Process Smells

Analysis paralysis, documentation theater, fake GREEN, unclassified debt, no stop condition.

## AI-Specific Failure Modes

AI often adds polished indirection, shallow tests, broad managers, and confident evidence language without proof.

## Blob/God Object

One unit owns unrelated responsibilities and becomes the center of change.

## Spaghetti Code

Control flow and dependency direction are tangled enough that local reasoning fails.

## Golden Hammer

A familiar pattern is used regardless of problem fit.

## Cut-and-Paste Programming

Logic is duplicated instead of extracting an owned capability or accepting deliberate repetition.

## Stovepipe System

A vertical slice cannot reuse or integrate with the rest of the platform.

## Analysis Paralysis

Decision-making never converges into bounded evidence and implementation.

## Architecture Theater

Architecture artifacts exist but do not guide validation or decisions.

## Fake Abstraction

Indirection exists without a real variation point or boundary.

## Service Locator

Runtime code pulls dependencies from a container instead of receiving them explicitly.

## Generic Bucket

Folders or classes named common/shared/util collect unrelated ownership.

## Shallow Tests

Tests prove construction or assertions, not behavior.

## Pattern Intent

A pattern is acceptable only when its intent matches the problem.

## Pattern Consequences

Every pattern adds trade-offs: indirection, lifecycle, coupling, testing cost, and naming burden.

## Pattern Abuse

Singleton for mutable state, Factory as service locator, Strategy for one behavior, Adapter reaching into private internals.

## Safe Refactoring

Refactoring preserves behavior and uses characterization tests before risky moves.

## Characterization Tests

Characterization captures current externally observable behavior before structure changes.

## Evidence Requirements

`antipattern-review.md`, `refactoring-safety.md`, `pattern-decision.md`, tests, and changed-scope validation.

## Checker Mapping

`check-antipatterns.php`, shallow-test checker, architecture fitness checker, and code review.

## Severity Rules

Service locator in runtime code is BLOCKER. Shallow tests used for GREEN are HIGH. Fake abstraction is MEDIUM/HIGH by blast radius.

## Stop Conditions

Stop when pattern fit, refactoring safety, or anti-pattern remediation cannot be stated.

