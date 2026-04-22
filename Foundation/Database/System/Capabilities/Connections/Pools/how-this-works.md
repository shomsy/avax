---
title: system-capabilities-connections-pools-how-this-works
owner: foundation-database-connections
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / Connections / Pools How This Works

## What this folder is

This folder owns reusable connection-pool behavior.

## Real commands or triggers that reach this folder

- `Database::connections()` and any runtime path that needs a concrete connection or PDO handle reach this folder.

## Exact upstream handoffs

- The parent slice hands work into this folder when it needs the behavior owned here.
- The files in this folder do the local work and return control upstream when their responsibility is complete.

## How this folder works

The pool reuses healthy connections, opens new ones when limits allow, wraps borrowed usage, and records pool state on
acquire and release.

## The simplest story

- A caller reaches the parent Database surface or the owning parent folder.
- This folder handles the one responsibility it owns.
- The result returns upstream or moves to the next local slice.

## The first important path

- The caller enters through the Database surface or the parent folder.
- `BorrowedConnection.php` participates directly in the behavior owned by this folder.
- Control returns to the caller or the next local slice once this folder finishes its job.

## Main units in this folder

- `BorrowedConnection.php` participates directly in the behavior owned by this folder.
- `ConnectionPool.php` participates directly in the behavior owned by this folder.
- `Contracts/` holds the next narrower ownership slice below this folder.
- `DTO/` holds the next narrower ownership slice below this folder.
- `PoolState.php` participates directly in the behavior owned by this folder.
- `PooledConnectionAuthority.php` participates directly in the behavior owned by this folder.

## Writes and side effects

- This slice can open connections, reuse pools, or expose PDO handles.

## Failure shape

- Wrong config, unreachable databases, pool exhaustion, or connection-state drift surface through this slice.

## Debug first

- Start with `BorrowedConnection.php` participates directly in the behavior owned by this folder.
- Start with `ConnectionPool.php` participates directly in the behavior owned by this folder.
- Start with `Contracts/` holds the next narrower ownership slice below this folder.
