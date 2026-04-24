---
title: Cookies-how-this-works
owner: foundation-http
last_reviewed: 2026-04-24
classification: internal
---

# Cookies How This Works

## What this folder is

This folder owns response cookie value objects and `Set-Cookie` header assembly.

## Real commands or triggers that reach this folder

- callers that need to attach or expire cookies on a response

## Exact upstream handoffs

- app code -> `SetCookieHeader.php`
- app code -> `ExpireCookieHeader.php`

## The simplest story

- a caller describes a cookie
- this folder renders the cookie into a valid header line
- the response gets an appended `Set-Cookie` header

## Debug first

- start here when cookie flags or expiration dates are wrong

## What to remember

- cookie formatting belongs here, not in generic header code
