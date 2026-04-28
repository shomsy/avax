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

No release snapshot recorded yet.
