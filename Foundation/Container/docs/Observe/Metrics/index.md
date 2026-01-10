# Observe/Metrics

## What This Folder Represents
This folder gathers performance and behavior metrics from resolution flows.

Technically, `Observe/Metrics` contains collectors and exporters that aggregate telemetry about container operation: timing, counts, hit rates, and other observability signals. The goal is to make “how the container behaves” measurable instead of guessy.

### For Humans: What This Means (Represent)
This is the container’s fitness tracker: it tells you how hard it’s working and where it struggles.

## What Belongs Here
- Metrics collectors and aggregators.
- Exporters that send telemetry to sinks.
- Integrations with logging/monitoring systems.

### For Humans: What This Means (Belongs)
If it counts, times, or reports container behavior, it belongs here.

## What Does NOT Belong Here
- Inspection/dumping tools (those are `Observe/Inspect`).
- Guards/policies that block resolution (those are `Guard`).

### For Humans: What This Means (Not Belongs)
Metrics describe reality; guards change reality.

## How Files Collaborate
Collectors gather signals during resolution. Exporters format and send signals to sinks. Sinks decide what to do with the data (drop it, log it, store it).

### For Humans: What This Means (Collaboration)
Collect → format → send → store (or drop).

