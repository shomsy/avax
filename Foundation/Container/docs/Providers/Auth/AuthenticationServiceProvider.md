# AuthenticationServiceProvider

## Quick Summary

- This file registers authentication services (hasher, rate limiter, identity, authenticator) into the container.
- It exists so “auth wiring” is centralized and bootstrapped consistently.
- It removes the complexity of manually constructing authentication dependencies across the app.

### For Humans: What This Means (Summary)

It installs the “login/auth toolbox” into the container so you can just ask for `AuthInterface` (or `'auth'`) and get a
working authenticator.

## Terminology (MANDATORY, EXPANSIVE)

- **Authenticator**: The service that performs authentication operations.
    - In this file: bound to `AuthInterface` and to itself.
    - Why it matters: it’s the main API you’ll use for auth checks.
- **Identity**: The representation of the current authenticated user/session identity.
    - In this file: `IdentityInterface` is bound to `SessionIdentity`.
    - Why it matters: it defines where identity is stored and how it’s retrieved.
- **Password hasher**: Adapter used to hash/verify passwords.
    - In this file: `PasswordHasher` is registered as a singleton.
    - Why it matters: consistent hashing is security-critical.
- **Rate limiter**: Adapter used to limit repeated attempts.
    - In this file: `RateLimiter` is registered as a singleton.
    - Why it matters: it reduces brute-force risk.
- **Alias (`'auth'`)**: A string service id that resolves to the auth contract.
    - In this file: `'auth'` resolves to `AuthInterface`.
    - Why it matters: convenience and compatibility for call sites.

### For Humans: What This Means (Terms)

This provider decides “what auth is” in your app: which identity storage, which hasher, and which authenticator.

## Think of It

Think of it like setting up the locks and keys for a building: you pick the lock type (hasher), the rules for repeated
attempts (rate limiter), and who holds the keys (identity).

### For Humans: What This Means (Think)

If auth is inconsistent, your whole app feels unsafe. This provider makes it consistent.

## Story Example

Your controllers typehint `AuthInterface`. You don’t want each controller to know about sessions, hashing, or rate
limiting. With this provider, the container resolves `AuthInterface` to `Authenticator`, which in turn depends on the
registered adapters.

### For Humans: What This Means (Story)

Your app code stays simple: “ask for auth”, not “build auth”.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)

If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code
with confidence.

1. Provider runs during boot.
2. It registers security-sensitive adapters as singletons.
3. It binds the main auth contract.
4. It adds `'auth'` as a friendly alias.

## How It Works (Technical)

`register()` binds:

- `PasswordHasher` and `RateLimiter` as singletons.
- `IdentityInterface` → `SessionIdentity`.
- `AuthInterface` → `Authenticator` and `Authenticator` → itself.
- `'auth'` alias → `AuthInterface`.

### For Humans: What This Means (How)

It wires all the “auth parts” together and gives you one main interface to use.

## Architecture Role

- Why this file lives in `Providers/Auth`: it’s auth infrastructure wiring.
- What depends on it: any code that needs authentication.
- What it depends on: session identity, hashing, rate limiting, and the base provider system.
- System-level reasoning: centralizing auth wiring reduces security drift.

### For Humans: What This Means (Role)

Security is where “small inconsistencies” become big problems. Central wiring helps prevent that.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: register(…)

#### Technical Explanation (register)

Registers authentication services and aliases into the container.

##### For Humans: What This Means (register)

It installs everything the app needs for authentication.

##### Parameters (register)

- None.

##### Returns (register)

- Returns nothing.

##### Throws (register)

- No explicit exceptions.

##### When to Use It (register)

- Called by your bootstrap sequence.

##### Common Mistakes (register)

- Running this provider before session provider if your identity depends on sessions.

## Risks, Trade-offs & Recommended Practices

- Risk: Provider ordering.
    - Why it matters: identity implementations often depend on session services.
    - Design stance: run HTTP/session providers before auth providers when needed.
    - Recommended practice: define a clear bootstrap order and keep it consistent.

### For Humans: What This Means (Risks)

Auth depends on foundations like sessions. Build the foundation first.

## Related Files & Folders

- `docs_md/Providers/Auth/index.md`: Auth providers chapter.
- `docs_md/Providers/HTTP/SessionServiceProvider.md`: Often required by session-backed identity.

### For Humans: What This Means (Related)

Auth and sessions often go hand-in-hand. Make sure both are installed.

