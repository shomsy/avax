---
title: Caching-how-this-works
owner: foundation-http
last_reviewed: 2026-04-24
classification: internal
---

# Caching How This Works

## What this folder is

This folder owns cache-control, ETag, Last-Modified, and 304-specific header shaping.

## Real commands or triggers that reach this folder

- `BuildNotModifiedResponse`
- callers that need explicit cache validators on responses

## Exact upstream handoffs

- `Flows/BuildResponse/BuildNotModifiedResponse.php` -> `BuildNotModifiedHeaders.php`

## The simplest story

- a builder asks for cache validators
- this folder formats the cache headers and strips body-specific headers for 304 responses

## Debug first

- start here when cache validators or 304 headers are malformed

## What to remember

- 304 rules are explicit and centralized here
