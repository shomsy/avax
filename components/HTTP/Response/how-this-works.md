---
title: Response-how-this-works
owner: foundation-http
last_reviewed: 2026-04-24
classification: internal
---

# Response How This Works

## What this folder is

This folder owns HTTP response creation, shared response capabilities, and runtime emission.

## Real commands or triggers that reach this folder

- controllers and middleware that need a PSR-7 response
- `Response::*` facade calls
- `ResponseFactory` compatibility calls
- runtime code that emits a built response

## Exact upstream handoffs

- `Foundation/HTTP/Dispatcher/ControllerDispatcher.php`
- function: `dispatchCallable(...)`
- `ControllerDispatcher` -> `Response::text(...)`
- `Foundation/Helpers/helpers.php`
- function: `response(...)`
- `response(...)` -> `ResponseFactory::createResponse(...)`

## The simplest story

- a caller chooses a response shape
- the matching build flow assembles immutable message state and a body stream
- emission stays separate and runs through `ResponseEmitter`

## Debug first

- start in `Response.php` or `ResponseFactory.php` when the wrong response shape is returned
- start in `Flows/EmitResponse/*` when status, headers, or body are emitted incorrectly

## What to remember

- `BuildResponse` owns creation
- `EmitResponse` owns side effects
- rendering and business JSON envelopes do not belong here
