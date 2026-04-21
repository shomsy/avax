# Test Strategy

Defines verification layers for this repository.

## Strict TDD Protocol

Auth implementation work uses strict TDD.

- no production code before a failing test exists
- no retroactive test writing after the solution is already known
- one behavior per iteration
- bug fixes require a regression test first
- refactors require characterization tests first
- security and auth flows require happy path, invalid input, edge case, abuse case, and regression coverage
- prefer unit tests; use integration tests only for boundaries, storage, middleware, adapters, or full flow seams
- if requirements are unclear, write the test cases first as a behavior spec
- each iteration ends with RED / GREEN / REFACTOR evidence

## Layers

- unit tests for pure logic and contracts
- integration tests for flow behavior and boundaries
- regression tests for previously fixed defects
- manual operator verification where automation is not yet available

## Rules

- bug fixes should include a regression verification path
- deterministic systems require deterministic tests
- test coverage should follow risk, not vanity metrics
- review this strategy with timestamped updates when the test posture changes
- do not treat green code without the required test evidence as complete

## Current Notes

Baseline strategy established. Per-release runs belong in `TEST_REPORTS.md`.
