---
title: Grammar-how-this-works
owner: foundation-database-querying
last_reviewed: 2026-04-22
classification: internal
---

# Grammar How This Works

## What this folder is

This folder owns SQL compilation for the QueryBuilder capability.

## Real commands or triggers that reach this folder

- QueryBuilder.php calls into grammar implementations when compiling SQL

## Exact upstream handoffs

- Querying injects the active grammar into each builder

## Main decision point

- Grammar classes decide how immutable builder state becomes dialect-specific SQL

## Writes and side effects

- No direct side effects; these files only compile SQL text

## Debug first

- Start with BaseGrammar.php or the active dialect grammar
