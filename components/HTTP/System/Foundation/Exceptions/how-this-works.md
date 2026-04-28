---
title: HTTP-Exceptions-how-this-works
owner: HTTP
last_reviewed: 2026-04-26
classification: internal
---

# HTTP Exceptions How This Works

## What this folder is

Owns exception types specific to the HTTP layer — currently the 404 Not Found exception.

## Direct files in this folder

### NotFoundException.php

Thrown when a requested resource does not exist. Automatically enriches the message with file, line, and stack trace
context for debugging. Sets HTTP status code 404.

## Debug first

- Start here when routes or resources return 404 responses
- The detailed message includes the exact file and line where the exception originated
