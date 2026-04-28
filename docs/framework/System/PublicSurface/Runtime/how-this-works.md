---
title: runtime-how-this-works
owner: framework-system
last_reviewed: 2026-04-27
classification: internal
---

# Runtime How This Works

## What this folder is

This folder is the public worker/runtime doorway for the framework lifecycle after boot.

## Real commands or triggers that reach this folder

- `Avax::runtime()->runWorker(...)`

## Exact upstream handoffs

- `framework/System/PublicSurface/Avax.php`
- function: `Avax::runtime()`
- `Avax::runtime()` -> `RuntimeKernel::runWorker(...)`

## The simplest story

- `Avax` exposes the stable runtime API.
- `RuntimeKernel` delegates worker execution to `Runtime::runWorker(...)`.
- `WorkerLoop` handles requests one by one and shuts the runtime down cleanly.

## The first important path

When you call:

```php
Avax::boot(...)->runtime()->runWorker($workerRuntime);
```

the important path is:

```mermaid
sequenceDiagram
    autonumber
    participant Avax as Avax::runtime
    participant Kernel as RuntimeKernel::runWorker
    participant Runtime as Runtime::runWorker
    participant Loop as WorkerLoop::run
    Avax ->> Kernel: expose runtime public API
    Kernel ->> Runtime: delegate worker execution
    Runtime ->> Loop: create loop for worker runtime
    Loop -->> Runtime: processed worker lifecycle
```

## Debug first

- start in `RuntimeKernel::runWorker(...)` when the public runtime API looks wrong
- start in `WorkerLoop::run()` when state leaks or repeated request handling fails
