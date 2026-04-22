---
title: Events-how-this-works
owner: foundation-database-telemetry
last_reviewed: 2026-04-22
classification: internal
---

# Events How This Works

## What this folder is

This folder owns telemetry event types, the event bus, and subscriber wiring for the Database system.

## Real commands or triggers that reach this folder

- Connections and QueryBuilder execution dispatch events here

## Exact upstream handoffs

- Telemetry.php exposes this runtime and lower-level executors publish into it

## Main decision point

- EventBus.php decides how subscribers receive emitted events

## Writes and side effects

- Dispatches in-process telemetry notifications

## Debug first

- Start with EventBus.php and the specific event class involved
