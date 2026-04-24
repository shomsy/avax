---
title: EmitResponse-how-this-works
owner: foundation-http
last_reviewed: 2026-04-24
classification: internal
---

# EmitResponse How This Works

## What this folder is

This folder owns the side effects of sending a built response to the PHP runtime.

## Real commands or triggers that reach this folder

- `Response::emit()`
- `ResponseEmitter::emit()`

## Exact upstream handoffs

- `ResponseEmitter.php` -> `EmitResponse.php`
- `EmitResponse.php` -> `EmitResponseStatus.php`, `EmitResponseHeaders.php`, `EmitResponseBody.php`

## The simplest story

- the status code is sent first
- headers are sent next
- the body stream is rewound if needed and copied out

## Debug first

- start here when emitted output differs from the response object state

## What to remember

- only this flow owns runtime output side effects
