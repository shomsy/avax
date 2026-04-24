---
title: Headers-how-this-works
owner: foundation-http
last_reviewed: 2026-04-24
classification: internal
---

# Headers How This Works

## What this folder is

This folder owns case-insensitive header storage plus header validation, normalization, append, replace, read, and
remove behavior.

## Real commands or triggers that reach this folder

- response creation paths that add or mutate headers
- response message methods such as `withHeader()` and `withAddedHeader()`

## Exact upstream handoffs

- `Capabilities/Message/ResponseMessage.php` -> `ResponseHeaders.php`
- `Flows/BuildResponse/*` -> header validators and normalizers through `ResponseMessage`

## The simplest story

- header names and values are validated once
- storage stays case-insensitive
- callers read original header names back through `getHeaders()`

## Debug first

- start here when a header disappears, duplicates incorrectly, or accepts invalid data

## What to remember

- header knowledge is centralized here
