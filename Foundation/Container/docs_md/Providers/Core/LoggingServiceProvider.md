# LoggingServiceProvider

## Quick Summary
- This file registers logging and error handling services into the container.
- It exists so your application gets a consistent logger and a global error handler during bootstrap.
- It removes the complexity of “how do we create a logger and install error handling?” by packaging it into one provider.

### For Humans: What This Means
It’s the provider that makes “logging works during bootstrap” true.

## Terminology (MANDATORY, EXPANSIVE)
- **LoggerFactory**: A service that creates configured loggers for a channel.
  - In this file: registered as a singleton.
  - Why it matters: it standardizes logger creation across the app.
- **LoggerInterface (PSR-3)**: The standard logging contract.
  - In this file: bound to a default logger created by the factory.
  - Why it matters: PSR-3 makes loggers swappable and consistent.
- **Error handler**: A component that installs global error/exception handling.
  - In this file: `ErrorHandler` is constructed and later `initialize()` is called in `boot()`.
  - Why it matters: it prevents silent failures and enables consistent reporting.
- **Boot phase**: A stage after registration where you can run initialization logic.
  - In this file: `boot()` installs the handler and emits a log line.
  - Why it matters: some systems must be initialized, not just registered.

### For Humans: What This Means
This provider sets up the “eyes and ears” of your system: logs and error reporting.

## Think of It
Think of it like installing smoke detectors and a security camera system before you move into a building.

### For Humans: What This Means
You want to detect problems early, especially during startup.

## Story Example
During app bootstrap, something fails in a provider. Without logging, you’re blind. With this provider, you have a logger and an error handler installed, so failures are captured and visible.

### For Humans: What This Means
It turns “mysterious crash” into “actionable log”.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. `register()` binds factory, logger, and error handler.
2. `boot()` installs global error handling and logs that bootstrap logging is ready.

## How It Works (Technical)
`register()` binds `LoggerFactory`, binds `LoggerInterface` to a closure that creates a logger for a fixed channel, and binds `ErrorHandler` to a closure that uses the logger. `boot()` retrieves the handler and calls `initialize()`, then logs an info message.

### For Humans: What This Means
It wires the pieces and then flips the “on” switch.

## Architecture Role
- Why this file lives in `Providers/Core`: logging is foundational infrastructure.
- What depends on it: everything that wants to log or handle errors consistently.
- What it depends on: logging library classes and the provider base class.
- System-level reasoning: you want logging ready as early as possible.

### For Humans: What This Means
If something goes wrong early, you still want evidence. That’s why this provider runs early.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: register(…)

#### Technical Explanation
Registers logger factory, default logger binding, and error handler binding.

##### For Humans: What This Means
It installs the logging components into the container.

##### Parameters
- None.

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- Called during bootstrap.

##### Common Mistakes
- Logging inside registration before the logger binding exists.

### Method: boot(…)

#### Technical Explanation
Initializes global error handling and emits a bootstrap log line.

##### For Humans: What This Means
It turns on error handling and proves logging is alive.

##### Parameters
- None.

##### Returns
- Returns nothing.

##### Throws
- Depends on `ErrorHandler::initialize()` and logger implementation behavior.

##### When to Use It
- Called after all providers are registered.

##### Common Mistakes
- Calling boot twice in the same lifecycle and installing handlers multiple times.

## Risks, Trade-offs & Recommended Practices
- Risk: Global error handler side effects.
  - Why it matters: installing global handlers affects the entire process.
  - Design stance: install once, early, and explicitly.
  - Recommended practice: keep boot idempotent or guard against repeated initialization.

### For Humans: What This Means
Global hooks are powerful. Make sure you install them once and intentionally.

## Related Files & Folders
- `docs_md/Providers/Core/index.md`: Core providers chapter.

### For Humans: What This Means
Logging is core infrastructure—treat it like a foundation.

