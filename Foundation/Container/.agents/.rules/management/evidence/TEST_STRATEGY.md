# Test Strategy

Defines verification layers for this repository.

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

## Current Notes

Baseline strategy established. Per-release runs belong in `TEST_REPORTS.md`.
