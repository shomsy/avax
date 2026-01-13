# Features/Operate/Config

## What This Folder Represents

This folder defines how you configure container behavior for different environments.

Technically, `Features/Operate/Config` holds configuration DTOs and profiles that control operational settings: cache
directories, diagnostics flags, bootstrap profiles, and potentially infrastructure integrations. It’s distinct from
“definitions” because it configures the container’s runtime machinery, not individual service bindings.

### For Humans: What This Means (Represent)

This is where you decide “how the container should behave” depending on where it runs (dev, staging, prod).

## What Belongs Here

- Container configuration objects and bootstrap profiles.
- Infrastructure/wiring configuration for caching and telemetry.

### For Humans: What This Means (Belongs)

If it’s a knob that changes container runtime behavior, it goes here.

## What Does NOT Belong Here

- Service definitions (those live in `Features/Define`).
- Observability tooling output (that lives in `Observe`).

### For Humans: What This Means (Not Belongs)

This folder configures the container system, not your application services directly.

## How Files Collaborate

Boot code loads/creates config objects from environment sources. Other runtime subsystems (prototype cache, telemetry,
scope) read those configs to decide where to store data and how strict to be.

### For Humans: What This Means (Collaboration)

Config is the shared “settings sheet” used by many parts of the container.

