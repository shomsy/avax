---
title: system-capabilities-telemetry-events-how-this-works
owner: foundation-database-telemetry
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / Telemetry / Events How This Works

## What this folder is

This folder owns telemetry event payloads and the event bus.

## Real commands or triggers that reach this folder

- Connection and query runtime paths reach this folder when they publish or consume telemetry.

## Exact upstream handoffs

- The parent slice hands work into this folder when it needs the behavior owned here.
- The files in this folder do the local work and return control upstream when their responsibility is complete.

## How this folder works

Runtime code emits typed events here, the event bus finds listeners, and the dispatch strategy invokes subscribers.

## The simplest story

- A caller reaches the parent Database surface or the owning parent folder.
- This folder handles the one responsibility it owns.
- The result returns upstream or moves to the next local slice.

## The first important path

- The caller enters through the Database surface or the parent folder.
- `ConnectionAcquired.php` participates directly in the behavior owned by this folder.
- Control returns to the caller or the next local slice once this folder finishes its job.

## Main units in this folder

- `ConnectionAcquired.php` participates directly in the behavior owned by this folder.
- `ConnectionFailed.php` participates directly in the behavior owned by this folder.
- `ConnectionOpened.php` participates directly in the behavior owned by this folder.
- `Contracts/` holds the next narrower ownership slice below this folder.
- `Event.php` participates directly in the behavior owned by this folder.
- `EventBus.php` participates directly in the behavior owned by this folder.
- `EventSubscriberInterface.php` participates directly in the behavior owned by this folder.
- `QueryExecuted.php` participates directly in the behavior owned by this folder.
- `Strategy/` holds the next narrower ownership slice below this folder.
- `Subscribers/` holds the next narrower ownership slice below this folder.

## Writes and side effects

- This slice can publish or consume in-process telemetry events and may log them through subscribers.

## Failure shape

- Missing events, duplicated events, bad dispatch, or lost correlation data surface through this slice.

## Debug first

- Start with `ConnectionAcquired.php` participates directly in the behavior owned by this folder.
- Start with `ConnectionFailed.php` participates directly in the behavior owned by this folder.
- Start with `ConnectionOpened.php` participates directly in the behavior owned by this folder.

