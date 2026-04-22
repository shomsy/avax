---
title: TypeMapping-how-this-works
owner: foundation-database-migrations
last_reviewed: 2026-04-22
classification: internal
---

# TypeMapping How This Works

## What this folder is

This folder owns focused design-time support types for the migration DSL.

## Real commands or triggers that reach this folder

- Blueprint and migration design flows delegate here when shaping schema changes

## Exact upstream handoffs

- Design/* composes these files into the final migration statements

## Main decision point

- These files define how structural schema intent is represented or rendered

## Writes and side effects

- No direct side effects

## Debug first

- Start with the design type directly involved in the failing migration path
