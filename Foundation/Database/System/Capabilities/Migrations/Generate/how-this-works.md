---
title: Generate-how-this-works
owner: foundation-database-migrations
last_reviewed: 2026-04-22
classification: internal
---

# Generate How This Works

## What this folder is

This folder owns one focused runtime sub-slice of the migrations capability.

## Real commands or triggers that reach this folder

- Migrations.php or Console adapters delegate directly into this folder

## Exact upstream handoffs

- The parent migrations capability constructs and invokes the runtime types stored here

## Main decision point

- The concrete classes in this folder decide the local behavior of this migration sub-slice

## Writes and side effects

- This slice may execute SQL, write files, or report migration status depending on the exact class used

## Debug first

- Start with the primary class in this folder referenced by the failing command or stack trace
