---
title: Json-how-this-works
owner: foundation-http
last_reviewed: 2026-04-24
classification: internal
---

# Json How This Works

## What this folder is

This folder owns JSON serialization for response payloads.

## Real commands or triggers that reach this folder

- `BuildJsonResponse`
- `BuildProblemResponse`

## Exact upstream handoffs

- `Flows/BuildResponse/BuildJsonResponse.php` -> `EncodeJsonBody.php`

## The simplest story

- input data is JSON encoded with strict error handling
- the caller receives a string payload or a clear failure

## Debug first

- start here when JSON encoding fails or escapes incorrectly

## What to remember

- this folder does not decide headers or status codes
