# TelemetryConfig

## Quick Summary

- This file defines the configuration DTO for container observability and performance monitoring.
- It exists to control how much data the container records about its own internal behavior (sampling, metrics,
  CPU/Memory tracking).
- It removes the complexity of ad-hoc monitoring flags by consolidating them into a single, immutable "Dashcam"
  configuration.

### For Humans: What This Means (Summary)

This is the **Dashboard Settings** for your container's "Flight Recorder". It determines how much information you want
to record: Do you want to know every single thing (Development), or just the highlights (Production)?

## Terminology (MANDATORY, EXPANSIVE)

- **Sampling Rate**: The percentage of events that are actually recorded (0.0 to 1.0).
    - In this file: The `$samplingRate` property.
    - Why it matters: Recording 100% of events in a high-traffic site can slow it down. Sampling 10% gives you accurate
      data with almost zero overhead.
- **CPU Tracking**: Measuring how much "brain power" each resolution takes.
    - In this file: The `$trackCpu` property.
    - Why it matters: CPU tracking is expensive. You usually only turn this on when you're hunting for a "Bottleneck".
- **Memory Tracking**: Measuring how much RAM each service uses.
    - In this file: The `$trackMemory` property.
    - Why it matters: Helps you find "Memory Leaks" or massive objects that shouldn't be singletons.
- **Internal Error Reporting**: Whether the container should log its own internal hiccups.
    - In this file: The `$reportErrors` property.
    - Why it matters: Even if your app doesn't crash, the container might be struggling internally (e.g., repeating
      reflections). This reports those hidden issues.

### For Humans: What This Means (Terminology)

These settings control the **Detail** (Sampling), **Depth** (CPU/Memory), and **Honesty** (Error Reporting) of the
container's performance data.

## Think of It

Think of a **Smart Thermostat** in your house:

- **Enabled**: Is the screen on?
- **Sampling**: Do we check the temp every second, or every 10 minutes?
- **Track CPU/Memory**: Do we also track the humidity and air quality (extra sensors)?
- **Errors**: Does it beep if the Wi-Fi goes down?

### For Humans: What This Means (Analogy)

You don't need all the data all the time. On a normal day, you just want to know the temp. When the AC breaks, you want
all the sensors turned on to find the problem.

## Story Example

Your app is suddenly running slow on Fridays. You check your logs, but they look fine. You go into `TelemetryConfig`,
set the `samplingRate` to `1.0` (100%) and enable `trackCpu`. You run the app for a few minutes on Friday, and the
container's metrics show you exactly which service is taking 500ms to build. You fix it, turn sampling back down to
`0.1`, and the site is fast again.

### For Humans: What This Means (Story)

It gives you a "High-Resolution Microscope" when you need it, and a "Low-Profile Watcher" the rest of the time.

## For Dummies

Imagine a black box on an airplane.

1. **Production**: It only records the most important data so it doesn't run out of storage.
2. **Development**: It records video, audio, and every single switch flick.
3. **Testing**: It's usually turned off because you're doing "Stress Tests" and don't want to skew the results.

### For Humans: What This Means (Walkthrough)

If you're wondering "Why is my app slow?", this is the configuration you need to tweak to find the answer.

## How It Works (Technical)

`TelemetryConfig` is a simple, immutable DTO. Its most important technical feature is the `samplingRate` logic, which is
used by the `MetricsCollector` to decide whether to skip a recording event. By providing `production()` and
`development()` presets, we ensure that the container never accidentally "Over-Monitor" a production environment and
cause its own performance degradation.

### For Humans: What This Means (Technical)

It’s the "Observer's Instructions". It tells the monitoring system exactly what to ignore and what to pay attention to.

## Architecture Role

- **Lives in**: `Features/Operate/Config`
- **Role**: Observability control.
- **Consumer**: Used by `MetricsCollector`, `EnhancedMetricsCollector`, and `Timeline`.

### For Humans: What This Means (Architecture)

It acts as the "Toggle Board" for the container's entire recording system.

## Methods

### Method: __construct(...)

#### Technical Explanation: __construct

Initializes the telemetry DTO with strict types. All properties are optional with best-practice defaults.

#### For Humans: What This Means

Constructs a custom monitor with your specific sensor requests.

### Method: production()

#### Technical Explanation: production

Factory for low-overhead monitoring: 10% sampling, CPU tracking OFF, Memory tracking ON.

#### For Humans: What This Means

The "Safe-for-Public" setting. It records just enough to keep you informed without slowing down your customers.

### Method: development()

#### Technical Explanation: development

Factory for maximum visibility: 100% sampling, all trackers ON.

#### For Humans: What This Means

The "X-Ray Mode" where nothing is hidden and everything is measured.

### Method: testing()

#### Technical Explanation: testing

Factory that disables telemetry entirely to ensure test isolation and speed.

#### For Humans: What This Means

The "Silent Mode" for your automated tests.

### Method: fromArray(array $data)

#### Technical Explanation: fromArray

Maps a loose array from a config file into the typed DTO.

#### For Humans: What This Means

The bridge between your text-based configuration files and the container's internal logic.

## Risks & Trade-offs

- **Overhead**: Enabling `trackCpu` or seting `samplingRate` to `1.0` in production *will* slow down your app slightly.
- **Data Volume**: High sampling rates generate massive amounts of log data. Be careful with your disk space!

### For Humans: What This Means (Risks)

Don't leave "X-Ray Mode" (Development) on in production. It’s like driving with your high-beams on in a rainstorm—it’s
too much information and it might cause problems.

## Related Files & Folders

- `MetricsCollector.php`: The one who reads this config to decide what to do.
- `Timeline.php`: The high-resolution timer that uses these settings.
- `BootstrapProfile.php`: The holder of this config.

### For Humans: What This Means (Relationships)

If the **Metrics Collector** is the camera, this **Config** is the "Resolution" and "FPS" setting.
