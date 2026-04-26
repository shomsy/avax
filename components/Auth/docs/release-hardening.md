# Release Hardening

Auth-sensitive releases need a stricter release bar than ordinary feature
packages.

## Required Gates

- dependency review
- SBOM generation
- secret scanning in CI
- artifact signing
- release provenance
- rollback proof for auth-sensitive releases

## Incident Drills

- signing-key rollover drill
- forced key-compromise drill
- legal-hold evidence preservation drill
- crypto-agility acceptance tests

## Local Tooling

- `php composer.phar dependency-review` enforces `tooling/dependency-review-policy.json`
  against the locked dependency graph and fails on unreviewed, unstable, or
  unapproved plugin dependencies
- `php composer.phar sbom` generates a CycloneDX-style JSON SBOM from the
  package metadata
- `php composer.phar secret-scan` scans the repo for committed secret patterns
- `php composer.phar release:provenance` emits a provenance record with git
  commit, branch, PHP version, and validation commands
- `php composer.phar rollback:proof` emits rollback evidence with target commit,
  rollback command, validation commands, and tracked artifact digests
- `php tooling/sign-release-artifact.php <artifact> <private-key.pem>` signs a
  release artifact with an RSA private key
- `php tooling/run-key-rollover-drill.php <key-ring.json>` verifies rollover
  overlap
- `php tooling/run-key-compromise-drill.php <before.json> <after.json>` proves
  old keys stop verifying after compromise rotation

## CI Workflows

- `.github/workflows/quality.yml` enforces analyse, strict analyse, PHPUnit,
  dependency review, and secret scan on `pull_request` and `main`
- `.github/workflows/release-hardening.yml` re-runs validation on tag and
  manual release flows, then publishes dependency-review, secret-scan, SBOM,
  provenance, and rollback-proof artifacts

## Policy Ownership

- dependency additions require updating `tooling/dependency-review-policy.json`
  in the same change that updates `composer.lock`
- release-sensitive changes should attach generated provenance and rollback
  evidence to the release record
- rollback evidence is a release artifact, not a substitute for application
  rollback drills
