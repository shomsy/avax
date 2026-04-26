---
title: integrations-http-how-this-works
owner: auth-integrations
last_reviewed: 2026-04-21
classification: internal
---

# HTTP Integrations How This Works

## What this folder is

`integrations/http/` translates HTTP requests and responses into kernel DTOs and outcomes. It publishes package-owned
protocol surfaces without leaking HTTP handling into `System/`.

## Real commands or triggers that reach this folder

- OIDC provider HTTP routes
- SCIM HTTP routes
- Tenant-security admin HTTP routes

## Exact upstream handoffs

- HTTP adapter reads request state and validates transport concerns.
- It calls into `Auth` or the owning capability surface.
- It maps domain results and failures back into transport-safe responses.

## Guardrail

Transport parsing stays here. If a change requires moving HTTP objects into `System/`, the boundary is being broken.
