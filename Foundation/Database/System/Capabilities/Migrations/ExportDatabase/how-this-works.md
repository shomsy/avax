---
title: exportdatabase-how-this-works
owner: foundation-database-migrations
last_reviewed: 2026-04-22
classification: internal
---

# ExportDatabase How This Works

## What this folder is

This folder owns exporting schema and data snapshots from the active database.

## Real commands or triggers that reach this folder

- `Migrations::exporter()`
- `Integrations/Console/ExportCommand.php`

## Exact upstream handoffs

- `Migrations.php` returns `DatabaseExporter.php` from this folder

## Main decision point

- `DatabaseExporter.php` decides which tables and rows are emitted into the export stream

## Writes and side effects

- Reads table metadata and data
- Writes SQL export files

## Debug first

- `DatabaseExporter.php`
