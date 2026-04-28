---
title: avax-container-how-this-works
owner: auth-integrations
last_reviewed: 2026-04-21
classification: internal
---

# Avax Container How This Works

## What this folder is

This adapter registers the auth kernel into the optional Avax container without moving container ownership into
`System/`.

## Real commands or triggers that reach this folder

- Application bootstrap registers `AuthServiceProvider`.
- Container resolution asks for `Auth`, `AuthInterface`, or supporting stores/services.

## Exact upstream handoffs

- The provider resolves user source, session storage, token stores, and optional diagnostics from the container.
- It builds the same kernel story that `Auth::configuration()->ready()` builds.
- It returns a usable `Auth` kernel, not a framework-specific auth service locator.

## What changed and why it matters

- The provider now matches the current `Identity` and `Auth` assembly contracts.
- It uses the stable backend assembly seam (`Identity::fromBackends(...)`) instead of tracking `Identity` constructor
  drift directly.
- The adapter is covered by container integration tests so drift is caught before release.
