---
title: Escape-how-this-works
owner: security
last_reviewed: 2026-04-30
classification: internal
---

# Escape How This Works

## What this folder is

This folder owns output escaping for HTML, attributes, and JSON contexts.

## Real commands or triggers that reach this folder

Application code calls `Security::escape()`, `Security::escapeAttribute()`, or `Security::safeJson()`.

## Exact upstream handoffs

`Security` delegates directly to `OutputEscaper`; templates and response builders should use the public surface instead
of duplicating escaping rules.

## Failure behavior

JSON encoding uses `JSON_THROW_ON_ERROR` so invalid values fail explicitly.
