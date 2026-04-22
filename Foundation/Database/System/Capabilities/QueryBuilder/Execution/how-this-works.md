---
title: Execution-how-this-works
owner: foundation-database-querybuilder
last_reviewed: 2026-04-22
classification: internal
---

# Execution How This Works

## What this folder is

This folder owns execution orchestration, PDO dispatch, and the low-level executor contract.

## Real commands or triggers that reach this folder

- QueryBuilder.php delegates compiled SQL here

## Exact upstream handoffs

- QueryBuilderRuntime.php builds executors and orchestrators from this folder

## Main decision point

- QueryOrchestrator.php decides transaction wrapping, pretend mode, and deferred execution behavior

## Writes and side effects

- Executes SQL statements against PDO connections

## Debug first

- Start with QueryOrchestrator.php and PDOExecutor.php
