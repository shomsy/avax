---
title: Contracts-how-this-works
owner: foundation-database-telemetry
last_reviewed: 2026-04-22
classification: internal
---

# Contracts How This Works

## What this folder is

This folder owns support types for Database telemetry delivery, scope, and logging.

## Real commands or triggers that reach this folder

- Telemetry runtime delegates to this folder during event dispatch and correlation

## Exact upstream handoffs

- Telemetry/Events and DatabaseBuilder depend on these support types

## Main decision point

- These files define how telemetry is configured, correlated, and delivered

## Writes and side effects

- Logger subscribers may emit logs; other support types are side-effect free

## Debug first

- Start with the support type directly involved in the failing telemetry path
