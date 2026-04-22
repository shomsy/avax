---
title: identity-sync-how-this-works
owner: auth-kernel
last_reviewed: 2026-04-21
classification: internal
---

# Identity Sync How This Works

## What this folder is

`System/Capabilities/IdentitySync/` owns SCIM directory flows, provisioning lifecycle actions, and lifecycle
orchestration over users that are managed by external systems or admin automation.

## Real commands or triggers that reach this folder

- `Auth::*Scim*`
- `Auth::suspendUser()`
- `Auth::reactivateUser()`
- `Auth::deprovisionUser()`

## Exact upstream handoffs

- `Auth` delegates into `IdentitySync`.
- `IdentitySync` splits work into `SCIM` and `Provisioning`.
- `SCIM` forwards to runtime classes for directory registration, token rotation, users, groups, and bulk.
- `Provisioning` forwards to lifecycle actions over existing users.

## Optional capability semantics

- `SCIM::isConfigured()` reports whether the SCIM runtime lane is fully assembled.
- `Provisioning::isConfigured()` reports whether admin lifecycle actions are wired.
- Unsupported operations now throw `IdentitySyncCapabilityUnavailable` with explicit operation names.

## Failure and refusal shape

- Invalid directory credentials stay in SCIM-specific runtime failures.
- Unsupported capability access does not masquerade as a generic runtime error anymore.
