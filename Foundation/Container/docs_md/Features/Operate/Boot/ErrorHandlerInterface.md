# ErrorHandlerInterface

## Quick Summary
- This file defines a contract for handling bootstrap/runtime errors in a pluggable way.
- It exists so different error strategies can be used (logging-only, alerts, recovery).
- It removes the complexity of hardcoding one error handling mechanism into the container lifecycle.

### For Humans: What This Means
It’s the “what do we do when something goes wrong?” interface.

## Terminology (MANDATORY, EXPANSIVE)
- **Error handling strategy**: How you respond to failures.
  - In this file: implementations decide logging, recovery, escalation.
  - Why it matters: different apps/environments need different reactions.
- **Bootstrap error**: Failure during startup wiring.
  - In this file: mentioned as one of the key scenarios.
  - Why it matters: bootstrap failures can prevent the app from starting at all.
- **Runtime error**: Failure during service resolution or request execution.
  - In this file: `handleError()` covers general operation.
  - Why it matters: runtime errors need visibility and predictable behavior.
- **Context**: Extra information about where the error happened.
  - In this file: optional `object|null $context`.
  - Why it matters: context makes logs/actionable alerts possible.

### For Humans: What This Means
This gives you one place to plug in “log it”, “alert it”, or “try a fallback”.

## Think of It
Think of it like a fire alarm system: sometimes you just log an incident, sometimes you evacuate the building.

### For Humans: What This Means
Not all errors are equal, and you need a consistent way to react.

## Story Example
In development you want verbose logs and stack traces. In production you want structured logs and alerts. Both implement this interface, and your bootstrap uses whichever is configured for the environment.

### For Humans: What This Means
Same boot code, different error behavior.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

Implement `handleError(Throwable $exception, object|null $context = null)`.

## How It Works (Technical)
The interface defines one method. It’s intentionally broad: it accepts a `Throwable` and optional context. Implementations choose whether to log, transform errors, recover, or rethrow (though the signature is void).

### For Humans: What This Means
It’s a “plug point” for your organization’s error handling policies.

## Architecture Role
- Why it lives here: error handling affects boot/runtime lifecycle.
- What depends on it: bootstrappers and kernels that want pluggable error reporting.
- What it depends on: `Throwable`.
- System-level reasoning: pluggable error handling improves observability and resilience.

### For Humans: What This Means
You can upgrade error handling without rewriting the container core.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: handleError(Throwable $exception, object|null $context = null)

#### Technical Explanation
Handles an error/exception that occurred during container lifecycle operations.

##### For Humans: What This Means
“Something went wrong—do whatever we decided is the right response.”

##### Parameters
- `Throwable $exception`: The failure.
- `object|null $context`: Optional context (often container/app).

##### Returns
- Returns nothing.

##### Throws
- No explicit throws; implementations may still throw if they choose to escalate.

##### When to Use It
- During bootstrap, resolution, or request handling when you want centralized error reporting.

##### Common Mistakes
- Swallowing all errors without logging/telemetry, making failures invisible.

## Risks, Trade-offs & Recommended Practices
- Risk: Over-handling can hide failures.
  - Why it matters: apps may continue in broken states.
  - Design stance: recover only when safe; otherwise fail fast with clear logs.
  - Recommended practice: differentiate fatal vs recoverable errors and ensure visibility.

### For Humans: What This Means
Recovering is great, but pretending nothing happened is how bugs become nightmares.

## Related Files & Folders
- `docs_md/Providers/Core/LoggingServiceProvider.md`: Provides logging infrastructure often used by implementations.

### For Humans: What This Means
Good error handling usually starts with good logging.

