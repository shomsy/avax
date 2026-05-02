---
title: System-how-this-works
owner: performance
last_reviewed: 2026-04-30
classification: internal
---

# Performance System How This Works

## What this folder is

This system owns performance primitives for query caching, route caching, config caching, and lazy loading.

## Real commands or triggers that reach this folder

Application code calls `Performance::queryCache()`, `Performance::routes()`, `Performance::config()`, or
`Performance::lazy()`.

## Exact upstream handoffs

`Performance` creates focused capability objects. Each cache capability owns one persistence shape and `LazyValue` owns
deferred resolution.

## Failure behavior

Missing cache files read as empty arrays. Lazy values resolve once and then return the cached value.
