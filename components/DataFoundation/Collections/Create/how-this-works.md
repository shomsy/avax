---
title: Create-how-this-works
owner: foundation-data
last_reviewed: 2026-04-24
classification: internal
---

# How This Works

`Collections/Create` owns creation-time normalization for collection-like inputs.

- `MakeCollection.php` normalizes iterable input into a stable array shape.
- `WrapValue.php` converts one value into a collection-ready array payload.

This keeps `Collection::make/wrap()` and `Arrhae::make/wrap()` thin and consistent.
