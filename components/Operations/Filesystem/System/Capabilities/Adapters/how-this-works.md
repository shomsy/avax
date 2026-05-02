---
title: Adapters-how-this-works
owner: operations-filesystem
last_reviewed: 2026-04-30
classification: internal
---

# Adapters How This Works

## What this folder is

This folder owns storage adapters. `StorageAdapter` defines the stable disk contract, while local and S3 adapters
provide concrete disk behavior.

## Real commands or triggers that reach this folder

Application code calls `Storage::disk()`, `Storage::put()`, or a concrete adapter directly in tests.

## Exact upstream handoffs

`Storage` resolves named disks and delegates all behavior to `LocalStorageAdapter` or `S3StorageAdapter`.

## Failure behavior

Local paths are normalized and path traversal is rejected. Missing files return `null` for reads and `true` for
idempotent deletes.
