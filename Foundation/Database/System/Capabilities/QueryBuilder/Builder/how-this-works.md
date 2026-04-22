---
title: Builder-how-this-works
owner: foundation-database-querybuilder
last_reviewed: 2026-04-22
classification: internal
---

# Builder How This Works

## What this folder is

This folder owns the main fluent QueryBuilder type and its join helper.

## Real commands or triggers that reach this folder

- QueryBuilderRuntime creates builders from this folder for every caller entrypoint

## Exact upstream handoffs

- Database::table() and Database::query()->from() resolve into this folder

## Main decision point

- QueryBuilder.php decides the public fluent API and builder cloning semantics

## Writes and side effects

- Builder mutations compile into SQL and delegate execution downstream

## Debug first

- Start with QueryBuilder.php
