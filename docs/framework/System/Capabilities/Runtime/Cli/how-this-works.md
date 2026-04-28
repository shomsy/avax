---
title: cli-runtime-how-this-works
owner: framework-system
last_reviewed: 2026-04-27
classification: internal
---

# CLI Runtime How This Works

## What this folder is

This folder owns the adapter that turns raw `argv` input into a framework console result.

## Real commands or triggers that reach this folder

- `php bin/avax <command>`

## Exact upstream handoffs

- `bin/avax`
- function: `CliRuntime::run(...)`
- `CliRuntime::run(...)` -> `ConsoleKernel::run(...)`

## The simplest story

- read CLI input
- delegate to the console public surface
- render the runtime result for stdout

## The first important path

When you type:

```bash
php bin/avax ping
```

the important path is:

```mermaid
sequenceDiagram
    autonumber
    participant Entry as bin/avax
    participant Runtime as CliRuntime
    participant Input as CliInputReader
    participant Kernel as ConsoleKernel
    participant Output as CliOutputWriter
    Entry ->> Runtime: Step 1: pass argv
    Runtime ->> Input: Step 2: parse command and arguments
    Input ->> Kernel: Step 3: delegate execution
    Kernel ->> Output: Step 4: return result for rendering
```

## What gets written changed or executed

- one command is selected and executed
- stdout rendering happens only after the console kernel returns a result

## Failure path

- missing commands or command errors surface through runtime results and explicit flow failures

## What user sees

- rendered console output and exit code

## Where to debug first

- `framework/System/Capabilities/Runtime/Cli/CliInputReader.php`
- `framework/System/Capabilities/Runtime/Cli/CliRuntime.php`
- `framework/System/Capabilities/Runtime/Cli/CliOutputWriter.php`
