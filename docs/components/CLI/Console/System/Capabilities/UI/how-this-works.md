---
title: cli-console-ui-how-this-works
owner: CLI
last_reviewed: 2026-05-06
classification: internal
---

# CLI Console UI How This Works

## What this folder is

The `UI/` folder provides interactive and structural terminal widgets, such as progress bars, tables, and user
questions/confirmations.

## Direct files in this folder

### ProgressBar.php / ConsoleProgressBar.php

Manages the rendering of a visual progress bar.

### Table.php

Formats data into a visual grid with headers and borders.

### Question.php / Confirm.php

Handles interactive user input via `STDIN`.
