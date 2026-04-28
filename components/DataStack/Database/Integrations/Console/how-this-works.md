---
title: integrations-console-how-this-works
owner: foundation-database-integrations
last_reviewed: 2026-04-22
classification: internal
---

# Integrations / Console How This Works

## What this folder is

This folder owns CLI wrappers for migration, seeding, and export commands.

## Real commands that reach this folder

- `migrate`
- `migrate:rollback`
- `migrate:status`
- `migrate:fresh`
- `migrate:refresh`
- `make:migration`
- `db:seed`
- `db:export`

## Exact CLI front doors

- `Foundation/Commands/CommandDefinitions.php` maps command names and aliases to the classes in this folder.
- Each `handle(...)` method is the concrete CLI front door for one command.

## How this folder works

Each command class accepts command input, calls one narrow runtime service, renders console output, and returns an exit
code.

## The simplest story

- A typed command resolves to one wrapper class in this folder.
- That wrapper calls one narrow Database runtime service.
- The wrapper prints the result and returns a process exit code.

## The first important path

- `Foundation/Commands/CommandDefinitions.php` maps the typed command to one wrapper class here.
- The wrapper `handle(...)` method translates CLI input into one runtime call.
- The runtime service performs the work and the wrapper renders the outcome.

## Main units in this folder

- `ExportCommand.php` participates directly in the behavior owned by this folder.
- `MakeMigrationCommand.php` participates directly in the behavior owned by this folder.
- `MigrateCommand.php` participates directly in the behavior owned by this folder.
- `MigrateFreshCommand.php` participates directly in the behavior owned by this folder.
- `MigrateRefreshCommand.php` participates directly in the behavior owned by this folder.
- `MigrateRollbackCommand.php` participates directly in the behavior owned by this folder.
- `MigrateStatusCommand.php` participates directly in the behavior owned by this folder.
- `SeedCommand.php` participates directly in the behavior owned by this folder.

## Writes and side effects

- This slice can register container bindings or translate CLI commands into Database runtime calls.

## Failure shape

- Assembly or adapter mistakes surface here before the deeper runtime does the wrong thing.

## Debug first

- Start with `ExportCommand.php` participates directly in the behavior owned by this folder.
- Start with `MakeMigrationCommand.php` participates directly in the behavior owned by this folder.
- Start with `MigrateCommand.php` participates directly in the behavior owned by this folder.
