---
title: Composites-how-this-works
owner: foundation-data
last_reviewed: 2026-04-24
classification: internal
---

# Composites How This Works

## What this folder is

This folder owns fixed-shape multi-slot values such as `Pair`, `Tuple`, `Record`, and `MapEntry`.

## Real commands or triggers that reach this folder

- public APIs that need to return more than one thing honestly
- callers that need immutable grouped values without turning everything into arrays

## Exact upstream handoffs

- `Collection.php::pull()` -> `Composites/Pair/Pair.php`
- `Collections/Map/*.php` -> `Composites/MapEntry/MapEntry.php`

## The simplest story

- one public action produces multiple outputs
- a composite type carries those outputs with explicit shape
- the caller can read the slots without guessing array indexes

## Debug first

- start here when a return type becomes “mystery array” shaped
- start here when a grouped value starts behaving like a collection

## What to remember

- composites are fixed-shape values, not mini collections
