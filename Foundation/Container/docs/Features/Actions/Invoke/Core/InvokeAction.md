# InvokeAction

## Quick Summary

- This file serves as the primary "Public Entry Point" for executing any PHP task with dependency injection.
- It exists to provide a clean, simple interface (an "API") for the rest of the application to execute code without
  needing to know implementation details of the executor.
- It removes the complexity of setting up an `InvocationExecutor` by handling the "Wiring" of the container and resolver
  automatically.

### For Humans: What This Means (Summary)

This is the **Receptionist** for the invocation system. When your application wants to run a function but doesn't want
to find the arguments manually, it hands the task to `InvokeAction`. This class then hands it off to the "Specialists" (
The Executor) in the back room to actually do the work.

## Terminology (MANDATORY, EXPANSIVE)

- **Wiring**: The process of connecting a high-level action to its internal engine.
    - In this file: The `wire()` and `setContainer()` methods.
    - Why it matters: Ensures that the "Action" has all the tools it needs (like the container) before it tries to run
      code.
- **Invocation Context**: A Job Sheet that describes exactly what needs to be run.
    - In this file: Specifically the `InvocationContext` object created at the start of `invoke()`.
    - Why it matters: It packages the "Original Target" safely so it can be passed through the execution pipeline.
- **Delegation**: Passing a task from one object to another specialized object.
    - In this file: Passing the call from `InvokeAction` to `InvocationExecutor`.
    - Why it matters: Keeps this class "Leads and Focused" while the executor handles the "Gritty Details".

### For Humans: What This Means (Terminology)

This class handles **Wiring** (Preparation), creates the **Context** (Job sheet), and then performs **Delegation** (
Giving the job to the right person).

## Think of It

Think of a **Personal Home Assistant (like Alexa or Siri)**:

1. **User**: "Run the 'Clean Kitchen' routine."
2. **InvokeAction (The Assistant UI)**: Hears your voice, realizes you want to run a routine, and confirms you have a
   kitchen.
3. **InvocationExecutor (The Smart Home Hub)**: Actually checks the schedule, finds the robot vacuum, checks if the
   lights are on, and starts the cleaning.

### For Humans: What This Means (Analogy)

The assistant (InvokeAction) is the "Friendly Voice" you talk to; the hub (Executor) is the actual machine doing the
deep thinking and connecting to your devices.

## Story Example

You are building a custom command-line tool. When a user types `avax run:task MyTask`, your tool wants to run the
`handle()` method of `MyTask`. It asks the container: `$container->call('MyTask@handle')`. Internally, the container
calls **InvokeAction**. The action sets up the engine, prepares the request for `MyTask@handle`, and triggers the
execution. A second later, your task has run with all its dependencies perfectly filled, and the `InvokeAction` returns
the result to your CLI.

### For Humans: What This Means (Story)

It provides a "Single Point of Failure" and a "Single Point of Success". If you want to change how functions are called
across your entire app, you only have to change it here.

## For Dummies

Imagine you're at a restaurant.

1. **You**: You give your order to the waiter. (`Invoke`)
2. **Waiter**: Checks if the kitchen is open (`executor !== null`) and writes down your order on a slip (
   `InvocationContext`).
3. **Cook**: The waiter hands the slip to the cook. (`executor->execute`).
4. **Meal**: The cook makes the food and the waiter brings it back to you. (`return result`).

### For Humans: What This Means (Walkthrough)

It’s the "Order Taker" that starts the chain reaction of building and running code.

## How It Works (Technical)

The `InvokeAction` operates via a "Lazy Initialization" pattern:

1. **Setup**: It can be initialized with a container immediately, or have one "Wired" in later via `setContainer()`.
2. **Engine Creation**: When a container is provided, it creates a fresh `InvocationExecutor` and "Wires" the container
   and resolver into it.
3. **Invocation**: When `invoke()` is called, it takes the raw target (string or callable), wraps it in an
   `InvocationContext`, and passes it to the executor. It also passes along any manual `$parameters` and the existing
   `$context` to ensure the call stays within the same "Execution Chain" (preventing loops).
4. **Result**: It returns whatever value the executor produced back to the original caller.

### For Humans: What This Means (Technical)

It is a simple wrapper. It doesn't contain "Business Logic" for *how* to call a function; it only contains "
Infrastructure Logic" for *who* should call it.

## Architecture Role

- **Lives in**: `Features/Actions/Invoke/Core`
- **Role**: Front-facing Invocation Controller.
- **Dependency**: `InvocationExecutor`.

### For Humans: What This Means (Architecture)

It is the "Face" of the invocation system.

## Methods

### Method: setContainer(ContainerInternalInterface $container)

#### Technical Explanation: setContainer

Injects the container and triggers the internal "Wiring" of the executor.

#### For Humans: What This Means (setContainer)

"Plugs the assistant into the power outlet."

### Method: invoke(callable|string $target, array $parameters)

#### Technical Explanation: invoke

The primary API method. It transforms the input into an execution context and delegates to the engine.

#### For Humans: What This Means (invoke)

"Take this order and get it done."

## Risks & Trade-offs

- **Initialization**: If you try to call `invoke()` before a container has been wired in, it will throw a
  `RuntimeException`. Always ensure the service is properly initialized during the container's bootstrap phase.
- **Tight Coupling**: This class is tightly coupled to the `InvocationExecutor`. This is intentional, as they are part
  of the same feature-sliced "Vertical," but it means you should treat them as a single unit.

### For Humans: What This Means (Risks)

Make sure the system is "Powered On" before you use it. If you get a "Not Initialized" error, it usually means you
forgot to set the container during your app setup.

## Related Files & Folders

- `InvocationExecutor.php`: The "Engine" that this action controls.
- `InvocationContext.php`: The "Job Sheet" created by this action.
- `ContainerKernel.php`: The high-level kernel that usually calls this action.

### For Humans: What This Means (Relationships)

The **Kernel** calls this **Action**, which uses the **Executor** to run the **Context**.
