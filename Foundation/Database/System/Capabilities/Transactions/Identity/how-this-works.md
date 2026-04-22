---
title: Identity-how-this-works
owner: foundation-database-transactions
last_reviewed: 2026-04-22
classification: internal
---

# Identity How This Works

## What this folder is

This folder owns deferred-write buffering through the transaction identity map.

## Real commands or triggers that reach this folder

- QueryBuilderRuntime attaches IdentityMap when deferred execution is enabled

## Exact upstream handoffs

- Transactions/Transaction.php provides the transaction boundary and this folder flushes queued writes inside it

## Main decision point

- IdentityMap.php decides when queued writes are scheduled and executed

## Writes and side effects

- Flushes deferred SQL statements inside a transaction boundary

## Debug first

- Start with IdentityMap.php
