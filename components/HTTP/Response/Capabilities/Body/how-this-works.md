---
title: Body-how-this-works
owner: foundation-http
last_reviewed: 2026-04-24
classification: internal
---

# Body How This Works

## What this folder is

This folder owns body normalization and specialized encoders for JSON, XML, and RFC 7807 problem-details payloads.

## Real commands or triggers that reach this folder

- build flows that need to turn input data into a writable response body stream

## Exact upstream handoffs

- `Flows/BuildResponse/BuildResponse.php` -> `NormalizeResponseBody.php`
- `Flows/BuildResponse/BuildJsonResponse.php` -> `Json/EncodeJsonBody.php`
- `Flows/BuildResponse/BuildProblemResponse.php` -> `Problem/*`

## The simplest story

- builders hand raw content to body normalizers or encoders
- the body layer resolves a stream-ready payload
- the message layer stores that payload without knowing encoding rules

## Debug first

- start here when payload encoding or body normalization is wrong

## What to remember

- body encoding lives here, not in the message object
