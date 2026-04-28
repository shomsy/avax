---
title: system-foundation-exceptions-how-this-works
owner: foundation-database-database
last_reviewed: 2026-04-22
classification: internal
---

# System / Foundation / Exceptions How This Works

## What this folder is

This folder owns Database-wide exception primitives that can be shared across capabilities when the concern is broader
than one single lane.

## Real commands or triggers that reach this folder

- Application bootstrap and Database runtime entry reach this folder.

## Exact upstream handoffs

- The parent slice hands work into this folder when it needs the behavior owned here.
- The files in this folder do the local work and return control upstream when their responsibility is complete.

## How this folder works

Specific capabilities still own their local failure language, but these base types give the component one small shared
throwable surface where that surface is genuinely needed.

## The simplest story

- A caller reaches the parent Database surface or the owning parent folder.
- This folder handles the one responsibility it owns.
- The result returns upstream or moves to the next local slice.

## The first important path

- The caller enters through the Database surface or the parent folder.
- `DatabaseException.php` participates directly in the behavior owned by this folder.
- Control returns to the caller or the next local slice once this folder finishes its job.

## Main units in this folder

- `DatabaseException.php` participates directly in the behavior owned by this folder.
- `DatabaseThrowable.php` participates directly in the behavior owned by this folder.

## Writes and side effects

- This slice shapes or assembles runtime behavior; lower folders perform the concrete side effects when needed.

## Failure shape

- Assembly or adapter mistakes surface here before the deeper runtime does the wrong thing.

## Debug first

- Start with `DatabaseException.php` participates directly in the behavior owned by this folder.
- Start with `DatabaseThrowable.php` participates directly in the behavior owned by this folder.
