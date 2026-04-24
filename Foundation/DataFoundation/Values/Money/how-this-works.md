---
title: Money-how-this-works
owner: foundation-data
last_reviewed: 2026-04-24
classification: internal
---

# Money How This Works

## What this folder is

This folder owns Money behavior inside DataFoundation.
The PHP files in this folder are: Currency.php,Money.php.

## Real commands or triggers that reach this folder

- app code instantiating the public type or helper from this folder
- PHPUnit coverage that exercises the invariant owned here

## Exact upstream handoffs

- app code -> Values/Money/Currency.php

## The simplest story

- input reaches a narrow type or helper in this folder
- the file here enforces one local invariant or one technical rule
- the caller receives a stable value or converted shape

## What to remember

- this folder owns one narrow responsibility
- if behavior drifts, start with the PHP file listed above, not a broader lane
