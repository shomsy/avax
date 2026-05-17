---
title: system-capabilities-connections-runwithconnection-how-this-works
owner: foundation-database-connections
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / Connections / Run With Connection How This Works

## What this folder is

This folder owns callback-style connection usage.

## Real commands or triggers that reach this folder

- `Database::connections()` and any runtime path that needs a concrete connection or PDO handle reach this folder.

## Exact upstream handoffs

- The parent slice hands work into this folder when it needs the behavior owned here.
- The files in this folder do the local work and return control upstream when their responsibility is complete.

## How this folder works

It resolves the right connection for one callback and leaves the underlying connection or pool runtime to restore state
on the way out.

## The simplest story

- A caller reaches the parent Database surface or the owning parent folder.
- This folder handles the one responsibility it owns.
- The result returns upstream or moves to the next local slice.

## The first important path

- The caller enters through the Database surface or the parent folder.
- `RunWithConnection.php` participates directly in the behavior owned by this folder.
- Control returns to the caller or the next local slice once this folder finishes its job.

## Main units in this folder

- `RunWithConnection.php` participates directly in the behavior owned by this folder.

## Writes and side effects

- This slice can open connections, reuse pools, or expose PDO handles.

## Failure shape

- Wrong config, unreachable databases, pool exhaustion, or connection-state drift surface through this slice.

## Debug first

- Start with `RunWithConnection.php` participates directly in the behavior owned by this folder.

