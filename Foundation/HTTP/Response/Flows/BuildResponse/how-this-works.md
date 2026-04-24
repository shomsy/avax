---
title: BuildResponse-how-this-works
owner: foundation-http
last_reviewed: 2026-04-24
classification: internal
---

# BuildResponse How This Works

## What this folder is

This folder owns explicit builders for every supported response shape.

## Real commands or triggers that reach this folder

- `Response::text()`
- `Response::json()`
- `ResponseFactory::create*Response()`

## Exact upstream handoffs

- `Response.php` -> `BuildTextResponse.php`, `BuildJsonResponse.php`, `BuildRedirectResponse.php`
- `ResponseFactory.php` -> `BuildResponse.php` and specialized builders

## The simplest story

- a caller picks a response shape
- the matching builder encodes payload and headers
- `BuildResponse.php` assembles the final immutable message

## Debug first

- start with the specific builder that matches the wrong output shape

## What to remember

- builders are intentionally boring and single-purpose
