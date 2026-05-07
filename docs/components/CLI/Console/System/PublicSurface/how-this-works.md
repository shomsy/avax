---
title: cli-console-public-surface-how-this-works
owner: CLI
last_reviewed: 2026-05-06
classification: internal
---

# CLI Console Public Surface How This Works

## What this folder is

The `PublicSurface/` folder defines the stable API for the CLI Console component. It contains the main `Console`
application class and the abstract `Command` class that all user commands must extend.

## Real commands or triggers that reach this folder

- `php avax <command>`
- Registration of new commands: `$console->register(new MyCommand())`

## Exact upstream handoffs

- `avax` binary -> `Console::run()`
- Application service providers -> `Console::register()`

## Direct files in this folder

### Console.php

This is the main application class that coordinates command registration and execution.

- **What arrives here:** Raw `argv` array.
- **What leaves this file:** Exit code (integer).
- **Why you open it first:** To debug command resolution or the main execution loop.

### Command.php

This is the abstract base class for all CLI commands.

- **What arrives here:** `ConsoleInput` and `ConsoleOutput`.
- **What leaves this file:** Exit code (integer).
- **Why you open it first:** To debug argument binding, signature parsing, or to understand the command lifecycle.
