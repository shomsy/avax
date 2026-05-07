---
title: application-text-how-this-works
owner: Application
last_reviewed: 2026-05-06
classification: internal
---

# Application Text How This Works

## What this folder is

The `Application/Text` component provides a rich set of string manipulation and validation utilities. it handles case
conversion, inflection (pluralization/singularization), transformation, and extraction.

## Real commands or triggers that reach this folder

- `Avax\Components\Application\Text\System\PublicSurface\Text::slug('Hello World')`
- `str('hello')->camel()` (via shortcuts)

## The simplest story

- A developer calls a string manipulation method on the `Text` facade or uses the `str()` helper.
- The component delegates the operation to a specific capability (e.g., `CaseConversion`, `Transform`).
- The result is returned as a string or a `Stringable` object.

## Child folders in this folder

### System/

Open `System/how-this-works.md`.

## What to remember

- Most methods are available via the `str()` helper.
- The component uses `mbstring` for UTF-8 safety where possible.
