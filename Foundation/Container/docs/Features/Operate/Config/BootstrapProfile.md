# BootstrapProfile

## Quick Summary

- This file defines an enterprise-grade configuration aggregator that bundles container and telemetry settings.
- It exists to provide a single, immutable "Profile" that can be passed to the `ContainerBootstrap` or
  `ContainerBuilder`.
- It removes the complexity of managing multiple configuration objects separately by providing unified environment
  presets (Production, Development, Testing).

### For Humans: What This Means (Summary)

This is the **Master Configuration Bundle**. Instead of handing the container a handful of loose settings, you hand it
one specific "Profile" (like "The Production Profile") which automatically contains the right rules for speed, security,
and logging.

## Terminology (MANDATORY, EXPANSIVE)

- **Bootstrap Profile**: A high-level object that wraps all sub-configurations needed to start the container.
    - In this file: Represented by the `BootstrapProfile` class.
    - Why it matters: It ensures that "Container Settings" and "Metrics Settings" are always kept together and stay
      consistent.
- **Container Config**: Settings for the internal behavior of the DI container.
    - In this file: The `$container` property.
    - Why it matters: It controls things like caching and strict mode.
- **Telemetry Config**: Settings for how the container records its own performance.
    - In this file: The `$telemetry` property.
    - Why it matters: It determines what the container logs and how much overhead it adds for monitoring.
- **Environment Preset**: A static factory method that returns a pre-tuned profile.
    - In this file: `production()`, `development()`, `testing()`.
    - Why it matters: It prevents developers from having to manually figure out which debug flags to set in production.

### For Humans: What This Means (Terminology)

It’s like a **Travel Profile**: if you select "Business Trip", you get a laptop and a suit; if you select "Beach
Vacation", you get swimsuits and sunscreen. The profile makes sure you have exactly the right gear for your destination.

## Think of It

Think of a **New Car Configuration**:

- **Profile**: The Trim Level (e.g., "Sport", "Luxury", "Economy").
- **Container Config**: The Engine and Transmission settings (how it runs).
- **Telemetry Config**: The Dashboard and Sensors (what it tells you).

### For Humans: What This Means (Analogy)

You don't want to manually choose the gear ratios and the sensor types separately every time you buy a car. You just
pick the "Sport" profile, and the manufacturer (this class) gives you a bundle that works perfectly together.

## Story Example

You are deploying a new version of your app to a staging server. You want it to behave exactly like production, but you
want a bit more logging. You start with `BootstrapProfile::production()`, but then use a "modifier" method to swap in a
more verbose `TelemetryConfig`. The resulting profile is still immutable and safe, but perfectly tuned for your staging
environment.

### For Humans: What This Means (Story)

It gives you a solid "Base" to start from, but lets you make safe, predictable tweaks.

## For Dummies

Imagine a DVD player setup.

1. **Production Profile**: "Optimized" (Fast loading, no subtitles, no menus).
2. **Development Profile**: "Special Edition" (Director's commentary, behind-the-scenes, full menus).
3. **Testing Profile**: "Diagnostic Mode" (Runs tests on the laser).

### For Humans: What This Means (Walkthrough)

Pick your "Mode" at the beginning of your `index.php`, and the rest of the application will follow those rules
automatically.

## How It Works (Technical)

The `BootstrapProfile` is a pure DTO that uses PHP 8.1+ `readonly` properties to enforce immutability. It provides
static factory methods (`production()`, etc.) that act as the source of truth for environment defaults. To allow "
modification" while staying immutable, it provides `with*` methods that return a new clone of the profile with one
specific property changed.

### For Humans: What This Means (Technical)

It’s a "Locked Box". You can't change the settings inside once it's created. If you want a different setting, you have
to "Order a New Box" based on the old one. This makes the system very stable and easy to debug.

## Architecture Role

- **Lives in**: `Features/Operate/Config`
- **Role**: Configuration aggregator.
- **Dependencies**: Depends on `ContainerConfig` and `TelemetryConfig`.
- **Consumer**: Used by `ContainerBootstrap` and `ContainerBuilder`.

### For Humans: What This Means (Architecture)

It is the "Contract" that describes the entire runtime environment of the container.

## Methods

### Method: __construct(ContainerConfig $container, TelemetryConfig $telemetry)

#### Technical Explanation: __construct

Standard constructor for the aggregate DTO.

#### For Humans: What This Means

The primary way to build a custom profile from scratch by providing both sets of settings.

### Method: development()

#### Technical Explanation: development

Factory that returns a profile initialized with development-friendly defaults (debug ON, sampling 100%).

#### For Humans: What This Means

The "Developer Mode" profile. It’s loud, it logs everything, and it’s very helpful for finding bugs.

### Method: production()

#### Technical Explanation: production

Factory that returns a profile initialized with performance-optimized defaults (debug OFF, sampling 10%).

#### For Humans: What This Means

The "Stealth Mode" profile. It’s fast and quiet, only recording enough data to make sure things are healthy.

### Method: testing()

#### Technical Explanation: testing

Factory that returns a profile tuned for automated testing (telemetry OFF, cache often disabled).

#### For Humans: What This Means

The "Lab Mode" profile. It isolates the app so that your tests are fast and don't get messy with external logs.

### Method: fromArrays(array $container = [], array $telemetry = [])

#### Technical Explanation: fromArrays

A convenience method that takes raw multidimensional arrays (from a config file) and hydrates them into the typed DTO
structure.

#### For Humans: What This Means

Lets you load your settings from a simple text file and turns them into "Smart Objects".

### Method: withContainer(ContainerConfig $container)

#### Technical Explanation: withContainer

Returns a new instance of the profile with the `container` property replaced.

#### For Humans: What This Means

Lets you take an existing profile and "Swap in" a new set of container settings.

### Method: withTelemetry(TelemetryConfig $telemetry)

#### Technical Explanation: withTelemetry

Returns a new instance of the profile with the `telemetry` property replaced.

#### For Humans: What This Means

Lets you take an existing profile and "Swap in" a new set of metrics settings.

## Risks & Trade-offs

- **Strictness**: Because it's a typed object, you can't just pass any random configuration key into it. This is a
  trade-off for better type safety.
- **Dependency**: If you add a third configuration area (e.g., `SecurityConfig`), this profile must be updated, which is
  a minor maintenance cost.

### For Humans: What This Means (Risks)

It’s very picky—it won't let you use a setting it doesn't recognize. This can be annoying at first, but it saves you
from "Silent Failures" where a typo in a config file causes a production crash.

## Related Files & Folders

- `ContainerConfig.php`: The settings for the "Engine".
- `TelemetryConfig.php`: The settings for the "Dashcam".
- `ContainerBootstrap.php`: The orchestrator that actually uses this profile.

### For Humans: What This Means (Relationships)

If the **Bootstrap** is the pilot, the **Profile** is the flight plan they've been given.
