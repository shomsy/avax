# DebugContainerBootstrapper

## Quick Summary
- This file defines a debug-flavored bootstrapper that enables extra diagnostics via base class configuration.
- It exists so you can create a “debug mode container” without rewriting bootstrap logic.
- It removes the complexity of toggling debug flags across many components by centralizing it in one subclass.

### For Humans: What This Means
It’s the “turn on debug mode” version of the normal bootstrapper.

## Terminology (MANDATORY, EXPANSIVE)
- **Debug mode**: Extra visibility, logging, or strictness during development.
  - In this file: enabled by passing `debug: true` to the parent constructor.
  - Why it matters: dev environments benefit from more diagnostics.
- **Subclass specialization**: Using a subclass to preset configuration.
  - In this file: subclass only changes constructor defaults.
  - Why it matters: it prevents “repeat flags everywhere” code.

### For Humans: What This Means
Instead of remembering which flags to set, you use the debug bootstrapper and you’re done.

## Think of It
Think of it like a “developer edition” of a tool: same tool, but with more gauges and logs.

### For Humans: What This Means
You want more information when you’re building/fixing things.

## Story Example
In local development you want prototype caches in a known folder and you want debug behavior enabled. You construct `new DebugContainerBootstrapper($cacheDir)` and use it to build the container. In production you use the normal bootstrapper.

### For Humans: What This Means
Different environment, different defaults, same underlying assembly logic.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

Use this class when developing. Use the base bootstrapper in production.

## How It Works (Technical)
The constructor forwards to `ContainerBootstrapper` with `debug: true` and no policy. The rest of the behavior is inherited.

### For Humans: What This Means
It’s a preset configuration, not a separate bootstrap implementation.

## Architecture Role
- Why it lives here: it’s a boot-time specialization.
- What depends on it: dev/test bootstrap code.
- What it depends on: `ContainerBootstrapper`.
- System-level reasoning: separate environment defaults reduce accidental production misconfiguration.

### For Humans: What This Means
Having a dedicated debug bootstrapper makes it harder to “accidentally run prod in debug mode”.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(string|null $cacheDir = null)

#### Technical Explanation
Constructs the bootstrapper with debug enabled and an optional cache directory.

##### For Humans: What This Means
It turns on debug mode and optionally chooses where caches should live.

##### Parameters
- `string|null $cacheDir`

##### Returns
- Returns nothing.

##### Throws
- Depends on parent constructor behavior.

##### When to Use It
- Local development and debugging.

##### Common Mistakes
- Assuming it automatically enables logging/telemetry without wiring those providers.

## Risks, Trade-offs & Recommended Practices
- Risk: Debug defaults leaking into production.
  - Why it matters: debug often has performance and security implications.
  - Design stance: keep debug bootstrap usage explicit.
  - Recommended practice: wire production bootstrap in deployment config, not in ad-hoc scripts.

### For Humans: What This Means
Debug is for your laptop, not for your customers.

## Related Files & Folders
- `docs_md/Features/Operate/Boot/ContainerBootstrapper.md`: The base implementation this class specializes.

### For Humans: What This Means
If you want to know what debug mode actually changes, start with the parent bootstrapper.

