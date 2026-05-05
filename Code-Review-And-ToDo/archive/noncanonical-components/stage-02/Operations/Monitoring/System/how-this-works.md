---
title: System-how-this-works
owner: operations-monitoring
last_reviewed: 2026-04-30
classification: internal
---

# Monitoring System How This Works

## What this folder is

This system owns runtime observability primitives: health reports, counters, timings, Sentry forwarding, and dashboard
data.

## Real commands or triggers that reach this folder

The `/health` route calls `Monitoring::health()`. Application code can call `Monitoring::metrics()` and
`Monitoring::report()` from runtime paths.

## Exact upstream handoffs

`Monitoring` is the public surface. It delegates health status to `HealthEndpoint`, metrics storage to
`MetricsRegistry`, and exception forwarding to `SentryReporter`.

## Failure behavior

Health checks degrade instead of throwing when a check reports down. Sentry forwarding is best-effort and no-ops when
Sentry is not installed or initialized.
