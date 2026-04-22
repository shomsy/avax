---
title: capabilities-how-this-works
owner: foundation-database
last_reviewed: 2026-04-22
classification: internal
---

# Capabilities How This Works

## What this folder is

This folder splits the Database system into explicit ownership slices. Each subfolder owns one runtime concern and
exposes a concrete surface instead of a generic module recipe.

## Real commands or triggers that reach this folder

- Any `DatabaseInterface` capability accessor call
- Internal runtime delegation from `DatabaseBuilder`

## Exact upstream handoffs

- `Database.php` routes calls into these capability owners
- Each capability owns its internal runtime types, helper DTOs, exceptions, and DSL primitives

## Main decision point

- The caller chooses the capability explicitly: query builder, migrations, connections, transactions, or telemetry

## Writes and side effects

- All Database runtime effects originate from these capability folders

## Debug first

- Go directly to the capability that matches the broken behavior rather than debugging the whole component
