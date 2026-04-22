---
title: avaxcontainer-how-this-works
owner: foundation-database-integrations
last_reviewed: 2026-04-22
classification: internal
---

# AvaxContainer How This Works

## What this folder is

This folder contains the optional Avax Container adapter for the Database system.

## Real commands or triggers that reach this folder

- Container provider boot that includes `DatabaseServiceProvider`

## Exact upstream handoffs

- The provider reads container config
- The provider builds `DatabaseInterface`
- The provider exposes derived capability services and migration runtime services

## Main decision point

- `DatabaseServiceProvider::buildDatabase()` decides how container config becomes a built Database runtime

## Writes and side effects

- Registers container singletons for Database root, capabilities, and migration runtime services

## Debug first

- `DatabaseServiceProvider.php`
