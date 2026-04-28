---
title: Filesystem-Exceptions-how-this-works
owner: Filesystem
last_reviewed: 2026-04-26
classification: internal
---

# Filesystem Exceptions How This Works

## What this folder is

Owns all exception types thrown by the Filesystem component during file and directory operations.

## Real commands or triggers that reach this folder

- Any `Filesystem` method that encounters a missing path, permission error, or I/O failure

## Direct files in this folder

### FilesystemException.php

Base exception for all filesystem errors. Enriches the message with the offending path for easier debugging.

## Debug first

- Start here when any filesystem operation throws — the path is embedded in the exception message
