---
title: foundation-how-this-works
owner: foundation-database-foundation
last_reviewed: 2026-04-22
classification: internal
---

# Foundation How This Works

## What this folder is

This folder is intentionally small. It contains shared primitives that are truly cross-capability, not a generic
warehouse for unrelated helpers.

## Real commands or triggers that reach this folder

- Any Database runtime path that throws a shared Database exception

## Exact upstream handoffs

- Capability-specific exceptions extend the base contracts defined here

## Main decision point

- Shared exception inheritance starts here and becomes more specific in each capability

## Writes and side effects

- None

## Failure shape

- Base Database exception semantics are defined here

## Debug first

- `Exceptions/DatabaseException.php`
- `Exceptions/DatabaseThrowable.php`
