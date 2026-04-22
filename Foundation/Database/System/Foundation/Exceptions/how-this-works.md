---
title: Exceptions-how-this-works
owner: foundation-database-foundation
last_reviewed: 2026-04-22
classification: internal
---

# Exceptions How This Works

## What this folder is

This folder owns the shared exception base contracts for the Database system.

## Real commands or triggers that reach this folder

- Capability-specific exceptions extend or implement these types

## Exact upstream handoffs

- All Database capabilities depend on these shared exception primitives

## Main decision point

- These files define the base failure contract for the component

## Writes and side effects

- No direct side effects

## Debug first

- Start with DatabaseException.php
