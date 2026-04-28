---
title: handle-worker-request-how-this-works
owner: framework-system
last_reviewed: 2026-04-27
classification: internal
---

# Handle Worker Request How This Works

## What this folder is

This folder owns one worker request from the moment the worker loop receives it until request-local state is reset.

## Real commands or triggers that reach this folder

- `Avax::runtime()->runWorker(...)`

## Exact upstream handoffs

- `framework/System/Capabilities/Runtime/Worker/WorkerLoop.php`
- function: `WorkerLoop::run()`
- `WorkerLoop::run()` -> `HandleWorkerRequest::handle(...)`

## The simplest story

- open a worker request scope
- run the existing HTTP handler inside that scope
- close the scope and reset all request-local state before the next worker request

## The first important path

```mermaid
sequenceDiagram
    autonumber
    participant Loop as WorkerLoop::run
    participant Flow as HandleWorkerRequest::handle
    participant Http as HandleIncomingHttp::handleInCurrentScope
    participant Reset as ResetApplicationState::reset
    Loop ->> Flow: WorkerRequest
    Flow ->> Http: execute current request
    Http -->> Flow: RuntimeResponse
    Flow ->> Reset: clear request-local state
    Reset -->> Loop: ready for next request
```

## Debug first

- start in `HandleWorkerRequest::handle(...)` when two worker requests can see each other's state
- start in `ResetApplicationState::reset()` when request-local state survives past one request
