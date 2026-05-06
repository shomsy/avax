---
title: system-capabilities-telemetry-support-how-this-works
owner: foundation-database-telemetry
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / Telemetry / Support How This Works

## What this folder is

This folder holds support primitives for the owning slice.

## Real commands or triggers that reach this folder

- Connection and query runtime paths reach this folder when they publish or consume telemetry.

## Exact upstream handoffs

- The parent slice hands work into this folder when it needs the behavior owned here.
- The files in this folder do the local work and return control upstream when their responsibility is complete.

## How this folder works

These types carry context or sequencing information that other runtime files need but should not recreate locally.

## The simplest story

- A caller reaches the parent Database surface or the owning parent folder.
- This folder handles the one responsibility it owns.
- The result returns upstream or moves to the next local slice.

## The first important path

- The caller enters through the Database surface or the parent folder.
- `ExecutionScope.php` participates directly in the behavior owned by this folder.
- Control returns to the caller or the next local slice once this folder finishes its job.

## Main units in this folder

- `ExecutionScope.php` participates directly in the behavior owned by this folder.
- `SequenceTracker.php` participates directly in the behavior owned by this folder.

## Writes and side effects

- This slice can publish or consume in-process telemetry events and may log them through subscribers.

## Failure shape

- Missing events, duplicated events, bad dispatch, or lost correlation data surface through this slice.

## Debug first

- Start with `ExecutionScope.php` participates directly in the behavior owned by this folder.
- Start with `SequenceTracker.php` participates directly in the behavior owned by this folder.

