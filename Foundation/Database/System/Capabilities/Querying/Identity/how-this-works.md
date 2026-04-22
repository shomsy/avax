---
title: identity-how-this-works
owner: foundation-database-querying
last_reviewed: 2026-04-22
classification: internal
---

# Identity How This Works

## What this folder is

This folder owns deferred-write buffering and flush coordination for Querying.

## Real commands or triggers that reach this folder

- `Querying/CreateBuilder/CreateBuilder.php` attaches `IdentityMap`
- `QueryBuilder::deferred()` reuses or overrides the active identity map

## Exact upstream handoffs

- `Execution/QueryOrchestrator.php` schedules and flushes deferred writes through this folder
- `Transactions/RunTransaction/Transaction.php` provides the transaction boundary used during flush

## Main decision point

- IdentityMap.php decides when queued writes are scheduled and executed

## Writes and side effects

- Flushes deferred SQL statements inside a transaction boundary

## Debug first

- Start with IdentityMap.php
