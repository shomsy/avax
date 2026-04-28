---
title: system-capabilities-telemetry-how-this-works
owner: foundation-database-telemetry
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / Telemetry How This Works

## What this folder is

This folder owns Database telemetry: event publication, subscribers, execution scope, sequence tracking, and telemetry
config.

## Real commands or triggers that reach this folder

- Connection and query runtime paths reach this folder when they publish or consume telemetry.

## Exact upstream handoffs

- The parent slice hands work into this folder when it needs the behavior owned here.
- The files in this folder do the local work and return control upstream when their responsibility is complete.

## How this folder works

Runtime code emits typed events, the event bus routes them to subscribers, and support objects carry correlation and
sequencing metadata through that flow.

## The simplest story

- A caller reaches the parent Database surface or the owning parent folder.
- This folder handles the one responsibility it owns.
- The result returns upstream or moves to the next local slice.

## The first important path

- The caller enters through the Database surface or the parent folder.
- `Config/` holds the next narrower ownership slice below this folder.
- Control returns to the caller or the next local slice once this folder finishes its job.

## Main units in this folder

- `Config/` holds the next narrower ownership slice below this folder.
- `Events/` holds the next narrower ownership slice below this folder.
- `Support/` holds the next narrower ownership slice below this folder.
- `Telemetry.php` participates directly in the behavior owned by this folder.

## Writes and side effects

- This slice can publish or consume in-process telemetry events and may log them through subscribers.

## Failure shape

- Missing events, duplicated events, bad dispatch, or lost correlation data surface through this slice.

## Debug first

- Start with `Config/` holds the next narrower ownership slice below this folder.
- Start with `Events/` holds the next narrower ownership slice below this folder.
- Start with `Support/` holds the next narrower ownership slice below this folder.
