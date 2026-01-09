# EnvironmentLoaderInterface

## Quick Summary
- This file defines a contract for loading environment-specific configuration during bootstrap.
- It exists so environment config loading can be swapped (dotenv, config files, remote services).
- It removes the complexity of hardcoding one configuration source into the bootstrap process.

### For Humans: What This Means
It’s the “how do we learn which environment we’re in and what settings apply?” interface.

## Terminology (MANDATORY, EXPANSIVE)
- **Environment**: A deployment context (dev/staging/prod).
  - In this file: environment influences what config is loaded.
  - Why it matters: different environments require different behavior.
- **Configuration source**: Where config values come from.
  - In this file: implementations can choose env vars, files, remote services.
  - Why it matters: config storage varies per organization.
- **Bootstrap**: Startup phase where container/app is assembled.
  - In this file: environment config is expected to be loaded during bootstrap.
  - Why it matters: bootstrap needs consistent configuration.

### For Humans: What This Means
This is the seam that lets you plug in “your way of loading config” without rewriting container boot code.

## Think of It
Think of it like a weather report provider. The app asks “what’s the weather?” and doesn’t care if it came from a website or a sensor.

### For Humans: What This Means
Your bootstrap asks “what settings do we use?” and doesn’t care where they came from.

## Story Example
In local dev you load `.env` file and return an array. In production you load from environment variables or a config server. Both implement `EnvironmentLoaderInterface`, so bootstrap code stays the same.

### For Humans: What This Means
You can keep one bootstrap flow across environments.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

Implement `loadEnvironment()` and return an array.

## How It Works (Technical)
The interface defines `loadEnvironment(): array`. Implementations should validate and throw a `RuntimeException` when config is missing/invalid, or provide sensible defaults for optional keys (depending on policy).

### For Humans: What This Means
It’s one method that returns “all the settings we need”.

## Architecture Role
- Why it lives here: environment loading is part of boot.
- What depends on it: bootstrappers that support environment-driven config.
- What it depends on: nothing; it’s a pure contract.
- System-level reasoning: externalizing config loading reduces framework lock-in.

### For Humans: What This Means
It’s easier to integrate with your organization’s config practices.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: loadEnvironment()

#### Technical Explanation
Loads and returns environment configuration as an associative array.

##### For Humans: What This Means
“Give me the settings for this environment.”

##### Parameters
- None.

##### Returns
- `array<string, mixed>`

##### Throws
- `RuntimeException` when config can’t be loaded or validated.

##### When to Use It
- During bootstrap before you register services that depend on config.

##### Common Mistakes
- Returning inconsistent shapes across environments, causing runtime surprises.

## Risks, Trade-offs & Recommended Practices
- Risk: Silent defaults hide missing config.
  - Why it matters: missing config can become security or correctness issues.
  - Design stance: be strict in production, flexible in development.
  - Recommended practice: validate required keys and log/alert on missing configuration.

### For Humans: What This Means
Defaults are helpful, but only if you still notice when something important is missing.

## Related Files & Folders
- `docs_md/Features/Operate/Config/index.md`: Operational configuration objects used during bootstrap.

### For Humans: What This Means
Environment loading gives you raw settings; config DTOs give you structured settings.

