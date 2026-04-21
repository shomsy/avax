---
title: capabilities-how-this-works
owner: auth-kernel
last_reviewed: 2026-04-21
classification: internal
---

# Capabilities How This Works

## What this folder is

`System/Capabilities/` groups the long-lived kernel owner zones. These coordinators present stable public capability
surfaces while delegating actual execution to runtime and flow classes deeper in the tree.

## Real commands or triggers that reach this folder

- `Auth` delegates consumer-facing operations into one of the capability owners.
- `AuthBuilder::ready()` assembles the owners after bootstrap validation succeeds.
- Integrations reach the same owners through the public `Auth` ingress, never by bypassing the kernel boundary.

## Exact upstream handoffs

- `System/Auth.php` -> `Access`, `Identity`, `ExternalIdentity`, `IdentitySync`, `Tenancy`, `Diagnostics`
- `System/Configuration/AuthBuilder.php` -> capability coordinators
- Capability coordinators -> `Runtime/`, `Flows/`, stores, registries, and external seams

## Why this root exists

This root keeps public ownership visible. Consumers should know which owner they are entering without having to learn
the entire runtime topology of the package.

## Where to debug first

- Public API drift: `System/Auth.php`
- Optional capability availability: the specific owner coordinator
- Ownership confusion: this page and the child `how-this-works.md` pages for each owner zone
