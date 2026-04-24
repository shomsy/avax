---
title: Structures-how-this-works
owner: foundation-data
last_reviewed: 2026-04-24
classification: internal
---

# Structures How This Works

## What this folder is

This folder owns access-order-sensitive data structures.

## Real commands or triggers that reach this folder

- app code that needs stack, queue, deque, priority, buffer, or tree behavior

## Exact upstream handoffs

- app code -> `Structures/<Type>/<Type>.php`

## The simplest story

- input enters a structure constructor or write-like method
- the structure preserves its invariant immediately
- reads and removals expose state transitions through explicit public shapes

## Debug first

- start here when order-sensitive behavior looks like a generic collection bug
- start in `PriorityQueue.php` or `RingBuffer.php` when priority or capacity rules are wrong

## What to remember

- structures exist because access order is the semantics
- they are not renamed collections
