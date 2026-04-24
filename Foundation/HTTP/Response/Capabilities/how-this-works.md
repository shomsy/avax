---
title: Capabilities-how-this-works
owner: foundation-http
last_reviewed: 2026-04-24
classification: internal
---

# Capabilities How This Works

## What this folder is

This folder holds reusable response building blocks such as message state, headers, body encoders, streams, redirects,
downloads, cookies, and caching.

## Real commands or triggers that reach this folder

- every response build flow in `Flows/BuildResponse`
- callers that need shared header, stream, cookie, or caching behavior

## Exact upstream handoffs

- `Flows/BuildResponse/BuildResponse.php` -> `Message/ResponseMessage.php`
- `Flows/BuildResponse/BuildJsonResponse.php` -> `Body/Json/EncodeJsonBody.php`
- `Flows/BuildResponse/BuildRedirectResponse.php` -> `Redirects/*`

## The simplest story

- a flow needs one narrow HTTP concern
- it hands that concern to the matching capability owner
- the flow stays small and the rule stays local

## Debug first

- start here when a response shape is right but one shared HTTP detail is wrong

## What to remember

- folders speak one HTTP concern each
- no capability here emits output directly
