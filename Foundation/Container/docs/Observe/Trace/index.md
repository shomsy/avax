# Observe/Trace

## What this folder represents and why it exists

Technical: Trace artifacts for resolution diagnostics (trace payload and observer contract).  
**For Humans: What This Means**: This is where the resolver’s breadcrumb data lives and how you subscribe to it.

## What belongs here

Technical: Trace value objects and observer interfaces tied to resolution diagnostics.  
**For Humans: What This Means**: Only trace-related types go here—payloads and listeners.

## What does NOT belong here

Technical: Metrics sinks, logging adapters, or unrelated observability code.  
**For Humans: What This Means**: Don’t park full logging systems here; just the trace contracts/payloads.

## How files collaborate

Technical: `ResolutionTrace` records stage outcomes; `TraceObserverInterface` consumes traces emitted by the engine.  
**For Humans: What This Means**: Engine writes the trace, observers read it.
