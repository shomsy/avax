# BootstrapProfile

## Quick Summary
- This file defines an immutable “bootstrap profile” that bundles container config and telemetry config.
- It exists so you can pick predictable presets (development/production/testing/etc.) for container startup.
- It removes the complexity of scattering environment-specific knobs by centralizing them into one object.

### For Humans: What This Means
Instead of tweaking 20 settings everywhere, you choose a profile like “development” or “production” and the container behaves consistently.

## Terminology (MANDATORY, EXPANSIVE)
- **Bootstrap profile**: A pre-made configuration bundle for startup.
  - In this file: `BootstrapProfile` contains `ContainerConfig` and `TelemetryConfig`.
  - Why it matters: it standardizes behavior per environment.
- **Preset**: A named configuration recipe.
  - In this file: `development()`, `production()`, `testing()`, `staging()`… create presets.
  - Why it matters: presets prevent “snowflake configuration”.
- **Immutable config**: Configuration that doesn’t change after creation.
  - In this file: `final readonly`.
  - Why it matters: startup behavior stays predictable during runtime.
- **Telemetry**: Observability settings (sampling, sinks, stack traces).
  - In this file: controlled by `TelemetryConfig`.
  - Why it matters: you want different visibility levels in dev vs prod.

### For Humans: What This Means
This is your “mode selector” for the container.

## Think of It
Think of it like choosing a driving mode in a car:
- Sport (dev): lots of feedback, less strict.
- Eco (prod): optimized and controlled.

### For Humans: What This Means
Different modes are optimized for different goals, and you shouldn’t have to rebuild the car to switch.

## Story Example
In CI you use `testing()` so telemetry is minimal and the environment is deterministic. In production you use `production()` so strict mode and compilation are enabled. Same codebase, different profile choice.

### For Humans: What This Means
You don’t hand-edit config files for every environment—you select a profile.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. Pick a preset (development/production/testing).
2. If needed, build from arrays (`fromArrays()`).
3. Pass the profile to your bootstrap orchestrator.

## How It Works (Technical)
The profile is just two typed configuration objects. Static constructors create preset combinations. `fromArrays()` adapts raw arrays (from files/env) into typed configs via `ContainerConfig::fromArray()` and `TelemetryConfig::fromArray()`.

### For Humans: What This Means
It’s a “bundle” that turns raw config into structured config.

## Architecture Role
- Why it lives in `Features/Operate/Config`: it configures operational startup behavior.
- What depends on it: `ContainerBootstrap` and any environment-driven boot logic.
- What it depends on: `ContainerConfig` and `TelemetryConfig`.
- System-level reasoning: profile selection is the simplest reliable environment switch.

### For Humans: What This Means
Profiles are how you avoid “works on my machine” configuration drift.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(ContainerConfig $container, TelemetryConfig $telemetry)

#### Technical Explanation
Stores the container runtime settings and telemetry settings together.

##### For Humans: What This Means
It ties “how the container behaves” and “how we observe it” into one mode.

##### Parameters
- `ContainerConfig $container`
- `TelemetryConfig $telemetry`

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- Prefer preset factory methods unless you’re building custom profiles.

##### Common Mistakes
- Mixing production telemetry with development container strictness unintentionally.

### Method: development()

#### Technical Explanation
Returns a development-optimized profile.

##### For Humans: What This Means
It turns on “developer-friendly” settings.

##### Parameters
- None.

##### Returns
- `self`

##### Throws
- No explicit exceptions.

##### When to Use It
- Local development.

##### Common Mistakes
- Using it in production.

### Method: production()

#### Technical Explanation
Returns a production-optimized profile.

##### For Humans: What This Means
It turns on “production-safe” settings.

##### Parameters
- None.

##### Returns
- `self`

##### Throws
- No explicit exceptions.

##### When to Use It
- Production deployments.

##### Common Mistakes
- Enabling debug mode via overrides.

### Method: testing()

#### Technical Explanation
Returns a testing-optimized profile.

##### For Humans: What This Means
It gives you deterministic behavior and less noise.

##### Parameters
- None.

##### Returns
- `self`

##### Throws
- No explicit exceptions.

##### When to Use It
- Automated tests/CI.

##### Common Mistakes
- Reusing it for staging/prod.

### Method: staging()

#### Technical Explanation
Returns a staging-like profile built from arrays (mixed production and debug traits).

##### For Humans: What This Means
It’s a “pre-production rehearsal” mode.

##### Parameters
- None.

##### Returns
- `self`

##### Throws
- Depends on downstream config constructors.

##### When to Use It
- Staging environments.

##### Common Mistakes
- Treating staging as production; staging config is often intentionally more verbose.

### Method: fromArrays(array|null $container = null, array|null $telemetry = null)

#### Technical Explanation
Builds a profile from raw config arrays.

##### For Humans: What This Means
It’s how you create a profile from config files.

##### Parameters
- `array|null $container`
- `array|null $telemetry`

##### Returns
- `self`

##### Throws
- Depends on validation in component configs.

##### When to Use It
- When loading config from files or external sources.

##### Common Mistakes
- Passing wrong key names and expecting it to “guess”.

## Risks, Trade-offs & Recommended Practices
- Risk: Presets may lag behind real production needs.
  - Why it matters: production often evolves (new telemetry sink, new cache dir).
  - Design stance: presets are a baseline, not a prison.
  - Recommended practice: keep presets reviewed and versioned; use overrides sparingly.

### For Humans: What This Means
Presets are a great start, but they need maintenance like any other code.

## Related Files & Folders
- `docs_md/Features/Operate/Config/ContainerConfig.md`: The container runtime settings part.
- `docs_md/Features/Operate/Config/TelemetryConfig.md`: The telemetry settings part.
- `docs_md/Features/Operate/Boot/ContainerBootstrap.md`: Uses profiles to bootstrap.

### For Humans: What This Means
Profile bundles config; bootstrap consumes it.

