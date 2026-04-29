---
title: Application Filesystem Architecture
owner: Architecture Team
last_reviewed: 2026-04-29
classification: Application Core
---

# How This Works: Filesystem Component

The Filesystem component provides an abstraction over local and remote storage systems using a disk-based driver
pattern.

## Architecture Topology

```mermaid
flowchart TD
    App[Application] --> Filesystem[Filesystem PublicSurface]
    Filesystem --> Resolve[ResolveDisk Flow]
    Resolve --> Drivers[Capabilities / Drivers]
    Drivers --> Local[LocalDisk]
```

## Key Capabilities

### 1. Disk Drivers

Supports multiple "disks" defined in configuration. Each disk uses a specific driver (e.g., `LocalDisk`) to perform I/O
operations.

## Where to Debug First

1. **Permission Denied**: `Avax\Components\Application\Filesystem\System\Capabilities\Drivers`.
2. **Disk Resolution**: `Avax\Components\Application\Filesystem\System\Flows\ResolveDisk`.

## Evidence

- `components/Application/Filesystem/`
