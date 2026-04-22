---
title: Design-how-this-works
owner: foundation-database-migrations
last_reviewed: 2026-04-22
classification: internal
---

# Design How This Works

## What this folder is

This folder owns the migration DSL and the base migration contract.

## Real commands or triggers that reach this folder

- Migration classes and runtime schema operations resolve design primitives from this folder

## Exact upstream handoffs

- Migrations/Execution and user migration files consume these types

## Main decision point

- Blueprint and BaseMigration decide how migration intent is expressed before execution

## Writes and side effects

- BaseMigration ultimately executes compiled statements through QueryBuilder

## Debug first

- Start with BaseMigration.php and Design/Table/Blueprint.php
