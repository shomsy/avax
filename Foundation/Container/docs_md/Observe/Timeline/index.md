# Observe/Timeline

## What This Folder Represents
This folder models “what happened over time” during resolution.

Technically, timelines are structured traces of resolution steps: start, end, success/failure, and any recorded metadata. They’re crucial for debugging because they tell you not only what failed, but *when* and *in which step*.

### For Humans: What This Means
It’s the container’s play-by-play replay. If something goes wrong, you can see the sequence of events.

## What Belongs Here
- Timeline models that record resolution activity.

### For Humans: What This Means
If it’s a trace of container work over time, it lives here.

## What Does NOT Belong Here
- Metric aggregation (that’s `Observe/Metrics`).
- Core resolution execution (that’s `Core/Kernel`).

### For Humans: What This Means
Timeline tells the story; core does the action.

## How Files Collaborate
Kernel steps emit telemetry events. A collector captures them. Timeline models store them so inspectors and debuggers can display the sequence.

### For Humans: What This Means
Events get recorded so you can replay and understand the container’s decisions.

