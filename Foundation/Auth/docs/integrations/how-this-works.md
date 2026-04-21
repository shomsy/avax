---
title: integrations-how-this-works
owner: auth-integrations
last_reviewed: 2026-04-21
classification: internal
---

# Integrations How This Works

## What this folder is

`integrations/` holds optional transport, container, cookie, header, diagnostics, and release-tooling adapters. It is
deliberately downstream of the kernel.

## Real commands or triggers that reach this folder

- Framework/container bootstrap uses `integrations/avax-container/`.
- HTTP surfaces use `integrations/http/`.
- Cookie/header helpers translate transport state into kernel input/output.
- Release evidence and crypto drills run from `integrations/release/`.

## Exact upstream handoffs

- Integrations read transport or environment state.
- They translate that state into kernel DTOs and call into `System/Auth` or lower capability owners.
- They map kernel-safe failures back into transport-safe outcomes.

## Guardrail

If an integration needs new framework behavior, the change belongs here unless the kernel contract itself is missing.
`System/` must stay framework-neutral.
