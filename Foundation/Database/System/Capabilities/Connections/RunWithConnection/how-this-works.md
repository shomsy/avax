---
title: runwithconnection-how-this-works
owner: foundation-database-connections
last_reviewed: 2026-04-22
classification: internal
---

# RunWithConnection How This Works

## What this folder is

This folder owns the safe callback execution path for resolved connections, including borrowed pool instances.

## Real commands or triggers that reach this folder

- `Connections::pool()`

## Exact upstream handoffs

- `Connections.php` delegates pooled callback execution into this folder

## Main decision point

- `RunWithConnection.php` decides whether a callback receives a direct connection or a borrowed pooled instance

## Writes and side effects

- Acquires pooled connections
- Releases pooled connections in `finally`

## Debug first

- `RunWithConnection.php`
