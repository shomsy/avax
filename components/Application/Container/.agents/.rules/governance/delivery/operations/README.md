# Operations Governance

Reusable runtime and release contracts for repositories that ship executable or
deployable systems.

Use these documents when the adopting repository exposes APIs, services, jobs,
stateful storage, or operator-managed runtime surfaces.

## Available Contracts

- `observability-and-error-envelope.md`
- `staging-smoke-checklist.md`
- `security-launch-checklist.md`
- `backup-and-restore-runbook.md`
- `runtime-handoff-contract.md`

`observability-and-error-envelope.md` also defines the baseline trace and replay
evidence contract for multi-step executions.

## Rule

These files are reusable baselines.
Bind real commands, endpoints, and environment details in the child
repository's local overlays.

Pair these files with `../security/**` when the repository needs full secure
SDLC policy instead of only launch-day operational checks.
