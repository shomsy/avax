# TerminateContainer

## Quick Summary

- This file defines the final cleanup action for the container's runtime lifecycle.
- It exists to provide a standardized way to wipe the container's memory and ensure that no data leaks between
  operational cycles.
- It removes the complexity of manual memory management by delegating the reset logic to the `ScopeManager`.

### For Humans: What This Means (Summary)

This is the **Emergency Stop and Reset** button for your application. When you're done with a task, you call this to
make sure the container forgets everything about what just happened, leaving it clean and ready for the next job.

## Terminology (MANDATORY, EXPANSIVE)

- **Termination Sequence**: The process of clearing all volatile state from the container.
    - In this file: Triggered by the `__invoke()` method.
    - Why it matters: Prevents "Stale Data" from being reused in the next request.
- **Graceful Shutdown**: Cleaning up resources (closing connections, freeing RAM) before the script finishes.
    - In this file: Delegates to `manager->terminate()`.
    - Why it matters: Ensures that your server doesn't hit memory limits during a long-running day of work.
- **Invokable Action**: A class that can be called like a function (using the `__invoke` magic method).
    - In this file: `TerminateContainer` is invokable.
    - Why it matters: It allows the container to be used as a simple callback in shutdown events or middleware stacks.
- **Scope Purging**: Specifically removing instances stored in the `ScopeRegistry`.
    - In this file: Managed via the `ScopeManager`.
    - Why it matters: It ensures that singletons and scoped services are all released.

### For Humans: What This Means (Terminology)

Termination is all about **Clean Slate** logic. It uses a **Purge** (Cleanup) to make sure you have a **Fresh Start** (
Reset).

## Think of It

Think of a **Restaurant Table**:

- **Resolution**: Bringing a customer their food.
- **Scope**: The individual table's settings.
- **TerminateContainer**: The "Busser" (Cleanup crew). They clear all the dishes, wipe the table, and put out new
  napkins so the next guest sees a completely fresh table.

### For Humans: What This Means (Analogy)

The Terminator doesn't care what was served on the table; it just makes sure the table is perfectly empty and clean for
the next person.

## Story Example

You have a PHP worker that processes messages from a queue. The worker stays alive for 24 hours. Without
`TerminateContainer`, every time it processes a message, it might build a `LargeReportGenerator` and keep it in memory.
After 1,000 messages, the worker crashes because it ran out of memory. If you add `TerminateContainer` at the end of
every message loop, the memory is cleared every single time, and the worker can run forever without any issues.

### For Humans: What This Means (Story)

It turns a "Leaky App" into a "Stable App" by forcing a cleanup after every important task.

## For Dummies

Imagine you're playing a game of Sudoku.

1. **Play**: You fill in the numbers (Resolution/Caching).
2. **Done**: You finish the puzzle.
3. **Terminate**: You use a giant eraser to clear every single square so you can start a new game tomorrow.

### For Humans: What This Means (Walkthrough)

If you're using a tool like Swoole, RoadRunner, or a simple long-running command-line loop, you MUST use this to keep
your app healthy.

## How It Works (Technical)

`TerminateContainer` is a simple orchestrator. It holds a reference to the `ScopeManager` via its constructor. When the
`__invoke()` method is called (often by a Kernel event or a manual trigger), it calls `terminate()` on the manager. The
manager then tells the registry to empty its internal singleton and scope arrays. Because it is `readonly` and final, it
is high-performance and thread-safe for reading.

### For Humans: What This Means (Technical)

It’s a "Single-Purpose Tool". Its only job is to shout "Reset!" at the storage layer.

## Architecture Role

- **Lives in**: `Features/Operate/Shutdown`
- **Role**: Cleanup Orchestrator.
- **Dependency**: `ScopeManager`.
- **Consumer**: Triggered at the end of the `Application::run()` or by a `WorkerKernel`.

### For Humans: What This Means (Architecture)

It is the "Final Chapter" of the container's lifecycle.

## Methods

### Method: __construct(ScopeManager $manager)

#### Technical Explanation: __construct

Standard dependency injection of the scope facade.

#### For Humans: What This Means

Assigns a "Cleanup Crew" (Manager) to the termination task.

### Method: __invoke()

#### Technical Explanation: __invoke

The execution point. It triggers the full memory reset.

#### For Humans: What This Means

Pressing the "Reset" button.

## Risks & Trade-offs

- **Re-Resolution Cost**: After termination, the next time you ask for a singleton (like a Logger), it must be built
  again. This costs a tiny bit of CPU time.
- **Connection Loss**: If your "Database Connection" is a singleton and you terminate the container, that connection
  might be closed or lost (depending on how the connection object handles its destructor).

### For Humans: What This Means (Risks)

Don't use it in the middle of a request! Only use it at the very, very end, when you are 100% sure you don't need any of
the current objects anymore.

## Related Files & Folders

- `ScopeManager.php`: The tool that does the actual erasing.
- `Application.php`: The class that usually triggers termination.
- `ScopeRegistry.php`: The actual place where memory is stored.

### For Humans: What This Means (Relationships)

If the **Manager** is the hand holding the eraser, and the **Registry** is the whiteboard, the **TerminateContainer** is
the brain that says "Okay, it's time to erase now."
