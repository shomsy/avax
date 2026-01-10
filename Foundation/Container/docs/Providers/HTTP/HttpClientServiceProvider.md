# HttpClientServiceProvider

## Quick Summary
- This file registers an HTTP client and its retry middleware into the container.
- It exists so outbound HTTP calls are wired consistently with logging and retry behavior.
- It removes the complexity of manually constructing HTTP clients in every service.

### For Humans: What This Means (Summary)
It installs a “ready-to-use HTTP client” that already knows how to retry and log.

## Terminology (MANDATORY, EXPANSIVE)
- **HTTP client**: A service used to make outbound HTTP requests.
  - In this file: `HttpClient` is constructed and registered.
  - Why it matters: lots of services need outbound calls (APIs, webhooks).
- **Retry middleware**: A component that retries failed requests.
  - In this file: `RetryMiddleware` is built with `maxRetries: 3`.
  - Why it matters: network failures happen; retries can improve resilience.
- **Logger**: Logging dependency for diagnostics.
  - In this file: used by middleware and client.
  - Why it matters: visibility into retries and failures is crucial.

### For Humans: What This Means (Terms)
It makes outbound calls more reliable and more debuggable by default.

## Think of It
Think of it like calling a friend with bad reception. The retry middleware is you saying “I’ll call again if the line drops”.

### For Humans: What This Means (Think)
Retries can turn flaky networks into stable behavior (when used carefully).

## Story Example
Your payment service calls an external gateway. Instead of creating an HTTP client each time, it asks the container for `HttpClient`. This provider makes sure the client exists and uses retry logic with logging.

### For Humans: What This Means (Story)
Your business service stays focused on payments, not on HTTP plumbing.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. Provider runs during boot.
2. It registers `RetryMiddleware` with logger + retry count.
3. It registers `HttpClient` with the middleware and logger.

## How It Works (Technical)
`register()` binds `RetryMiddleware` using a closure that resolves `LoggerInterface`. It then binds `HttpClient` using a closure that resolves the middleware and logger.

### For Humans: What This Means (How)
It wires the client so you don’t have to pass logger and retry settings around.

## Architecture Role
- Why this file lives in `Providers/HTTP`: outbound HTTP is part of HTTP infrastructure.
- What depends on it: any service making external calls.
- What it depends on: logging provider.
- System-level reasoning: centralized client wiring enforces consistent resilience policies.

### For Humans: What This Means (Role)
When every team builds its own client, you get inconsistent behavior. This keeps it consistent.

## Methods 


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: register(…)

#### Technical Explanation (register)
Registers retry middleware and the HTTP client as container singletons.

##### For Humans: What This Means (register)
It installs a robust outbound HTTP client into the container.

##### Parameters (register)
- None.

##### Returns (register)
- Returns nothing.

##### Throws (register)
- Depends on logger availability and HTTP client constructors.

##### When to Use It (register)
- Bootstrap before services that make outbound calls.

##### Common Mistakes (register)
- Overusing retries without backoff policies (depends on middleware behavior).

## Risks, Trade-offs & Recommended Practices
- Risk: Retries can amplify load or delay failures.
  - Why it matters: retry storms can make outages worse.
  - Design stance: retries are useful but must be controlled.
  - Recommended practice: configure retries thoughtfully (max retries, backoff, idempotency awareness).

### For Humans: What This Means (Risks)
Retrying is helpful, but “retry everything forever” is dangerous.

## Related Files & Folders
- `docs_md/Providers/Core/LoggingServiceProvider.md`: Supplies `LoggerInterface`.

### For Humans: What This Means (Related)
Reliable outbound HTTP depends on good logging.

