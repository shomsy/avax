# Management

This folder is the execution memory layer.

## Canonical Files

- `TODO.md` for active implementation queue
- `BUGS.md` for active defects and regressions
- `ACTIVE.md` for the current visual board view of active tasks and bugs
- `IDEAS.md` for raw opportunities
- `DECISIONS.md` for ADR-lite decision log
- `TIMELINE.md` for timestamp format and estimation law
- `evidence/CHANGELOG.md` for completed evidence
- `evidence/RELEASE_CHECKLIST.md` for release readiness snapshots
- `evidence/RISK_REGISTER.md` for active and accepted risks
- `evidence/TEST_STRATEGY.md` for current test approach
- `evidence/TEST_REPORTS.md` for concrete test runs and outcomes
- `evidence/TRACE_REPORTS.md` for execution traces, replays, and high-risk run evidence
- `evidence/traces/` for per-run routing, replay, or operator trace bundles

New work should be recorded only in the canonical files above.

## Rule

- all new management and evidence records must follow `TIMELINE.md`
- queues without timestamps cannot support honest estimation or aging analysis
- `ACTIVE.md` is a visualization layer and must mirror `TODO.md` and `BUGS.md`
