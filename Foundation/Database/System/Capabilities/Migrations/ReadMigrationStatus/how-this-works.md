---
title: readmigrationstatus-how-this-works
owner: foundation-database-migrations
last_reviewed: 2026-04-22
classification: internal
---

# ReadMigrationStatus How This Works

## What this folder is

This folder owns computing migration run/pending state and checksum integrity snapshots.

## Real commands or triggers that reach this folder

- `Migrations::status()`
- `Integrations/Console/MigrateStatusCommand.php`

## Exact upstream handoffs

- `Migrations.php` returns `ReadMigrationStatus.php` from this folder

## Main decision point

- `ReadMigrationStatus.php` decides each migration row's status and integrity label

## Writes and side effects

- Reads migration repository rows
- Reads migration files from disk

## Debug first

- `ReadMigrationStatus.php`
