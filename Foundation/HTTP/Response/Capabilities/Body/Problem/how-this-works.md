---
title: Problem-how-this-works
owner: foundation-http
last_reviewed: 2026-04-24
classification: internal
---

# Problem How This Works

## What this folder is

This folder owns RFC 7807 problem-details value construction and encoding.

## Real commands or triggers that reach this folder

- `BuildProblemResponse`

## Exact upstream handoffs

- `Flows/BuildResponse/BuildProblemResponse.php` -> `ProblemDetails.php`
- `BuildProblemResponse.php` -> `EncodeProblemDetails.php`

## The simplest story

- a builder describes an HTTP problem
- this folder turns it into a structured payload
- the response stays transport-level, not business-envelope shaped

## Debug first

- start here when problem responses lose fields or extension members

## What to remember

- problem-details are a shared HTTP concern, not an app-specific envelope
