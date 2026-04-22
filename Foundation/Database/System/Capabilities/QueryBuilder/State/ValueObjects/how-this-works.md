---
title: ValueObjects-how-this-works
owner: foundation-database-querybuilder
last_reviewed: 2026-04-22
classification: internal
---

# ValueObjects How This Works

## What this folder is

This folder owns supporting state, AST nodes, DTOs, enums, value objects, or exceptions for QueryBuilder.

## Real commands or triggers that reach this folder

- QueryBuilder fluent calls create or consume these support types

## Exact upstream handoffs

- Builder, Grammar, and Execution layers all depend on these local support files

## Main decision point

- These types define the data shape and error boundaries of query execution

## Writes and side effects

- No direct side effects

## Debug first

- Start with the exact support type named in the failing stack trace
