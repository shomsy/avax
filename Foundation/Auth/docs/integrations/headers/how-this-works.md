---
title: integrations-headers-how-this-works
owner: auth-integrations
last_reviewed: 2026-04-21
classification: internal
---

# Header Integrations How This Works

## What this folder is

`integrations/headers/` translates request headers into kernel-facing sender-constraint, forwarding, and auth-ingress
inputs.

## Real commands or triggers that reach this folder

- HTTP request verification that depends on authorization or forwarding headers
- Proxy-aware auth ingress preparation

## Exact upstream handoffs

- HTTP adapters read headers.
- Header integrations normalize or validate them.
- Kernel request DTOs receive only the normalized values they need.

## Guardrail

Header normalization is transport work. The kernel should receive intent, not raw transport artifacts.
