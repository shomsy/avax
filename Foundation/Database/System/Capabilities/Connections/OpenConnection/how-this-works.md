---
title: openconnection-how-this-works
owner: foundation-database-connections
last_reviewed: 2026-04-22
classification: internal
---

# OpenConnection How This Works

## What this folder is

This folder owns the direct physical connection opening path.

## Real commands or triggers that reach this folder

- `ReadConnection::connection()` when a non-pooled connection is requested
- `ConnectionPool::acquire()` when the pool must spawn a fresh physical connection

## Exact upstream handoffs

- `ReadConnection/ReadConnection.php` delegates one config payload into this folder
- `Pools/ConnectionPool.php` delegates pool growth into this folder

## Main decision point

- `OpenConnection.php` decides how telemetry wraps the physical open
- `BuildPhysicalConnection.php` decides how config becomes PDO + `PdoConnection`

## Writes and side effects

- Opens real PDO connections
- Dispatches connection-opened or connection-failed telemetry events

## Debug first

- `OpenConnection.php`
- `BuildPhysicalConnection.php`
