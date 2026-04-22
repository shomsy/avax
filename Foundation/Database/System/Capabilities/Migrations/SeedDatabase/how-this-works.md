---
title: seeddatabase-how-this-works
owner: foundation-database-migrations
last_reviewed: 2026-04-22
classification: internal
---

# SeedDatabase How This Works

## What this folder is

This folder owns base seeder behavior for populating database tables.

## Real commands or triggers that reach this folder

- `Integrations/Console/SeedCommand.php`

## Exact upstream handoffs

- Seeder classes extend `Seeder.php` from this folder

## Main decision point

- `Seeder.php` decides how nested seeders are invoked and how table builders are retrieved

## Writes and side effects

- Writes seed data through query builders

## Debug first

- `Seeder.php`
