---
title: configuration-how-this-works
owner: auth-kernel
last_reviewed: 2026-04-21
classification: internal
---

# Configuration How This Works

## What this folder is

`System/Configuration/` owns kernel assembly. Its job is to turn explicit user-supplied dependencies plus safe defaults
into a usable `Auth` kernel without leaking framework/container ownership into the core package.

## Real commands or triggers that reach this folder

- `Auth::configuration()` returns `AuthBuilder`.
- Tests and integrations call fluent `with...()` methods and finish with `ready()`.
- The Avax container adapter ultimately delegates into the same builder path.

## Exact upstream handoffs

- Consumers provide stores, runtimes, and registries through the builder.
- `ready()` assembles flow owners and capability coordinators and returns `Auth`.

## Readiness rules that matter

- At least one identity backend must exist: session or JWT.
- OAuth/OIDC-specific configuration requires JWT plus refresh-token storage.
- OIDC request-object storage requires an OIDC provider.
- Passkey-specific configuration requires the passkey runtime.
- Federation-specific configuration requires the federation runtime.
- SCIM-specific storage requires a provisionable user source.

## Why the readiness profile exists

`AuthBuilder::ready()` used to scatter optional-capability booleans across the method body.
`Readiness/AuthCapabilityReadiness.php` now centralizes the supported combinations, while `AuthCapabilityRequests` and
`AuthBootstrapValidator` make partial configuration requests fail before deep object assembly starts.

## Build phases

1. Capture configuration through fluent `with...()` methods.
2. Validate core bootstrap and optional-capability requests.
3. Resolve defaults for stores, runtimes, and helpers that the package can safely own.
4. Assemble capability coordinators and flow/runtime owners.
5. Return one buildable `Auth` kernel.

## Failure and refusal shape

- Unsupported bootstrap combinations fail before the kernel is returned.
- Optional capability surfaces are assembled as explicitly unavailable owners when their runtime contracts are missing.

## Where to debug first

- Use `tests/Configuration/AuthBuilderTest.php` for bootstrap regressions.
- Check `integrations/avax-container/AuthServiceProvider.php` if container assembly diverges from the builder.
