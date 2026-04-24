---
title: Exceptions-how-this-works
owner: foundation-data
last_reviewed: 2026-04-24
classification: internal
---

# Exceptions How This Works

## What this folder is

This folder owns the DataFoundation exception hierarchy.

## Real commands or triggers that reach this folder

- any invariant failure during value construction
- any failed conversion, mutation guard, or structure/flow validation

## Exact upstream handoffs

- public type constructor -> `Exceptions/<Type>.php`
- internal converter or guard -> `Exceptions/<Type>.php`

## The simplest story

- a public or internal unit detects an invalid state
- it throws the narrowest named exception in this folder
- the caller learns exactly which invariant failed

## Debug first

- start here when exception naming feels too generic
- start here when failure messages stop being specific

## What to remember

- every exception name must say what failed honestly
- `DataFoundationException` is the root, not a catch-all hiding place
