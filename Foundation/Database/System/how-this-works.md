---
title: system-how-this-works
owner: foundation-database
last_reviewed: 2026-04-22
classification: internal
---

# System How This Works

## What this folder is

This folder contains the canonical Database system. It is the only Database core surface that application code should
build against.

## Real commands or triggers that reach this folder

- `Database::configuration()->ready()`
- Container resolution through `DatabaseServiceProvider`
- Any runtime call that enters `Database`, `DatabaseInterface`, or capability accessors

## Exact upstream handoffs

- `Configuration/DatabaseBuilder.php` constructs the root
- `Database.php` hands work to capability owners
- `Capabilities/*` execute the actual runtime logic

## Main decision point

- `DatabaseBuilder::ready()` determines the concrete runtime instances exposed through `DatabaseInterface`

## Writes and side effects

- None at load time
- Runtime side effects happen only when capabilities are invoked

## Failure shape

- Mis-wired builders fail here before the request reaches lower-level capabilities

## Debug first

- Check `DatabaseInterface` for expected public contract
- Check `DatabaseBuilder` for assembly bugs
