---
title: Interop-how-this-works
owner: foundation-data
last_reviewed: 2026-04-24
classification: internal
---

# Interop How This Works

## What this folder is

This folder owns explicit bridges between public DataFoundation types and external data shapes.

## Real commands or triggers that reach this folder

- app code converting arrays, iterables, generators, JSON, or XML into DataFoundation types
- app code exporting DataFoundation types back into transport-friendly formats

## Exact upstream handoffs

- app code -> `Interop/Arrays/FromArray.php`
- app code -> `Interop/Json/ToJson.php`
- app code -> `Interop/Xml/FromXml.php`

## The simplest story

- an external shape enters an interop class
- the interop class delegates mechanical parsing or encoding to `Internal/Conversion`
- the caller receives a public DataFoundation type or an external transport shape

## Debug first

- start here when conversion code begins leaking into public types
- start in `Internal/Conversion/*` when JSON or XML output is malformed

## What to remember

- interop depends on public types
- public types must not depend on interop
