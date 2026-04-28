# Timeline Policy

This file defines the minimum timestamp and estimation discipline for
management, evidence, planning, and review records.

## Why

Without timestamps, queue age, execution speed, blocked time, and estimation
accuracy cannot be measured honestly.

## Canonical Timestamp Format

Use this format for all new management and evidence records:

- `YYYY-MM-DD HH:MM TZ`

Examples:

- `2026-03-27 21:14 CET`
- `2026-03-27 20:14 UTC`
- `2026-03-27 20:14 UTC+01:00`

Rules:

- include both date and exact time
- include timezone
- keep the original creation timestamp forever
- update the `updated_at` or equivalent field every time the record changes
- when work is closed, record `completed_at`, `closed_at`, or `decision_at`
- legacy entries may remain date-only until touched; once touched, upgrade them
  to the canonical format when the real time is known

## Minimum Timeline Fields By Record Type

### Queue Items

For TODO, BUG, and IDEA records, include at least:

- `created_at`
- `updated_at`
- `status`
- `estimate`

When applicable also include:

- `started_at`
- `blocked_at`
- `completed_at`
- `actual`

## Decisions

For decisions and ADR-lite records, include at least:

- `recorded_at`
- `decision_at` when the outcome is no longer only proposed
- `updated_at`

## Evidence

For test reports, release snapshots, and changelog-style closure records,
include at least:

- `recorded_at` or `executed_at`
- `scope`
- `result` or `decision`

## Review Notes

For review findings and review documents, include at least:

- `captured_at`
- `updated_at`
- `severity`

## Estimation Rule

Estimation is part of the management contract, not a nice-to-have.

For work items worth tracking, record:

- `estimate`: the expected effort or duration
- `actual`: the real effort or duration once known

The goal is not fake precision.
The goal is to compare expectation with reality over time.

## Ordering Rule

- active queues still keep newest items first
- timeline fields make newest, oldest, and stalled work measurable
- do not rewrite history to hide churn or slippage
