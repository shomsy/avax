---
title: console-how-this-works
owner: foundation-database-integrations
last_reviewed: 2026-04-22
classification: internal
---

# Console How This Works

## What this folder is

This folder contains CLI-facing wrappers for Database migration commands. The wrappers keep command ownership out of the
Database core while delegating to the System migration runtime.

## Real commands that reach this folder

- `migrate`
- `migrate:rollback`
- `make:migration`

## Exact CLI front doors

- `Foundation/Commands/CommandDefinitions.php` maps command aliases to these wrapper classes

## Main decision point

- Each wrapper constructs the corresponding System migration command and forwards arguments without introducing new core
  behavior

## Writes and side effects

- Runs migrations
- Rolls back migrations
- Generates migration files

## Debug first

- `MigrateCommand.php`
- `MigrateRollbackCommand.php`
- `MakeMigrationCommand.php`
