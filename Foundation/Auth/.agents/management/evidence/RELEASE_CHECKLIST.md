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

- `snapshot_at`: 2026-04-15 02:03 CEST
- `release_scope`: AUTH-031 practical OIDC completeness closure across public/confidential request-object verification,
  OIDC dynamic client registration HTTP lifecycle, tenant-admin verifier-key propagation, and canonical status/evidence refresh
- `decision`: hold
- `rollback_path`: revert AUTH-031 changeset and restore the prior OIDC surface, OAuth client DTOs, tenant-admin HTTP surface,
  and canonical docs/evidence files
- `smoke_reference`: `.agents/management/evidence/TEST_REPORTS.md`
- `risk_reference`: `.agents/management/evidence/RISK_REGISTER.md`
- `notes`: Full PHPUnit, integration suite, PHPStan, strict PHPStan, source-truth, and system-shape all pass locally.
  Mutation tooling also executes end-to-end, but release remains on hold because mutation-quality posture is still open
  (`MSI 59%`, `394` escaped mutants, `63` timeouts) and manual observability/self-healing gates still require human closure.

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

- `snapshot_at`: 2026-04-09 21:12 CEST
- `release_scope`: AUTH-015 verification recovery and strict hardening across Composer toolchain restore, real test
  execution, adapter seam coverage, and production-readiness review
- `decision`: hold
- `rollback_path`: revert AUTH-015 changeset and restore the prior composer/tooling/test files plus the pre-hardening
  runtime fixes
- `smoke_reference`: `.agents/management/evidence/TEST_REPORTS.md`
- `risk_reference`: `.agents/management/evidence/RISK_REGISTER.md`
- `notes`: PHPUnit, baseline PHPStan, strict PHPStan, no-skip PHPUnit, and no-deprecation PHPUnit all pass locally.
  Hold remains only because mutation testing cannot execute in this environment without a PHP coverage driver
  (`xdebug`, `pcov`, or `phpdbg`).

- `snapshot_at`: 2026-04-09 21:44 CEST
- `release_scope`: AUTH-016 local coverage-driver installation and mutation-lane repair across repo-local `pcov`,
  Composer timeout removal, and mutation diagnostics
- `decision`: hold
- `rollback_path`: revert AUTH-016 changeset and remove the repo-local coverage tooling plus composer-script changes
- `smoke_reference`: `.agents/management/evidence/TEST_REPORTS.md`
- `risk_reference`: `.agents/management/evidence/RISK_REGISTER.md`
- `notes`: Mutation tooling now runs locally and no longer fails for missing drivers. Hold remains because the actual
  mutation signal is not production-ready yet: critical slices currently show low MSI, many escaped mutants, and
  timeout instability under high parallelism.

- `snapshot_at`: 2026-04-14 23:50 CEST
- `release_scope`: AUTH-029 release/source-truth tooling rewrite, canonical status cleanup, migration guide, deployment
  packaging docs, and non-canonical merged-artifact archival
- `decision`: hold
- `rollback_path`: revert AUTH-029 changeset and restore the prior tooling/docs/status files
- `smoke_reference`: `.agents/management/evidence/TEST_REPORTS.md`
- `risk_reference`: `.agents/management/evidence/RISK_REGISTER.md`
- `notes`: PHPUnit, baseline PHPStan, strict PHPStan, source-truth check, and migration check pass locally. Hold
  remains because mutation quality is still an open accepted release concern and quality-gate manual lanes still require
  human closure.

- `snapshot_at`: 2026-04-15 01:28 CEST
- `release_scope`: AUTH-030 source-truth evidence validation, system-shape enforcement, release-gate strengthening,
  integration-suite separation, and canonical deployment/explainability status refresh
- `decision`: hold
- `rollback_path`: revert AUTH-030 changeset and restore the prior release/source-truth scripts, phpunit suite shape,
  and canonical docs
- `smoke_reference`: `.agents/management/evidence/TEST_REPORTS.md`
- `risk_reference`: `.agents/management/evidence/RISK_REGISTER.md`
- `notes`: Full PHPUnit, dedicated integration suite, PHPStan, strict PHPStan, source-truth, system-shape, and
  release gate all pass locally. Hold remains because mutation-quality posture is still an active open risk and the
  manual observability/self-healing quality gates still require human closure.
