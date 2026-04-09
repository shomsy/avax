# Release Checklist

Use this checklist before every release decision.

## Checklist

- scope of release is explicit
- critical/high review findings resolved
- test strategy referenced
- test report recorded
- smoke or critical-path verification recorded
- failure, degraded-path, or refusal behavior recorded for touched public
  surfaces
- observability and operator signals reviewed
- security posture reviewed for touched surfaces
- known risks reviewed and accepted
- rollback route documented
- stateful recovery path documented when applicable
- changelog updated

## Latest Snapshot

Use snapshots in this shape:

- `snapshot_at`:
- `release_scope`:
- `decision`: go | no-go | hold
- `rollback_path`:
- `smoke_reference`:
- `risk_reference`:
- `notes`:

- `snapshot_at`: 2026-04-09 15:31 CEST
- `release_scope`: AUTH-012 auth-kernel refactor across public API, ingress runtime, token/session strategy unification,
  secondary security flows, docs, and evidence
- `decision`: hold
- `rollback_path`: revert AUTH-012 changeset and restore pre-kernel facade/tests/docs
- `smoke_reference`: `.agents/management/evidence/TEST_REPORTS.md`
- `risk_reference`: `.agents/management/evidence/RISK_REGISTER.md`
- `notes`: Core kernel passed syntax checks, PHPStan, and executable smoke paths. Hold remains because PHPUnit cannot
  run in this local environment without missing CLI extensions (`dom`, `xml`, `xmlwriter`, `mbstring`), and the optional
  Avax container adapter was not end-to-end verified against the real package.

- `snapshot_at`: 2026-04-09 18:10 CEST
- `release_scope`: AUTH-013 production-grade MFA subsystem across enrollment, challenge lifecycle, backup codes,
  recovery, step-up guards, docs, and release evidence
- `decision`: hold
- `rollback_path`: revert AUTH-013 changeset and restore pre-MFA-subsystem `Flow/Mfa/`, Auth facade, docs, and tests
- `smoke_reference`: `.agents/management/evidence/TEST_REPORTS.md`
- `risk_reference`: `.agents/management/evidence/RISK_REGISTER.md`
- `notes`: MFA kernel passes syntax checks, PHPStan on `System/`, and executable MFA smoke paths. Hold remains because
  the local CLI still cannot execute PHPUnit or Infection without `dom`, `xml`, `xmlwriter`, and `mbstring`.

- `snapshot_at`: 2026-04-09 19:21 CEST
- `release_scope`: AUTH-014 kernel and integration boundary freeze across extracted Avax container adapter, HTTP
  transport adapters, public API boundary docs, and compatibility evidence
- `decision`: hold
- `rollback_path`: revert AUTH-014 changeset and restore `System/Configuration/AuthServiceProvider`, pre-extraction
  docs, and pre-boundary tests
- `smoke_reference`: `.agents/management/evidence/TEST_REPORTS.md`
- `risk_reference`: `.agents/management/evidence/RISK_REGISTER.md`
- `notes`: Syntax and executable smoke paths passed without Composer. Hold remains because PHPUnit, PHPStan on the new
  adapter lane, and end-to-end Avax container verification could not run in this environment after the Composer
  toolchain was removed.
