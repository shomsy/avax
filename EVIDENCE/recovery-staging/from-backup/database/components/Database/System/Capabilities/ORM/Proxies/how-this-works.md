---
title: system-capabilities-orm-proxies-how-this-works
owner: foundation-database-orm
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / ORM / Proxies How This Works

## What this folder owns

This folder owns lazy-reference helpers used when an entity relation should resolve only when it is first accessed.

## The simplest story

- ORM code creates a `LazyReference.php` with a loader callback.
- The relation can be carried around without loading the target immediately.
- The callback runs only when the value is explicitly resolved.

## Direct files in this folder

- `LazyReference.php` is the small lazy-loader wrapper used by relation-aware ORM flows.

## Debug first

- Start here when lazy relation behavior eagerly loads data or never resolves data.

## What to remember

- This folder is intentionally small.
- It exists to keep lazy loading mechanics separate from metadata and persistence code.

