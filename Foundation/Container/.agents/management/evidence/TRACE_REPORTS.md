# TRACE_REPORTS

Use this file as the index for execution traces and replay-oriented evidence.

Capture entries for:

- release and rollback runs
- destructive migrations or repair jobs
- security-sensitive escalations
- autonomous multi-step workflows
- external write operations performed by agents or integrations

Suggested fields per entry:

- timestamp
- trace ID
- task or session ID
- operation name
- risk level
- artifact path or bundle location
- outcome
- resume or rollback note when relevant
