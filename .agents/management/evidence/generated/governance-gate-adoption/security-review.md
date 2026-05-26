# Security Review

Generated: 2026-05-26

## Security-Sensitive Effect

This pass does not implement authentication, authorization, token, session, cryptography, request-signing, persistence, or runtime security behavior.

It does affect security assurance because shallow-test and PHPStan findings can hide security regressions.

## Controls Added

- Shallow-test changed mode fails on any finding in changed test files.
- Security-sensitive shallow-test findings remain HIGH in the baseline.
- Identity rewrite policy treats changed-scope shallow-test findings as HARD BLOCKERS.
- Changed Identity code remains subject to PHPStan changed mode under `components/` and `tests/`.

## Threat Decision

Baseline debt is not accepted as secure. It is tracked as legacy debt. New or touched security-sensitive tests must provide negative and fail-closed proof.
