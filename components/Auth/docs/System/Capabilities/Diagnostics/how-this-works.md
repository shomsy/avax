---
title: diagnostics-how-this-works
owner: auth-kernel
last_reviewed: 2026-04-21
classification: internal
---

# Diagnostics How This Works

## What this folder is

`System/Capabilities/Diagnostics/` owns operator-facing diagnostic behavior that belongs inside the kernel contract:
audit coordination and explainability of auth failures or posture requirements.

## Real commands or triggers that reach this folder

- Runtime flows record audit events through `AuditLogInterface`.
- Consumers call `Auth::diagnostics()` or facade helpers such as `explainAccessDenied()`.
- Optional diagnostics integrations export or forward the resulting events outside the kernel.

## Exact upstream handoffs

- `System/Auth.php` -> `Diagnostics`
- Flow/runtime classes -> `AuditLogInterface`
- `Diagnostics` -> `AuthIssueExplainer`

## The simplest story

- A flow records an event or a consumer asks for a human-safe explanation.
- Diagnostics keeps the wording and emitted audit shape kernel-owned.
- Integrations can publish those artifacts, but they do not redefine their meaning.

## Where to debug first

- Missing or wrong operator explanation: `Explainability/`
- Audit shape drift: `Audit/`
- Export behavior: `integrations/diagnostics/`
