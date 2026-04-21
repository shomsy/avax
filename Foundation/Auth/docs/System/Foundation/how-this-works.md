---
title: foundation-how-this-works
owner: auth-kernel
last_reviewed: 2026-04-21
classification: internal
---

# Foundation How This Works

## What this folder is

`System/Foundation/` holds the tiny primitives the rest of the kernel depends on: time, id generation, and typed
composition exceptions. It exists to keep low-level shared mechanics explicit and small.

## Real commands or triggers that reach this folder

- `AuthBuilder` resolves defaults such as `Clock` and `IdGenerator`.
- Runtime flows consume `Clock` and `IdGeneratorInterface`.
- Bootstrap and adapter failures raise `ConfigurationException`.

## Exact upstream handoffs

- `System/Configuration/AuthBuilder.php` -> `Clock`, `IdGenerator`, `ConfigurationException`
- Runtime flows -> `Clock`, `IdGeneratorInterface`
- Integrations -> same primitives when they want deterministic tests or environment overrides

## Guardrail

This folder is not a dumping ground. If something here starts looking domain-shaped, it belongs back in the owning
capability or flow slice.
