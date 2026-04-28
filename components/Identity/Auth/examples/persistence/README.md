# Persistence Contract Examples

This directory contains reference persistence artifacts for the auth kernel.

## Included Artifacts

- `PdoSessionRegistry.php`: durable implementation example for
  `SessionRegistryInterface`
- `sql/session-registry.sql`: tracked session schema
- `sql/refresh-token-family.sql`: refresh family and rotation schema
- `sql/token-revocation.sql`: access token revocation schema
- `sql/mfa-challenge.sql`: MFA challenge schema
- `sql/audit-export-cursor.sql`: durable export cursor schema
- `migrations/*.sql`: sequential migration examples for adapter-owned rollout
- `sql/refresh-token-family-locking.sql`: transaction pattern for reuse detection

## Concurrency Notes

- refresh-token reuse detection should lock one token row or family row during
  rotation
- use `SELECT ... FOR UPDATE` or the equivalent database locking primitive
- mark the old token as rotated before committing the replacement token
- revoke the full family when a reused token appears after rotation

## Cleanup Notes

- prune expired sessions, reset tokens, MFA challenges, authorization codes,
  and passkey challenges with time-based jobs
- revoke or delete refresh-token families after retention windows expire
- keep audit export cursors only as long as the downstream export pipeline
  requires replay protection
- `../jobs/RunAuthMaintenanceJobs.php` shows one package-owned scheduler entry
  that can run all cleanup and export flows
