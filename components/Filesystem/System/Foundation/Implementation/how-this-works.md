---
title: Filesystem-Implementation-how-this-works
owner: Filesystem
last_reviewed: 2026-04-26
classification: internal
---

# Filesystem Implementation How This Works

## What this folder is

Contains concrete implementations of the `Filesystem` interface. Currently provides the local-disk adapter.

## Direct files in this folder

### LocalFilesystem.php

Delegates all filesystem operations to local disk through action-owner classes (`ReadFile`, `WriteToFile`,
`CreateDirectory`, etc.). Injected via DI wherever `Filesystem` is type-hinted.

## Debug first

- Start here when file reads/writes fail on the local disk
- Check the individual action-owner class (e.g., `CreateDirectory`) for the actual I/O call
