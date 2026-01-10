# Observe/Inspect

## What This Folder Represents
This folder is the “inspection tools” layer for the container.

Technically, `Observe/Inspect` contains utilities that read container state, prototypes, and resolution metadata to produce human-facing diagnostics (CLI dumps, inspectors, managers). These tools are about understanding the container, not changing its behavior.

### For Humans: What This Means (Represent)
This folder is your container’s “X-ray machine”. It helps you see what’s happening inside.

## What Belongs Here
- Inspectors and managers that read container/prototype information.
- CLI-facing dumpers and diagnostic helpers.

### For Humans: What This Means (Belongs)
If a class helps you answer “what’s registered?”, “what was resolved?”, “why did this fail?”, it belongs here.

## What Does NOT Belong Here
- Core resolution logic and steps.
- Policy enforcement (that’s `Guard` / policy actions).

### For Humans: What This Means (Not Belongs)
Inspection observes; it shouldn’t change the rules of resolution.

## How Files Collaborate
Inspectors typically consume prototype models, definition store data, and telemetry timelines. They format those into readable reports for developers (often via CLI commands).

### For Humans: What This Means (Collaboration)
These tools don’t fix problems directly, but they make problems obvious.

