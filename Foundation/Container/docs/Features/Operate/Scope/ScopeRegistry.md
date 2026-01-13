# ScopeRegistry

## Quick Summary

- This file serves as the physical memory (storage) for resolved singleton and scoped instances.
- It exists to maintain a stack of "Scopes" that isolate instances based on their lifecycle (e.g., Requests, Sessions).
- It removes the complexity of managing instance lifecycles by providing a managed stack for entering and exiting
  operational boundaries.

### For Humans: What This Means (Summary)

This is the container’s **Instance Warehouse**. When the container builds a singleton or a scoped service, it doesn't
want to build it again later. It stores the finished object here so it can "check it out" later without any extra work.

## Terminology (MANDATORY, EXPANSIVE)

- **Singleton Storage**: A permanent shelf for objects that live as long as the application is running.
    - In this file: The `$singletons` array.
    - Why it matters: It ensures that things like "Database Connections" are created only once and shared by everyone.
- **Scoped Storage**: A temporary box for objects that only live for a short time (like one web request).
    - In this file: The `$scopes` property (a stack of arrays).
    - Why it matters: It allows you to have "Per-Request" services that are shared *during* the request but destroyed
      right after.
- **Scope Stack**: Multiple levels of isolation (e.g., a "Job" scope inside a "Worker" scope).
    - In this file: New scopes are added to the end of the `$scopes` array.
    - Why it matters: It enables complex nested lifecycles where you can start a sub-task and clean up its memory
      without affecting the main app.
- **Termination**: A full "Wipe" of all instances.
    - In this file: The `terminate()` method.
    - Why it matters: Essential for long-running processes (like workers) to prevent "Memory Leaks" by periodically
      cleaning everything out.

### For Humans: What This Means (Terminology)

This registry manages **Permanent Shelves** (Singletons) and **Temporary Boxes** (Scopes). It keeps track of which box
you are currently using and makes sure that when you're done with a box, everything inside it is thrown away properly.

## Think of It

Think of a **Hotel Reception Desk**:

- **Singletons**: The fixed furniture and the building itself (It's always there).
- **Scope Stack**: The Guest Rooms. When a guest checks in (`beginScope`), they get a room.
- **Scoped Instances**: The items in the room (Towels, Mini-bar). They are shared by the guest during the stay.
- **End Scope**: Checkout time. Everything in the room is cleared out for the next guest.

### For Humans: What This Means (Analogy)

The Registry is the system that manages "Rooms" (Scopes) and "Furniture" (Singletons) to make sure guests don't wake up
to find the previous guest's half-eaten sandwich in their bed.

## Story Example

You are running a web server. When a request comes in, you call `beginScope()`. During that request, your code asks for
the `CurrentUser`. The container builds it once and saves it in the `ScopeRegistry`. Ten different classes use that same
`CurrentUser` object. When the response is sent, you call `endScope()`. The `CurrentUser` object is destroyed, so the
*next* request doesn't accidentally think it's still the previous person logged in.

### For Humans: What This Means (Story)

It guarantees that temporary data stays temporary and doesn't "leak" into places it shouldn't be.

## For Dummies

Imagine a desk with drawers.

1. **Top Surface**: Where you keep your computer (Singleton). It stays there all day.
2. **Current Drawer**: Where you put the files for the project you are working on right now (Scope).
3. **Opening a Drawer**: `beginScope()`.
4. **Closing and Emptying a Drawer**: `endScope()`.
5. **Burning the Desk**: `terminate()`.

### For Humans: What This Means (Walkthrough)

If you have a service that should disappear after a specific task is done, you should use a "Scoped" lifetime and the
`ScopeRegistry` will handle the cleanup for you.

## How It Works (Technical)

The `ScopeRegistry` uses a stack-based approach for scopes. When `beginScope()` is called, a new empty array is pushed
onto the `$scopes` stack. All následné `set()` calls write to this top-most array. When `get()` is called, the registry
checks the top scope first, then falls back to the `$singletons` layer. This "Last-In-First-Out" approach ensures that
nested scopes correctly override or isolate data. Crucially, `terminate()` clears both layers completely to prevent
memory bloat in daemonized environments.

### For Humans: What This Means (Technical)

It always looks at its "Current Task" (the top scope) first. If it can't find what it needs there, it looks in its "
Permanent Memory" (Singletons). This makes it very smart about which objects to give you at any given moment.

## Architecture Role

- **Lives in**: `Features/Operate/Scope`
- **Role**: Instance Persistence.
- **Visibility**: Usually hidden behind `ScopeManager` or accessed by the `Engine` during resolution.

### For Humans: What This Means (Architecture)

It is the "RAM" of the container system. It holds the actual objects while the app is running.

## Methods

### Method: has(string $abstract)

#### Technical Explanation: has

Checks for existence in the active scope stack (highest priority) and then the singleton map.

#### For Humans: What This Means (has)

"Do we already have a finished version of this object somewhere in our memory?"

### Method: get(string $abstract)

#### Technical Explanation: get

Retrieves a stored instance using prioritized lookup (Current Scope > Singletons).

#### For Humans: What This Means (get)

Fetches the existing object so the container doesn't have to build a new one from scratch.

### Method: set(string $abstract, mixed $instance)

#### Technical Explanation: set

Writes an instance to the current active layer. If no scope is active, defaults to the singleton layer.

#### For Humans: What This Means (set)

Saves a finished object into the warehouse for later use.

### Method: addSingleton(string $abstract, mixed $instance)

#### Technical Explanation: addSingleton

Bypasses active scopes to write directly to the permanent singleton layer.

#### For Humans: What This Means (addSingleton)

Forces an object to be saved on the "Permanent Shelf", no matter what Task we're currently doing.

### Method: beginScope()

#### Technical Explanation: beginScope

Pushes a new empty array onto the scope stack, designating a new isolation boundary.

#### For Humans: What This Means (beginScope)

"Start a new Task". Creates a fresh temporary box for objects.

### Method: endScope()

#### Technical Explanation: endScope

Pops the top array from the stack, discarding all scoped instances contained within.

#### For Humans: What This Means (endScope)

"Finish the current Task". Throws away everything in the temporary box.

### Method: terminate()

#### Technical Explanation: terminate

Resets all internal storage arrays to empty.

#### For Humans: What This Means (terminate)

The "Emergency Stop / Reset" button. Clears all memory.

## Risks & Trade-offs

- **Memory Leaks**: If you call `beginScope()` but forget to call `endScope()`, the stack will grow forever until the
  app crashes.
- **Race Conditions**: In multi-threaded environments (like Swoole), a single registry shared across requests can cause
  data contamination (this specific implementation is designed for single-threaded lifecycles).

### For Humans: What This Means (Risks)

Always make sure you "Close what you Open". If you start a scope, use a `try...finally` block to make sure it ends, or
your server might run out of RAM!

## Related Files & Folders

- `ScopeManager.php`: The friendly public face of this registry.
- `Engine.php`: The one who asks the registry for instances.
- `TerminateContainer.php`: The cleanup crew that calls `terminate()`.

### For Humans: What This Means (Relationships)

The **Engine** does the work, the **Registry** holds the results, and the **Manager** makes it easy for you to control
the flow.
