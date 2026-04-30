---
title: RateLimiter-how-this-works
owner: resilience
last_reviewed: 2026-04-30
classification: internal
---

# RateLimiter How This Works

## What this folder is

This folder owns request and action throttling. It gives Avax one sliding-window limiter that can use Redis when present
and falls back to an in-process array driver for tests and local development.

## Real commands or triggers that reach this folder

HTTP middleware calls `RateLimitMiddleware::handle()`. Application code can call `RateLimit::attempt()` directly when a
feature needs throttling outside HTTP.

## Exact upstream handoffs

The public handoff is `RateLimitMiddleware`, `RateLimit`, or `RateLimiter`. Those units delegate counting to
`RedisRateLimiter`; callers should not manipulate Redis keys directly.

## Failure behavior

When Redis is unavailable, the limiter falls back to memory instead of failing the request path. Exhausted limits return
`429` with `X-RateLimit-*` and `Retry-After` headers.
