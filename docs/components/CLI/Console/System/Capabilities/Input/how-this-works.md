---
title: cli-console-input-how-this-works
owner: CLI
last_reviewed: 2026-05-06
classification: internal
---

# CLI Console Input How This Works

## What this folder is

The `Input/` folder owns the translation of raw terminal strings (`argv`) into structured data that commands can easily
consume.

## Direct files in this folder

### ConsoleInput.php

This class is the primary data structure for command input.

- **What arrives here:** Raw `array` of strings from `argv`.
- **What leaves this file:** Structured arguments and options via `getArgument()` and `getOption()`.
- **Why you open it first:** To debug why a specific flag or positional argument isn't being recognized.

## The simplest story

- `Console` calls `ConsoleInput::fromArgv()`.
- The `parse()` method loops through the strings, identifying long options (`--`), short options (`-`), and positional
  arguments.
- `Command` later calls `bindNamedArguments()` to map indexed arguments to names from the command signature.
