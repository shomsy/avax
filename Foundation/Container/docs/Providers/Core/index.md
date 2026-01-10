# Providers/Core

## What This Folder Represents
This folder contains foundational providers for core infrastructure integrations (configuration, filesystem, logging).

Technically, these providers register the “platform services” that many other subsystems depend on. By keeping them separate, you can bootstrap the container in layers: core infrastructure first, then higher-level features.

### For Humans: What This Means (Represent)
These are the “must-have basics” you want available early.

## What Belongs Here
- Providers for config access, filesystem integration, and logging/observability wiring.

### For Humans: What This Means (Belongs)
If lots of things depend on it, it’s probably a Core provider.

## What Does NOT Belong Here
- Domain-specific providers (HTTP, Auth, Database).

### For Humans: What This Means (Not Belongs)
Core is “plumbing”; domain providers are “rooms”.

## How Files Collaborate
Core providers usually run early and register base services. Domain providers then assume those services exist (config, logger, filesystem) and build on top of them.

### For Humans: What This Means (Collaboration)
Get the foundations right first, then build the features.

