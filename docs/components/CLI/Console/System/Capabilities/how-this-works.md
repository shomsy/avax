---
title: cli-console-capabilities-how-this-works
owner: CLI
last_reviewed: 2026-05-06
classification: internal
---

# CLI Console Capabilities How This Works

## What this folder is

The `Capabilities/` folder contains the functional units that provide CLI features: input parsing, output writing, UI
components (like progress bars and tables), and the default commands.

## Child folders in this folder

### Input/

Open `Input/how-this-works.md`. Parses `argv` into named arguments and options.

### Output/

Open `Output/how-this-works.md`. Writes text to the terminal with ANSI color support.

### UI/

Open `UI/how-this-works.md`. Provides higher-level terminal widgets (ProgressBars, Tables, Questions).

### Commands/

Open `Commands/how-this-works.md`. Contains built-in framework commands.
