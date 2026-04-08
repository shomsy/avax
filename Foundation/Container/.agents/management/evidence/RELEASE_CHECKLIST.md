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

- `snapshot_at`: `2026-04-08 17:04 CEST`
  `release_scope`: `ownership-aware composition foundations`
  `decision`: `hold`
  `rollback_path`: `revert the ownership-aware composition wave commits and rebuild compiled artifacts`
  `smoke_reference`: `.agents/management/evidence/TEST_REPORTS.md`
  `risk_reference`: `.agents/management/evidence/RISK_REGISTER.md`
  `notes`: `Verification is green, but the full ownership-aware program still has follow-up work captured in TODO-016 before a complete 12/10 claim should be made.`

- `snapshot_at`: `2026-04-08 18:16 CEST`
  `release_scope`: `ownership-aware composition closure`
  `decision`: `go`
  `rollback_path`: `revert the ownership-aware composition closure commits and rebuild compiled artifacts`
  `smoke_reference`: `.agents/management/evidence/TEST_REPORTS.md`
  `risk_reference`: `.agents/management/evidence/RISK_REGISTER.md`
  `notes`: `All ownership-aware closure criteria now have code, tests, docs, and green validation evidence. Peer benchmark targets remain optional and were not configured in this local run.`
