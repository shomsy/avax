# Events

## What This Folder Represents
Kernel step lifecycle events emitted during the resolution pipeline: start, success, and failure notifications. They exist to provide structured telemetry payloads.

### For Humans: What This Means
These are the messages the kernel sends when a step begins, finishes, or fails.

## What Belongs Here
Event classes describing step lifecycle moments (`StepStarted`, `StepSucceeded`, `StepFailed`).

### For Humans: What This Means
Only the payload definitions for step lifecycle events live here.

## What Does NOT Belong Here
Telemetry collectors, pipeline logic, or resolution steps. Those belong in their own domains.

### For Humans: What This Means
Just the event data, not the code that listens or emits.

## How Files Collaborate
`StepStarted`, `StepSucceeded`, and `StepFailed` carry context for telemetry and debugging; telemetry collectors consume them, and pipeline steps/runner emit them.

### For Humans: What This Means
The pipeline fires these events, and your monitoring tools read them.
