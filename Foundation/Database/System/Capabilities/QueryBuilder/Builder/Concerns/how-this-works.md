---
title: Concerns-how-this-works
owner: foundation-database-querybuilder
last_reviewed: 2026-04-22
classification: internal
---

# Concerns How This Works

## What this folder is

This folder decomposes the QueryBuilder public API into focused behavioral concerns.

## Real commands or triggers that reach this folder

- QueryBuilder.php mixes these traits into the public fluent builder

## Exact upstream handoffs

- Builder/QueryBuilder.php routes specific DSL calls into these concern traits

## Main decision point

- Each trait owns one coherent slice of builder behavior such as conditions, joins, or aggregates

## Writes and side effects

- Side effects happen only after compiled SQL is handed to execution layers

## Debug first

- Start with the concern trait that owns the broken fluent method
