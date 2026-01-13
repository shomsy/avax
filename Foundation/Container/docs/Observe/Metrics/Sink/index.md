# Observe/Metrics/Sink

## What This Folder Represents

This folder defines where telemetry data goes.

Technically, sinks are pluggable endpoints for metrics export. A sink can write to a logger, push to an external system,
store in memory, or do nothing. Keeping sinks separate prevents the collector from being coupled to any monitoring
vendor.

### For Humans: What This Means (Represent)

This is the “destination” for metrics—like choosing whether your fitness tracker saves to the cloud or just shows
numbers on screen.

## What Belongs Here

- Sink interfaces and implementations.

### For Humans: What This Means (Belongs)

If it’s “where telemetry ends up”, it belongs here.

## What Does NOT Belong Here

- Metrics collection logic.
- Resolution logic.

### For Humans: What This Means (Not Belongs)

Sinks don’t measure; they just receive measurements.

## How Files Collaborate

Exporters send telemetry events to a sink. The sink decides what to do with each event. A null sink is useful when you
want to disable telemetry without changing code paths.

### For Humans: What This Means (Collaboration)

You can turn observability on/off by swapping the sink.

