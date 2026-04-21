# Observability And Error Envelope Contract

Version: 1.0.0
Status: Normative

Use this contract for APIs, jobs, CLI tools, or services that expose operator
visible failures.

## Machine-Readable Error Envelope

All externalized errors should expose a stable structure such as:

```json
{
  "status": "error",
  "code": "MACHINE_CODE",
  "message": "Human-readable message",
  "details": [],
  "traceId": "request-or-operation-id"
}
```

Rules:

- `code` is stable for automation and client handling
- `message` is human-readable but should not leak secrets
- `details` may be empty, but should stay machine-parseable
- `traceId` or equivalent correlation identifier should always exist

## Error Code Baseline

Each child repository should define a stable mapping for:

- validation failures
- auth or trust failures
- permission failures
- not found cases
- conflict cases
- domain rule failures
- internal failures
- unavailable dependency failures

## Tracing Contract

Where the runtime model allows it, logs and telemetry should expose:

- `traceId`
- actor or subject identifier when safe
- tenant, account, or workspace identifier when relevant
- request or operation path
- status or result
- duration
- release or version identifier for deploy-related triage

## Execution Trace Package

For workflows, agents, jobs, and releases that span multiple steps, basic logs
are not enough. Produce an execution trace package that lets an operator answer
what happened, why it happened, and where it can be resumed.

Recommended artifact layout:

- `.agents/management/evidence/TRACE_REPORTS.md` for the human-readable index
- optional per-run bundles under `.agents/management/evidence/traces/`

Each trace package should capture at least:

- `traceId`
- `sessionId` or `taskId` when applicable
- start and end timestamps
- actor, workspace, or tenant identifiers when safe
- triggering request, command, or workflow name
- tool or step timeline with durations
- approvals, escalations, and gate decisions
- feature-gate snapshot when behavior depends on flags
- release or build identifier
- final status and resume point if incomplete

## Replay And Reconstruction Contract

High-value systems should make important executions replayable or at least
reconstructable.

For replay-oriented traces, capture:

- effective configuration source or layer
- model, provider, or runtime version when AI or distributed systems are involved
- input references or redacted input summaries
- emitted outputs or output references
- deterministic seed or equivalent when supported
- event order for streamed or asynchronous steps

If the exact execution cannot be replayed, the trace should still support
operator-grade reconstruction of the decision path.

## Mandatory Full-Trace Scenarios

Full trace capture should be required for:

1. release and rollback operations
2. destructive migrations or data repair jobs
3. security-sensitive approvals or trust-boundary escalations
4. external write operations performed autonomously
5. multi-step agent or workflow runs that may pause, resume, fork, or retry

## Redaction Rules

- Do not store raw secrets, access tokens, or private keys in traces.
- Minimize personal or tenant-sensitive data; prefer identifiers over payloads.
- If raw payload retention is unsafe, store a redacted summary plus location of
  the canonical secured source.
- Trace usefulness must survive redaction. A redacted trace that explains
  nothing is an operational failure.

## Operational Usage

Incident triage should capture at least:

1. deployment or CI reference when relevant
2. `traceId` or equivalent operation identifier
3. actor, tenant, or scoped ownership identifier when relevant
4. path or operation name and timestamp

If this tuple cannot be reconstructed, diagnosability is too weak.

This contract is compatible with the stronger trace-and-replay patterns used by
systems such as Playwright traces, LangChain callback traces, Codex rollouts,
and Dify LLMOps pipelines.
