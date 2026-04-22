---
title: loadmigrations-how-this-works
owner: foundation-database-migrations
last_reviewed: 2026-04-22
classification: internal
---

# LoadMigrations How This Works

## What this folder is

This folder owns discovering migration files, loading them, and calculating their checksums.

## Real commands or triggers that reach this folder

- `Migrations::loader()`
- migration run, rollback, and status adapters

## Exact upstream handoffs

- `Migrations.php` returns `MigrationLoader.php` from this folder

## Main decision point

- `MigrationLoader.php` decides which PHP files are valid migrations and how they are keyed

## Writes and side effects

- Reads migration files from disk

## Debug first

- `MigrationLoader.php`
