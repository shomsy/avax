---
title: Internal-how-this-works
owner: foundation-data
last_reviewed: 2026-04-24
classification: internal
---

# Internal How This Works

## What this folder is

This folder owns neutral substrate used by multiple public lanes.

## Real commands or triggers that reach this folder

- root public types calling path, mutation, comparison, or conversion atoms
- collection capability slices normalizing input and guarding invariants

## Exact upstream handoffs

- `Collection.php` -> `Internal/Mutability/MutationGuard.php`
- `Collections/*` -> `Internal/Paths/DotPath.php`
- `Interop/*` -> `Internal/Conversion/*.php`

## The simplest story

- a public type keeps its semantic API
- it delegates boring mechanics to `Internal/`
- `Internal/` returns a stable technical result without leaking a new public concept

## Debug first

- start in `Paths/DotPath.php` for path traversal bugs
- start in `Comparison/Comparator.php` for uniqueness or ordering bugs

## What to remember

- `Internal/` is reusable mechanics down
- nothing here should pretend to be the public product
