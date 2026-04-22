---
title: telemetry-how-this-works
owner: foundation-database-telemetry
last_reviewed: 2026-04-22
classification: internal
---

# Telemetry How This Works

## What this folder is

This folder owns database event emission, event transport, execution scope correlation, sequence tracking, and logging
subscribers.

## Real commands or triggers that reach this folder

- Any connection acquisition or connection failure
- Any query execution handled by the PDO executor
- Builder assembly that installs a logger subscriber

## Exact upstream handoffs

- `System/Configuration/DatabaseBuilder.php` creates the event bus and optional logger subscriber
- `QueryBuilder/Execution/PDOExecutor.php` dispatches query events
- `Connections/*` dispatch connection events

## Main decision point

- `Telemetry.php` exposes the capability surface, while `Events/EventBus.php` fans out events to subscribers

## Writes and side effects

- Emits in-process events
- Writes logs when a logger subscriber is attached

## Failure shape

- Telemetry is designed to observe runtime behavior; it should not own business failures

## Debug first

- `Events/EventBus.php`
- `Events/Subscribers/DatabaseLoggerSubscriber.php`
- `Support/ExecutionScope.php`
