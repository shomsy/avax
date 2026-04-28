---
title: worker-how-this-works
owner: framework-system
last_reviewed: 2026-04-27
classification: internal
---

# Worker How This Works

## What this folder is

This folder owns the generic worker contracts that isolate repeated-request runtimes from the core framework.

## Real commands or triggers that reach this folder

- `Avax::runtime()->runWorker(...)`

## Exact upstream handoffs

- `framework/System/PublicSurface/Runtime/RuntimeKernel.php`
- function: `RuntimeKernel::runWorker(...)`
- `RuntimeKernel::runWorker(...)` -> `Runtime::runWorker(...)` -> `WorkerLoop::run()`

## The simplest story

- `WorkerRuntimeInterface` receives and sends worker messages.
- `WorkerLoop` drives repeated request execution.
- `WorkerLifecycle` records what happened so shutdown and tests can verify behavior.

## Debug first

- start in `WorkerLoop::run()` when repeated requests stop too early or never stop
- start in `HandleWorkerRequest::handle(...)` when request scope or reset behavior leaks across requests
