---
title: Pool-how-this-works
owner: foundation-database-connections
last_reviewed: 2026-04-22
classification: internal
---

# Pool How This Works

## What this folder is

This folder owns pooled connection acquisition, release, state tracking, and borrowed-connection wrappers.

## Real commands or triggers that reach this folder

- ConnectionManager selects pooled authority for a configured connection

## Exact upstream handoffs

- Connections/ConnectionManager.php delegates pool-backed connections here

## Main decision point

- ConnectionPool.php decides whether to reuse, prune, or create a physical connection

## Writes and side effects

- Borrows and releases physical database connections

## Debug first

- Start with ConnectionPool.php and BorrowedConnection.php
