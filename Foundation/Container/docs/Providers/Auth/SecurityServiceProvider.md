# SecurityServiceProvider

## Quick Summary

- This file registers security-related HTTP services (CSRF token manager) into the container.
- It exists so CSRF protection is wired consistently and can depend on session and logging.
- It removes the complexity of manually building CSRF tooling in controllers/middleware.

### For Humans: What This Means (Summary)

It installs “CSRF protection support” so other parts of your app can just request it.

## Terminology (MANDATORY, EXPANSIVE)

- **CSRF**: Cross-Site Request Forgery; an attack where a user is tricked into submitting unwanted actions.
    - In this file: `CsrfTokenManager` is registered.
    - Why it matters: CSRF protection is a standard web security requirement.
- **Token manager**: A component that creates and validates CSRF tokens.
    - In this file: constructed with `Session` and `LoggerInterface`.
    - Why it matters: tokens must be stored/validated reliably.
- **Session**: Per-user storage across requests.
    - In this file: used as CSRF token storage.
    - Why it matters: CSRF tokens are usually bound to a session.
- **Logger**: A service for recording events.
    - In this file: used for security-related logging/diagnostics.
    - Why it matters: security visibility helps debugging and auditing.

### For Humans: What This Means (Terms)

This provider sets up the “anti-forgery” system that helps protect your forms and state-changing requests.

## Think of It

Think of CSRF tokens like a wristband at an event. Without the wristband, anyone can walk in and pretend they belong.

### For Humans: What This Means (Think)

CSRF protection is how your app checks “did this request come from a real user flow?”

## Story Example

Your form middleware needs a CSRF token manager. Instead of building it manually and figuring out storage/logging, it
asks the container for `CsrfTokenManager`. This provider ensures it exists and is constructed with the correct
dependencies.

### For Humans: What This Means (Story)

You get security features without repeating setup code.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)

If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code
with confidence.

1. Provider runs during boot.
2. It registers `CsrfTokenManager` as a singleton.
3. The manager uses session to store tokens and logger to report issues.

## How It Works (Technical)

`register()` binds `CsrfTokenManager` to a closure that constructs it using `Session` and `LoggerInterface` resolved
from the container.

### For Humans: What This Means (How)

It’s “create the CSRF manager with the right tools” in one place.

## Architecture Role

- Why this file lives in `Providers/Auth`: security wiring often belongs next to auth wiring.
- What depends on it: CSRF middleware and form helpers.
- What it depends on: session and logging providers.
- System-level reasoning: security services should be centrally wired and visible.

### For Humans: What This Means (Role)

Security wiring should be explicit, not hidden across controllers.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: register(…)

#### Technical Explanation (register)

Registers the CSRF token manager singleton with session and logger dependencies.

##### For Humans: What This Means (register)

It installs CSRF protection support into the container.

##### Parameters (register)

- None.

##### Returns (register)

- Returns nothing.

##### Throws (register)

- Depends on session/logger availability and CSRF manager constructor behavior.

##### When to Use It (register)

- Called during bootstrap.

##### Common Mistakes (register)

- Running before session/logging providers, causing missing dependencies.

## Risks, Trade-offs & Recommended Practices

- Risk: Missing session dependency.
    - Why it matters: tokens usually need somewhere to live.
    - Design stance: CSRF is an HTTP/session concern.
    - Recommended practice: bootstrap session + logging before security provider.

### For Humans: What This Means (Risks)

CSRF needs memory (session) and visibility (logging). Install those first.

## Related Files & Folders

- `docs_md/Providers/HTTP/SessionServiceProvider.md`: Provides `Session`.
- `docs_md/Providers/Core/LoggingServiceProvider.md`: Provides `LoggerInterface`.

### For Humans: What This Means (Related)

Security is built on top of session storage and good logging.

