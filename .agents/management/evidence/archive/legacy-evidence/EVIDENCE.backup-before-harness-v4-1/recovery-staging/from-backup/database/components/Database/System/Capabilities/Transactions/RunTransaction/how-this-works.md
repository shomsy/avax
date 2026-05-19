---
title: system-capabilities-transactions-runtransaction-how-this-works
owner: foundation-database-transactions
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / Transactions / Run Transaction How This Works

## What this folder is

This folder owns the actual transaction lifecycle.

## Real commands or triggers that reach this folder

- `Database::transactions()` and any transaction-wrapped query or migration path reach this folder.

## Exact upstream handoffs

- The parent slice hands work into this folder when it needs the behavior owned here.
- The files in this folder do the local work and return control upstream when their responsibility is complete.

## How this folder works

It decides whether to begin a real transaction, create a savepoint, commit, or roll back based on nesting depth and
callback outcome.

## The simplest story

- A caller reaches the parent Database surface or the owning parent folder.
- This folder handles the one responsibility it owns.
- The result returns upstream or moves to the next local slice.

## The first important path

- The caller enters through the Database surface or the parent folder.
- `RunTransaction.php` participates directly in the behavior owned by this folder.
- Control returns to the caller or the next local slice once this folder finishes its job.

## Main units in this folder

- `RunTransaction.php` participates directly in the behavior owned by this folder.
- `Transaction.php` participates directly in the behavior owned by this folder.
- `TransactionScope.php` participates directly in the behavior owned by this folder.

## Writes and side effects

- This slice can begin, commit, roll back, or nest transactions through savepoints.

## Failure shape

- Broken nesting, rollback defects, or transaction-state drift surface through this slice.

## Debug first

- Start with `RunTransaction.php` participates directly in the behavior owned by this folder.
- Start with `Transaction.php` participates directly in the behavior owned by this folder.
- Start with `TransactionScope.php` participates directly in the behavior owned by this folder.

