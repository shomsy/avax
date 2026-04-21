---
title: flows-how-this-works
owner: auth-kernel
last_reviewed: 2026-04-21
classification: internal
---

# Flows How This Works

## What this folder is

`System/Flows/` owns use-case execution for shared auth stories such as login, registration, current-user projection,
password recovery, and verification. These classes do work; they do not decide package composition.

## Real commands or triggers that reach this folder

- `Auth` convenience methods eventually land in one of these flows.
- Capability owners call flow classes for concrete execution.
- Request-authentication ingress resolves into `CheckAuthentication/`.

## Exact upstream handoffs

- `System/Auth.php` -> capability owner -> flow class
- `System/Configuration/AuthBuilder.php` wires the flow graph during bootstrap
- Flows -> stores, registries, current-authentication state, audit log, and result DTOs

## The canonical output contract

- Request-resolution ends in `AuthenticationContext`.
- Login and token flows end in `AuthenticationResult`.
- Registration and change flows end in dedicated result objects or explicit domain failures.

## Guardrail

If code in this folder starts deciding framework behavior or container resolution, it is in the wrong place. That
belongs in `integrations/` or `System/Configuration/`.
