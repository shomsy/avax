---
title: Audit-how-this-works
owner: security
last_reviewed: 2026-04-30
classification: internal
---

# Audit How This Works

## What this folder is

This folder owns lightweight security audit events for local runtime decisions.

## Real commands or triggers that reach this folder

`Security::audit()` records explicit events, and CSRF verification records accepted or rejected outcomes.

## Exact upstream handoffs

`Security` owns the singleton audit log and delegates event storage to `SecurityAuditLog`.

## Failure behavior

Sensitive keys such as `password`, `token`, and `secret` are redacted before events are retained.
