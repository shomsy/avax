---
title: system-how-this-works
owner: auth-kernel
last_reviewed: 2026-04-21
classification: internal
---

# System How This Works

## What this folder is

`System/` is the kernel. It owns the public `Auth` facade, the configuration root, the capability coordinators, the
flow/runtime execution path, and the shared foundation types that every integration depends on.

## Real commands or triggers that reach this folder

- Application bootstrap calls `Auth::configuration()->ready()`.
- Optional container/bootstrap adapters resolve the same kernel through
  `integrations/avax-container/AuthServiceProvider.php`.
- Runtime requests hit `Auth::authenticateRequest()`, `Auth::login()`, `Auth::refresh()`, capability owners, or one of
  the direct convenience methods on `Auth`.

## Exact upstream handoffs

- `integrations/` adapts transport, container, cookie, and release tooling concerns and then hands work into `System/`.
- `Auth` forwards high-frequency calls into capability owners.
- `AuthBuilder` is the only supported composition root inside the kernel.

## One concrete path through this folder

1. Bootstrap enters through `Auth::configuration()`.
2. `AuthBuilder::ready()` validates the configured identity backends and optional capability readiness.
3. The builder assembles capability owners such as `Access`, `Identity`, `ExternalIdentity`, `IdentitySync`, and
   `Tenancy`.
4. Runtime calls enter through `Auth` and are delegated to the owning capability or flow.
5. The flow updates stores, audit logs, and the immutable `AuthenticationContext`.

## Failure and refusal shape

- Missing identity backends fail during bootstrap instead of producing a half-built kernel.
- Optional capability owners throw explicit capability-unavailable exceptions when their runtime contract is absent.
- Runtime refusals stay in domain exceptions such as `Unauthenticated`, `FreshMfaRequired`,
  `RefreshAuthenticationFailed`, and related auth-specific failures.

## Where to debug first

- Start in `System/Auth.php` for public API drift.
- Start in `System/Configuration/AuthBuilder.php` for bootstrap or readiness failures.
- Start in the owning capability folder if one specific surface behaves incorrectly.

## Terms that are easy to confuse

- `Auth` is the public kernel facade.
- `Access`, `Identity`, `ExternalIdentity`, `IdentitySync`, and `Tenancy` are capability owners.
- `Flows` and `Runtime` classes execute use cases; they are not composition roots.
