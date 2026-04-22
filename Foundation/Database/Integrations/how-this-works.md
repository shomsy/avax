---
title: integrations-how-this-works
owner: foundation-database-integrations
last_reviewed: 2026-04-22
classification: internal
---

# Integrations How This Works

## What this folder is

This folder owns optional adapters around the Database system. Nothing here is required for the Database core to
function.

## Real commands or triggers that reach this folder

- Container bootstrap flows
- Console migration commands

## Exact upstream handoffs

- The adapters resolve or delegate into `Foundation/Database/System`

## Main decision point

- Each adapter chooses one narrow integration surface and keeps container or CLI concerns out of the Database core

## Writes and side effects

- Registers services in the container
- Invokes migration commands from CLI entrypoints

## Debug first

- `AvaxContainer/DatabaseServiceProvider.php`
- `Console/*Command.php`
