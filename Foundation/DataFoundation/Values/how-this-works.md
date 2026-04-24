---
title: Values-how-this-works
owner: foundation-data
last_reviewed: 2026-04-24
classification: internal
---

# Values How This Works

## What this folder is

This folder owns semantic single-purpose values with strict invariants.

## Real commands or triggers that reach this folder

- app code constructing `Option`, `Result`, `Uuid`, `NonEmptyString`, `Money`, or a range type
- other lanes replacing raw primitives with explicit meaning

## Exact upstream handoffs

- app code -> `Values/<Lane>/<Type>.php`
- `Structures/` and `Flows/` may accept values but do not define them

## The simplest story

- a primitive enters a value constructor
- the constructor validates the invariant immediately
- the caller receives a value that cannot represent the invalid state

## Debug first

- start here when primitive obsession leaks into calling code
- start here when invalid input reaches runtime later than it should

## What to remember

- values do not depend on collections, structures, or flows
- each value says one honest truth
