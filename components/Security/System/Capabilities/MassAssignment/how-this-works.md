---
title: MassAssignment-how-this-works
owner: security
last_reviewed: 2026-04-30
classification: internal
---

# MassAssignment How This Works

## What this folder is

This folder owns allow-list filtering for user supplied arrays before data is assigned into domain models.

## Real commands or triggers that reach this folder

Application code calls `Security::fillable()` with input data and the allowed keys.

## Exact upstream handoffs

`Security` delegates to `MassAssignmentGuard::onlyFillable()`.

## Failure behavior

Unknown input keys throw `InvalidArgumentException` so assignment bypasses cannot be silently ignored.
