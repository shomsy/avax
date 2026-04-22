---
title: integrations-cookies-how-this-works
owner: auth-integrations
last_reviewed: 2026-04-21
classification: internal
---

# Cookie Integrations How This Works

## What this folder is

`integrations/cookies/` maps browser cookie state into kernel session allowances and transport-safe decisions.

## Real commands or triggers that reach this folder

- Browser request handling that needs session-cookie posture
- Middleware/bootstrap code deciding whether cookie-backed auth should run

## Exact upstream handoffs

- HTTP or framework code reads cookies.
- Cookie integration code resolves whether the session lane may participate.
- The request then continues into the kernel authentication path.

## Guardrail

Cookie parsing and policy translation stay here, not inside `System/`.
