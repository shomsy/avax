---
title: Collections-how-this-works
owner: foundation-data
last_reviewed: 2026-04-24
classification: internal
---

# Collections How This Works

## What this folder is

This folder owns array-backed collection families and the legacy capability slices that power `Collection`.

## Real commands or triggers that reach this folder

- `Collection.php` fluent operations
- app code constructing `DataList`, `Set`, `Map`, `MultiMap`, or `Sequence`

## Exact upstream handoffs

- `Collection.php` -> `Collections/Read|Write|Transform|Aggregate|Order|Search|Convert/*`
- app code -> `Collections/DataList/DataList.php` and sibling public family types

## The simplest story

- a caller chooses a collection shape
- the chosen type enforces its invariant
- capability slices perform mechanical work for the generic `Collection` engine

## Debug first

- start in the public family subfolder when the wrong invariant is exposed
- start in the capability slices when `Collection` fluent behavior is wrong

## What to remember

- `Collection` is still important, but it is now one lane inside a larger system
- `DataList`, `Set`, `Map`, and `Sequence` are first-class siblings, not aliases
