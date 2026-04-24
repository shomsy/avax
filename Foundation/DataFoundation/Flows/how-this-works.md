---
title: Flows-how-this-works
owner: foundation-data
last_reviewed: 2026-04-24
classification: internal
---

# Flows How This Works

## What this folder is

This folder owns staged, lazy, batched, and windowed data processing.

## Real commands or triggers that reach this folder

- app code building a `Pipeline`
- app code wrapping iterables with `LazySequence`, `Batch`, or `Window`

## Exact upstream handoffs

- app code -> `Flows/Pipeline/Pipeline.php::process`
- app code -> `Flows/LazySequence/LazySequence.php::getIterator`

## The simplest story

- a caller chooses a flow abstraction
- the flow applies ordered stages or lazy iteration rules
- the result leaves as a processed value or an iterable view

## Debug first

- start in `Pipeline.php` when stage order or stage output looks wrong
- start in `LazySequence.php` when work runs too early or too late

## What to remember

- flows are about movement and evaluation order
- they do not replace collections or values
