---
title: system-capabilities-connections-how-this-works
owner: foundation-database-connections
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / Connections How This Works

## What this folder is

This folder owns the public connections capability: direct connections, pooled connections, config normalization, and
the acquire or release lifecycle.

## Real commands or triggers that reach this folder

- `Database::connections()` and any runtime path that needs a concrete connection or PDO handle reach this folder.

## Exact upstream handoffs

- The parent slice hands work into this folder when it needs the behavior owned here.
- The files in this folder do the local work and return control upstream when their responsibility is complete.

## How this folder works

`Connections.php` stays small, while the child folders resolve names, open physical connections, manage pools, and
expose PDO access or callback-style borrowing.

## The simplest story

- A caller reaches the parent Database surface or the owning parent folder.
- This folder handles the one responsibility it owns.
- The result returns upstream or moves to the next local slice.

## The first important path

- The caller enters through the Database surface or the parent folder.
- `Connections.php` participates directly in the behavior owned by this folder.
- Control returns to the caller or the next local slice once this folder finishes its job.

## Main units in this folder

- `Connections.php` participates directly in the behavior owned by this folder.
- `Contracts/` holds the next narrower ownership slice below this folder.
- `Exceptions/` holds the next narrower ownership slice below this folder.
- `OpenConnection/` holds the next narrower ownership slice below this folder.
- `Pools/` holds the next narrower ownership slice below this folder.
- `ReadConnection/` holds the next narrower ownership slice below this folder.
- `RunWithConnection/` holds the next narrower ownership slice below this folder.
- `ValueObjects/` holds the next narrower ownership slice below this folder.

## Writes and side effects

- This slice can open connections, reuse pools, or expose PDO handles.

## Failure shape

- Wrong config, unreachable databases, pool exhaustion, or connection-state drift surface through this slice.

## Debug first

- Start with `Connections.php` participates directly in the behavior owned by this folder.
- Start with `Contracts/` holds the next narrower ownership slice below this folder.
- Start with `Exceptions/` holds the next narrower ownership slice below this folder.

