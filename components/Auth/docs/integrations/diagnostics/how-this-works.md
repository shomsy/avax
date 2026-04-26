---
title: integrations-diagnostics-how-this-works
owner: auth-integrations
last_reviewed: 2026-04-21
classification: internal
---

# Diagnostics Integrations How This Works

## What this folder is

`integrations/diagnostics/` exports package-owned audit and notification signals to external sinks such as syslog, JSON
lines, webhooks, and queues.

## Real commands or triggers that reach this folder

- Audit export jobs
- Security notification dispatch
- Operator tooling that forwards diagnostics artifacts outside the package boundary

## Exact upstream handoffs

- `System/Capabilities/Diagnostics/` produces events and explanations.
- These integrations serialize or transport them.
- External systems consume the exported records.

## Guardrail

Meaning stays in the kernel; only delivery changes here.
