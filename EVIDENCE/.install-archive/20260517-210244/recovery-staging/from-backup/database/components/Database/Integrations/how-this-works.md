---
title: integrations-how-this-works
owner: foundation-database-integrations
last_reviewed: 2026-04-22
classification: internal
---

# Integrations How This Works

## What this folder is

This folder owns optional adapters around the Database system so container wiring and CLI behavior stay outside the core
system root.

## Real commands or triggers that reach this folder

- Container bootstrap or CLI command resolution reaches this folder.

## Exact upstream handoffs

- The parent slice hands work into this folder when it needs the behavior owned here.
- The files in this folder do the local work and return control upstream when their responsibility is complete.

## How this folder works

Each adapter translates one external runtime into calls against the already-built Database system without changing the
Database core model.

## The simplest story

- A caller reaches the parent Database surface or the owning parent folder.
- This folder handles the one responsibility it owns.
- The result returns upstream or moves to the next local slice.

## The first important path

- The caller enters through the Database surface or the parent folder.
- `AvaxContainer/` holds the next narrower ownership slice below this folder.
- Control returns to the caller or the next local slice once this folder finishes its job.

## Main units in this folder

- `AvaxContainer/` holds the next narrower ownership slice below this folder.
- `Console/` holds the next narrower ownership slice below this folder.

## Writes and side effects

- This slice can register container bindings or translate CLI commands into Database runtime calls.

## Failure shape

- Assembly or adapter mistakes surface here before the deeper runtime does the wrong thing.

## Debug first

- Start with `AvaxContainer/` holds the next narrower ownership slice below this folder.
- Start with `Console/` holds the next narrower ownership slice below this folder.

