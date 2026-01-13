# ScopeManager

## Quick Summary

- This file defines a high-level manager that provides a clean, fluent API for controlling the container's lifecycle and
  scopes.
- It exists to decouple your application code from the low-level logic of the `ScopeRegistry`.
- It removes the complexity of interacting with internal storage by providing a "Facade" with simple methods like
  `beginScope()`, `endScope()`, and `terminate()`.

### For Humans: What This Means (Summary)

This is the **Remote Control** for the container’s lifespan. Instead of digging into the container’s "Internal Memory" (
the Registry), you use this simple manager to tell the container when a request starts, when it ends, and when to wipe
its memory clean.

## Terminology (MANDATORY, EXPANSIVE)

- **Scope Manager**: The public coordinator for instance lifecycles.
    - In this file: The `ScopeManager` class.
    - Why it matters: It provides a stable "Front Door" for lifecycle operations.
- **Scope Isolation**: Preventing one part of the app from seeing another part's temporary data.
    - In this file: Achieved via `beginScope()` and `endScope()`.
    - Why it matters: Essential for security and correctness (e.g., don't let Request A see Request B's data).
- **Facade**: A design pattern that simplifies a complex system.
    - In this file: `ScopeManager` is a facade for `ScopeRegistry`.
    - Why it matters: It keeps your code clean—you call `manager->beginScope()` instead of dealing with stacks and
      arrays.
- **Instance Proxying**: Passing calls from the manager directly to the storage.
    - In this file: Methods like `has()`, `get()`, and `set()` simply forward to the registry.
    - Why it matters: It allows the internal storage to change without you ever having to update your code.
- **Scoped Runner**: A helper that wraps a callable in begin/end scope.
    - In this file: `run()` opens a scope, executes a callback, then closes it.
    - Why it matters: It keeps scope handling correct and exception-safe.

### For Humans: What This Means (Terminology)

The manager is the **Friendly Face** of the system. It uses **Scopes** (Isolation) to keep things organized, and acts as
a **Facade** (Simplifier) so you don't have to be a genius to use the container's advanced features.

## Think of It

Think of a **Safety Box at a Bank**:

- **ScopeRegistry**: The giant vault in the back with thousands of boxes and security protocols.
- **ScopeManager**: The helpful teller at the window.
- **You**: The application.

### For Humans: What This Means (Analogy)

You don't go into the vault yourself. You talk to the Teller (Manager). You say "I'd like to open a new account" (
`beginScope`), "I want to put this in my box" (`set`), or "I'm closing my account" (`endScope`). The Teller handles all
the boring security and paperwork in the background.

## Story Example

You are building a CLI tool that processes 10,000 CSV rows. Each row needs its own "Database Transaction" and "Logger
Channel". You use `ScopeManager::beginScope()` at the start of every row. You do your work, and then call `endScope()`.
Because you used the manager, the memory for each row is cleaned up immediately, and your CLI tool uses almost no RAM
even after processing thousands of rows.

### For Humans: What This Means (Story)

It gives you a simple way to keep your app's memory clean and "fresh" every time you start a new task.

## For Dummies

Imagine a whiteboard.

1. **Writing**: `set()` adds a note.
2. **Reading**: `get()` reads a note.
3. **New Page**: `beginScope()` puts a transparent plastic sheet over the board so you can write new things without
   ruining the ones underneath.
4. **Erasing the Page**: `endScope()` throws away the plastic sheet.
5. **Cleaning the Board**: `terminate()` wipes the whole board and all the sheets.
6. **Do Work on a Fresh Page**: `run(fn() => ...)` automatically does steps 3 and 4 around your code.

### For Humans: What This Means (Walkthrough)

If you're writing a script that does many things in a loop, use `beginScope` and `endScope` inside the loop. It’s like
turning the page in a notebook so you always have a clean space to work.

## How It Works (Technical)

