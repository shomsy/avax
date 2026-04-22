---
title: runtransaction-how-this-works
owner: foundation-database-transactions
last_reviewed: 2026-04-22
classification: internal
---

# RunTransaction How This Works

## What this folder is

This folder owns the actual transaction execution runtime, including nesting and savepoint behavior.

## Real commands or triggers that reach this folder

- `Transactions::run()`
- `Transactions::on()`
- `QueryOrchestrator::transaction()`

## Exact upstream handoffs

- `Transactions.php` routes callback execution into this folder
- `OnConnection.php` returns `Transaction.php` from this folder

## Main decision point

- `Transaction.php` decides begin, commit, rollback, savepoint, and scoped transaction behavior

## Writes and side effects

- Opens and closes PDO transactions
- Creates and rolls back savepoints

## Debug first

- `RunTransaction.php`
- `Transaction.php`
- `TransactionScope.php`
