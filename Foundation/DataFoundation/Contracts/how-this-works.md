---
title: Contracts-how-this-works
owner: foundation-data
last_reviewed: 2026-04-24
classification: internal
---

# Contracts How This Works

## What this folder is

This folder owns the explicit public contracts for root DataFoundation facades.

## Real commands or triggers that reach this folder

- Composer autoload when `ArrhaeInterface` or `CollectionInterface` is type-hinted
- Static analysis and test suites that verify public API compatibility

## Exact upstream handoffs

- caller file -> `CollectionInterface` or `ArrhaeInterface`
- implementation file -> `Collection.php` or `Arrhae.php`

## The simplest story

- App code type-hints a root facade contract
- A concrete root facade implements that contract
- The contract protects the stable public shape during migration

## Debug first

- start here when a signature change breaks consumers
- start here when root facade behavior and contract drift apart

## What to remember

- contracts define public promise, not internal mechanics
- `pull()` is a semantic state transition and now returns `Pair`
