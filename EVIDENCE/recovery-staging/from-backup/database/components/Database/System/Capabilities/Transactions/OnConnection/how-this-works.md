---
title: system-capabilities-transactions-onconnection-how-this-works
owner: foundation-database-transactions
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / Transactions / On Connection How This Works

## What this folder is

This folder owns resolution of a transaction manager for one concrete connection.

## Real commands or triggers that reach this folder

- `Database::transactions()` and any transaction-wrapped query or migration path reach this folder.

## Exact upstream handoffs

- The parent slice hands work into this folder when it needs the behavior owned here.
- The files in this folder do the local work and return control upstream when their responsibility is complete.

## How this folder works

It is the handoff point between connection resolution and transaction control, so callers do not wire that boundary
themselves.

## The simplest story

- A caller reaches the parent Database surface or the owning parent folder.
- This folder handles the one responsibility it owns.
- The result returns upstream or moves to the next local slice.

## The first important path

- The caller enters through the Database surface or the parent folder.
- `OnConnection.php` participates directly in the behavior owned by this folder.
- Control returns to the caller or the next local slice once this folder finishes its job.

## Main units in this folder

- `OnConnection.php` participates directly in the behavior owned by this folder.

## Writes and side effects

- This slice can begin, commit, roll back, or nest transactions through savepoints.

## Failure shape

- Broken nesting, rollback defects, or transaction-state drift surface through this slice.

## Debug first

- Start with `OnConnection.php` participates directly in the behavior owned by this folder.

