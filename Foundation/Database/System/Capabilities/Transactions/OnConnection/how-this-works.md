---
title: onconnection-how-this-works
owner: foundation-database-transactions
last_reviewed: 2026-04-22
classification: internal
---

# OnConnection How This Works

## What this folder is

This folder owns resolving a transaction manager for one concrete database connection.

## Real commands or triggers that reach this folder

- `Transactions::on()`
- `Querying/CreateBuilder.php`

## Exact upstream handoffs

- `Transactions.php` delegates connection-specific transaction resolution into this folder

## Main decision point

- `OnConnection.php` decides which connection-backed `Transaction` manager is returned

## Writes and side effects

- Resolves one database connection

## Debug first

- `OnConnection.php`
