---
title: createmigration-how-this-works
owner: foundation-database-migrations
last_reviewed: 2026-04-22
classification: internal
---

# CreateMigration How This Works

## What this folder is

This folder owns generating new migration files and their stub templates.

## Real commands or triggers that reach this folder

- `Migrations::generator()`
- `Integrations/Console/MakeMigrationCommand.php`

## Exact upstream handoffs

- `Migrations.php` returns `MigrationGenerator.php` from this folder

## Main decision point

- `MigrationGenerator.php` decides file naming, class naming, and stub selection

## Writes and side effects

- Creates migration files on disk

## Debug first

- `MigrationGenerator.php`
