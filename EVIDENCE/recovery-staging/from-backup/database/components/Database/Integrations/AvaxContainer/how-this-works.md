---
title: integrations-avaxcontainer-how-this-works
owner: foundation-database-integrations
last_reviewed: 2026-04-22
classification: internal
---

# Integrations / Avax Container How This Works

## What this folder is

This folder owns the Avax Container adapter for the Database system.

## Real commands or triggers that reach this folder

- Container bootstrap or CLI command resolution reaches this folder.

## Exact upstream handoffs

- The parent slice hands work into this folder when it needs the behavior owned here.
- The files in this folder do the local work and return control upstream when their responsibility is complete.

## How this folder works

The service provider resolves config from the container, builds the Database runtime once, and publishes the Database
surface and derived services as container bindings.

## The simplest story

- A caller reaches the parent Database surface or the owning parent folder.
- This folder handles the one responsibility it owns.
- The result returns upstream or moves to the next local slice.

## The first important path

- The caller enters through the Database surface or the parent folder.
- `DatabaseServiceProvider.php` participates directly in the behavior owned by this folder.
- Control returns to the caller or the next local slice once this folder finishes its job.

## Main units in this folder

- `DatabaseServiceProvider.php` participates directly in the behavior owned by this folder.

## Writes and side effects

- This slice can register container bindings or translate CLI commands into Database runtime calls.

## Failure shape

- Assembly or adapter mistakes surface here before the deeper runtime does the wrong thing.

## Debug first

- Start with `DatabaseServiceProvider.php` participates directly in the behavior owned by this folder.

