---
title: transactions-how-this-works
owner: foundation-database-transactions
last_reviewed: 2026-04-22
classification: internal
---

# Transactions How This Works

## What this folder is

This folder owns transaction boundaries, nested transaction handling, savepoints, transaction scopes, and the deferred
identity-map flush mechanism.

## Real commands or triggers that reach this folder

- `DatabaseInterface::transactions()`
- `QueryBuilder::transaction()`
- Deferred-write flows that use `IdentityMap`
- Migration runner transaction wrapping

## Exact upstream handoffs

- `Transactions.php` creates transaction managers per connection name
- `Transaction.php` manages begin, commit, rollback, and savepoints
- `Identity/IdentityMap.php` buffers deferred writes until commit-time execution

## Main decision point

- `Transactions::on()` binds the transaction manager to the requested connection

## Writes and side effects

- Opens and closes database transactions
- Creates and releases savepoints
- Flushes buffered writes when the transaction succeeds

## Failure shape

- Transaction lifecycle defects throw `TransactionException`

## Debug first

- `Transaction.php`
- `Identity/IdentityMap.php`
