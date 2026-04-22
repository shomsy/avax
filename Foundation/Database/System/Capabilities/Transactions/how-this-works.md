---
title: transactions-how-this-works
owner: foundation-database-transactions
last_reviewed: 2026-04-22
classification: internal
---

# Transactions How This Works

## What this folder is

This folder owns transaction boundaries, nested transaction handling, savepoints, transaction scopes, and connection-
bound transaction execution.

## Real commands or triggers that reach this folder

- `DatabaseInterface::transactions()`
- `QueryBuilder::transaction()`
- Deferred-write flows coordinated by Querying
- Migration runner transaction wrapping

## Exact upstream handoffs

- `Transactions.php` is the public capability owner
- `OnConnection/OnConnection.php` resolves the concrete transaction manager for one connection
- `RunTransaction/Transaction.php` manages begin, commit, rollback, and savepoints

## Main decision point

- `Transactions::on()` binds the transaction manager to the requested connection

## Writes and side effects

- Opens and closes database transactions
- Creates and releases savepoints
- Transaction lifecycle defects throw `TransactionException`

## Debug first

- `OnConnection/OnConnection.php`
- `RunTransaction/Transaction.php`
