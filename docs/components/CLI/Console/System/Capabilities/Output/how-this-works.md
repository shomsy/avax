---
title: cli-console-output-how-this-works
owner: CLI
last_reviewed: 2026-05-06
classification: internal
---

# CLI Console Output How This Works

## What this folder is

The `Output/` folder owns the responsibility of writing text to the terminal. It handles colorization via ANSI escape
codes and supports disabling colors for environments that don't support them.

## Direct files in this folder

### ConsoleOutput.php

The primary service for writing to `STDOUT`.

- **What arrives here:** Plain strings and formatting requests (info, error, bold).
- **What leaves this file:** ANSI-formatted strings written to the output buffer.
- **Why you open it first:** To debug layout issues or color display problems.
