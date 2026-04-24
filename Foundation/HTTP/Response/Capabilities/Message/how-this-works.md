---
title: Message-how-this-works
owner: foundation-http
last_reviewed: 2026-04-24
classification: internal
---

# Message How This Works

## What this folder is

This folder owns immutable PSR-7 response state: status code, reason phrase, protocol version, headers, and body.

## Real commands or triggers that reach this folder

- any builder that needs a finished `ResponseInterface`

## Exact upstream handoffs

- `Flows/BuildResponse/BuildResponse.php` -> `ResponseMessage.php`

## The simplest story

- a build flow resolves body and headers
- `ResponseMessage` validates status and protocol invariants
- the flow gets a stable immutable response object back

## Debug first

- start here when cloning, `withStatus()`, or `withHeader()` behavior is wrong

## What to remember

- message state owns no runtime side effects
