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
