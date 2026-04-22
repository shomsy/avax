---
title: runmigrations-how-this-works
owner: foundation-database-migrations
last_reviewed: 2026-04-22
classification: internal
---

# RunMigrations How This Works

## What this folder is

This folder owns migration execution and repository persistence for migration history.

## Real commands or triggers that reach this folder

- `Migrations::repository()`
- `Migrations::runner()`
- migration run, rollback, and status adapters

## Exact upstream handoffs

- `Migrations.php` returns the services from this folder

## Main decision point

- `MigrationRunner.php` decides execution order and up/down lifecycle
- `MigrationRepository.php` decides how the `migrations` table is read and written

## Writes and side effects

- Runs schema changes
- Writes migration audit rows

## Debug first

- `MigrationRunner.php`
- `MigrationRepository.php`
