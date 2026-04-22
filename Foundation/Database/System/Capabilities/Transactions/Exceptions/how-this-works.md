---
title: Exceptions-how-this-works
owner: foundation-database-transactions
last_reviewed: 2026-04-22
classification: internal
---

# Exceptions How This Works

## What this folder is

This folder owns support contracts and exception types for transaction coordination.

## Real commands or triggers that reach this folder

- Transaction runtime code resolves these types during begin, commit, rollback, and savepoint flows

## Exact upstream handoffs

- Transactions/* relies on these support files for stable boundaries

## Main decision point

- These types define the contract and failure shape of transaction coordination

## Writes and side effects

- No direct side effects

## Debug first

- Start with the contract or exception referenced by the failing transaction path
