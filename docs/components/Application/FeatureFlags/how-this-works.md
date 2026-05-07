---
title: application-featureflags-how-this-works
owner: Application
last_reviewed: 2026-05-06
classification: internal
---

# Application FeatureFlags How This Works

## What this folder is

The `Application/FeatureFlags` component manages conditional feature activation. It allows checking if a feature is
enabled for the current request, user, or environment.

## Real commands or triggers that reach this folder

- `Avax\Components\Application\FeatureFlags\System\PublicSurface\FeatureFlags::enabled('feature-name')`

## The simplest story

- A developer defines a feature flag in the configuration or code.
- At runtime, the application checks `FeatureFlags::enabled('my-feature')`.
- The component evaluates the flag against registered drivers (e.g., config, database, or custom logic).
- It returns `true` or `false`.

## Child folders in this folder

### System/

Open `System/how-this-works.md`.

## What to remember

- Flags can be simple booleans or complex rules.
- The component supports multiple drivers for flag resolution.
