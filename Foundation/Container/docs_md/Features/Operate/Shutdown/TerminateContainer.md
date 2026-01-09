# TerminateContainer

## Quick Summary
- This file defines a tiny shutdown action that terminates the active scope via `ScopeManager`.
- It exists to centralize shutdown behavior behind a single invokable class.
- It removes the complexity of “how do I reliably end scopes?” by being a named, injectable action.

### For Humans: What This Means
It’s a one-button “shut down the container’s scope system” action.

## Terminology (MANDATORY, EXPANSIVE)
- **Shutdown action**: A unit of work executed when the container is being terminated.
  - In this file: the action is `__invoke()`.
  - Why it matters: shutdown needs to be explicit and repeatable.
- **Scope**: A boundary for scoped lifetimes (e.g., per request).
  - In this file: termination is delegated to `ScopeManager`.
  - Why it matters: scoped services must be released when the scope ends.
- **ScopeManager**: A wrapper API around scope storage/registry.
  - In this file: it provides `terminate()`.
  - Why it matters: it’s the high-level interface for scope cleanup.
- **Invokable class**: A class with `__invoke()` that can be treated like a callable.
  - In this file: `TerminateContainer` is invokable.
  - Why it matters: it’s easy to plug into pipelines and frameworks.

### For Humans: What This Means
This is a small “cleanup button” you can pass around and call when you’re done.

## Think of It
Think of it like pressing “Stop” on a machine: it doesn’t do work, it ends work safely.

### For Humans: What This Means
It’s the “we’re finished, let go of resources” step.

## Story Example
Your application finishes handling a request. You run a shutdown phase that calls `TerminateContainer`. The action tells the `ScopeManager` to terminate, which clears scoped instances so the next request starts clean.

### For Humans: What This Means
It prevents “request A’s state leaking into request B”.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. Something calls `TerminateContainer` (as a callable).
2. It receives a `ScopeManager`.
3. It calls `$scope->terminate()`.
4. Scoped state is cleared.

## How It Works (Technical)
The class is `readonly` and `final`, exposing a single `__invoke(ScopeManager $scope): void` method. That method delegates to the scope subsystem’s `terminate()` behavior.

### For Humans: What This Means
It’s intentionally boring: it just forwards the call.

## Architecture Role
- Why it lives in this folder: it’s part of the Operate/Shutdown phase.
- What depends on it: shutdown orchestration code (boot/kernel/application layer).
- What it depends on: `ScopeManager`.
- System-level reasoning: naming shutdown actions makes them discoverable and testable.

### For Humans: What This Means
You want shutdown steps to be explicit, not hidden inside random destructors.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __invoke(…)

#### Technical Explanation
Terminates the container’s scope by delegating to `ScopeManager::terminate()`.

##### For Humans: What This Means
It ends the “current scope” and clears scoped instances.

##### Parameters
- `ScopeManager $scope`: The scope controller to terminate.

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- At the end of a request/job scope, or when shutting down the container.

##### Common Mistakes
- Forgetting to terminate scopes, leading to scoped instances persisting longer than intended.

## Risks, Trade-offs & Recommended Practices
- Risk: Not terminating scope leaks memory/state.
  - Why it matters: scoped services can retain references to large object graphs.
  - Design stance: scope termination should be part of your standard lifecycle.
  - Recommended practice: call this from a guaranteed shutdown hook.

### For Humans: What This Means
If you don’t clean up, your app can feel “haunted” by old state.

## Related Files & Folders
- `docs_md/Features/Operate/Scope/ScopeManager.md`: The API this action calls.

### For Humans: What This Means
If you’re curious what “terminate” really does, `ScopeManager` is the next step.

