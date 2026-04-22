---
title: Execution-how-this-works
owner: foundation-database-migrations
last_reviewed: 2026-04-22
classification: internal
---

# Execution How This Works

## What this folder is

This folder owns runtime execution of migrations once design intent has been resolved.

## Real commands or triggers that reach this folder

- Migrations.php constructs repository, runner, and console-facing runtime services from this folder

## Exact upstream handoffs

- Integrations/Console and Migrations.php route execution work here

## Main decision point

- Runner and repository types decide apply, rollback, and audit behavior

## Writes and side effects

- Executes migration SQL and persists migration history

## Debug first

- Start with Execution/Runner/MigrationRunner.php or Execution/Repository/MigrationRepository.php
