---
title: system-capabilities-transactions-exceptions-how-this-works
owner: foundation-database-transactions
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / Transactions / Exceptions How This Works

## What this folder is

This folder defines the failure types used by the owning slice.

## Real commands or triggers that reach this folder

- `Database::transactions()` and any transaction-wrapped query or migration path reach this folder.

## Exact upstream handoffs

- The parent slice hands work into this folder when it needs the behavior owned here.
- The files in this folder do the local work and return control upstream when their responsibility is complete.

## How this folder works

Runtime code throws these exceptions when the slice-specific invariants break, so callers can distinguish this failure
family from the rest of the component.

## The simplest story

- A caller reaches the parent Database surface or the owning parent folder.
- This folder handles the one responsibility it owns.
- The result returns upstream or moves to the next local slice.

## The first important path

- The caller enters through the Database surface or the parent folder.
- `TransactionException.php` participates directly in the behavior owned by this folder.
- Control returns to the caller or the next local slice once this folder finishes its job.

## Main units in this folder

- `TransactionException.php` participates directly in the behavior owned by this folder.

## Writes and side effects

- This slice can begin, commit, roll back, or nest transactions through savepoints.

## Failure shape

- Broken nesting, rollback defects, or transaction-state drift surface through this slice.

## Debug first

- Start with `TransactionException.php` participates directly in the behavior owned by this folder.

