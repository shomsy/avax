---
title: Flows-how-this-works
owner: foundation-http
last_reviewed: 2026-04-24
classification: internal
---

# Flows How This Works

## What this folder is

This folder owns the two runtime axes of the component: building responses and emitting responses.

## Real commands or triggers that reach this folder

- public `Response::*` facade calls
- `ResponseFactory` compatibility calls
- runtime code that sends a finished response

## Exact upstream handoffs

- `Response.php` -> `BuildResponse/*`
- `ResponseEmitter.php` -> `EmitResponse/*`

## The simplest story

- build flows create immutable responses
- emit flows send those responses to the PHP runtime

## Debug first

- choose `BuildResponse` when the object is wrong
- choose `EmitResponse` when output is wrong

## What to remember

- creation and side effects stay in separate folders