The `ScopeManager` is a `readonly` class that holds a reference to the `ScopeRegistry`. It doesn't store any data
itself; it purely delegates. It uses the "Proxy" pattern where every public method is essentially a one-liner that calls
the same method on the registry. This separation of concerns allows the container to swap out simple registries for more
complex ones (like Redis-backed or Session-backed) without changing the `ScopeManager` API.

### For Humans: What This Means (Technical)

It’s an "Empty Wrapper". It’s just there to make the API look pretty and to keep the "Brain" (Registry) protected.

### Method: run(callable $callback)

#### Technical Explanation

Opens a scope, executes the callback, and always closes the scope in a finally block, returning the callback result.

#### For Humans: What This Means

“Do this work in a safe scope, and clean up even if it fails.”

#### Parameters

- `callable $callback`: The work to perform inside the scope.

#### Returns

- `mixed`: Whatever the callback returns.

#### Throws

- Any exception from the callback.

#### When to Use It

- When you want guaranteed begin/end scope around a block of code (e.g., per HTTP request or job).

#### Common Mistakes

- Forgetting to ensure dependencies are resolvable inside the scope; assuming it catches exceptions (it only ensures
  scope closure).

## Architecture Role

- **Lives in**: `Features/Operate/Scope`
- **Role**: Public API Facade.
- **Dependency**: `ScopeRegistry`.
- **Consumer**: High-level application code, HTTP Kernels, and CLI Kernels.

### For Humans: What This Means (Architecture)

It is the primary point of contact for anything related to the container's "Now" (current state) and "Future" (
lifecycle).

## Methods

### Method: __construct(ScopeRegistry $registry)

#### Technical Explanation: __construct

Standard dependency injection constructor.

#### For Humans: What This Means (__construct)

Connects the manager to the actual memory storage.

### Method: has(string $abstract)

#### Technical Explanation: has

Delegates existence check to the registry.

#### For Humans: What This Means (has)

Checks if an object is already built and waiting.

### Method: get(string $abstract)

#### Technical Explanation: get

Fetches an existing instance from the storage.

#### For Humans: What This Means (get)

Retrieves the object you asked for from the current scope.

### Method: set(string $abstract, mixed $instance)

#### Technical Explanation: set

Stores an instance into the current layer of the registry.

#### For Humans: What This Means (set)

Saves an object so it can be reused later in the same task.

### Method: instance(string $abstract, mixed $instance)

#### Technical Explanation: instance

An alias for `addSingleton` on the registry, used for forced global registration.

#### For Humans: What This Means (instance)

A "Big Hammer" method to force an object to be saved globally, skipping any temporary tasks.

### Method: beginScope()

#### Technical Explanation: beginScope

Signals the registry to start a new isolation layer.

#### For Humans: What This Means (beginScope)

"Start a new temporary task / page".

### Method: endScope()

#### Technical Explanation: endScope

Signals the registry to discard the current isolation layer.

#### For Humans: What This Means (endScope)

"Finish the current task and throw away its temporary data".

### Method: terminate()

#### Technical Explanation: terminate

Triggers a hard reset of all container instance storage.

#### For Humans: What This Means (terminate)

"Total System Reset". Wipes every single piece of memory in the container.

## Risks & Trade-offs

- **Naming Confusion**: The method `instance()` in this class behaves differently than `set()`. `instance()` is always
  global, while `set()` follows the current scope.
- **Tight Coupling**: Although it's a facade, your app becoming heavily reliant on direct scope manipulation can make it
  harder to test classes in isolation.

### For Humans: What This Means (Risks)

Be careful which button you press! If you use `instance()` when you meant to use `set()`, you might accidentally share
data between customers on your website, which is a big security risk.

## Related Files & Folders

- `ScopeRegistry.php`: The actual engine behind this manager.
- `TerminateContainer.php`: The shutdown action that uses this manager to clean up.

### For Humans: What This Means (Relationships)

If the **Registry** is the hard drive, the **Manager** is the operating system that gives you the buttons to use it.
