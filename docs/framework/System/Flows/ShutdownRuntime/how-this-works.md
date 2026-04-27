---
title: shutdown-runtime-how-this-works
owner: framework-system
last_reviewed: 2026-04-27
classification: internal
---

# Shutdown Runtime How This Works

## What this folder is

This folder owns the last runtime step: mark the runtime as stopped and finish worker lifecycle bookkeeping.

## Real commands or triggers that reach this folder

- `Avax::runtime()->runWorker(...)`

## Exact upstream handoffs

- `framework/System/Capabilities/Runtime/Worker/WorkerLoop.php`
- function: `WorkerLoop::run()`
- `WorkerLoop::run()` -> `ShutdownRuntime::shutdown(...)`

## The simplest story

- the worker loop finishes
- `ShutdownRuntime` stamps the runtime state with a shutdown time
- the worker lifecycle is marked as stopped for diagnostics and tests

## Debug first

- start in `ShutdownRuntime::shutdown(...)` when worker lifecycle never marks shutdown
- start in `RuntimeState::markShutdown(...)` when the framework still looks booted after the worker stops
