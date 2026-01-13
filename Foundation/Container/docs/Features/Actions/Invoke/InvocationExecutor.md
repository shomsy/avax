# InvocationExecutor

## Quick Summary

- This file defines the core logic for "Calling things with dependencies" (Action Injection).
- It exists to allow developers to execute any PHP callable (closures, methods, invokables) and have the container
  automatically fill in the arguments based on type-hints.
- It removes the complexity of manually managing method parameters and resolving object instances for "Class@method"
  style calls.

### For Humans: What This Means (Summary)

This is the **Function Runner** of the container. While other parts of the container build objects, this part **Runs**
things. It is the magic behind features like "Controller Injection", where you just define a method and the container "
Knows" exactly what objects to pass to it when it runs.

## Terminology (MANDATORY, EXPANSIVE)

- **Target Normalization**: Converting different ways of describing a "Task" (like a string `"App\Controller@index"`)
  into a format PHP can actually execute (like `[$controllerInstance, "index"]`).
    - In this file: The `normalizeTarget()` method.
    - Why it matters: It allows the container to handle many different syntaxes so the developer doesn't have to think
      about them.
- **Reflection Cache**: A high-speed memory area that stores the "Blueprint" of a function after the first time it’s
  analyzed.
    - In this file: The `ReflectionCache` collaborator.
    - Why it matters: Analyzing functions is slow. By caching the results, the container can run the same function
      thousands of times per second with almost zero overhead.
- **Invocation Context**: A data-object that tracks everything about a single custom "Call" (What is being called, what
  were the results, etc.).
    - In this file: The `InvocationContext` class.
    - Why it matters: Keeps the execution logic "Stateless"—the executor just follows the instructions in the context.
- **Effective Target**: The actual, final PHP callable that will be executed after all normalization is finished.
    - In this file: Accessed via `getEffectiveTarget()`.
    - Why it matters: This is the "Truth"—it's the exact function or method pair that PHP will run.

### For Humans: What This Means (Terminology)

The Executor uses **Normalization** (Translation) to find the **Effective Target** (The Task), uses a **Cache** (Memory)
for speed, and tracks everything in a **Context** (Job sheet).

## Think of It

Think of a **Universal Remocon (Remote Control)**:

- **Buttons**: The different callables (Closures, Methods, Strings).
- **Signal**: The `InvocationContext`.
- **Normalization**: The remote "Translating" your button press into a specific infra-red code for the TV.
- **Executor**: The electronics inside the remote that actually fire the laser.
- **Parameters**: The "Batteries" and "Settings" required for the TV to react.

### For Humans: What This Means (Analogy)

The remote (Executor) takes a "Simple Input" (Your Button Press) and handles all the "Complex Output" (Sending the right
code with the right settings) so the user doesn't have to.

## Story Example

You have a `Route`. When a user visits `/profile`, you want to run `ProfileController@show`. You pass this string to the
container's `call()` method. The **InvocationExecutor** receives the string. It splits it, asks the container to build
the `ProfileController`, and then looks at the `show()` method. It sees the method needs a `User` object and a
`Response` object. It fetches those from the container and then finally executes the method. Your route logic is 1 line
long, but the Executor did 10 steps of work for you.

### For Humans: What This Means (Story)

It’s what allows for "Clean Controllers" and "Simple Routing". You just define what you need in the parameters, and the
Executor makes it happen.

## For Dummies

Imagine you're hiring a contractor to fix your sink.

1. **Normalization**: You tell the agency you need "Plumbing Services". They translate that into a specific person (
   Contractor).
2. **Preparation**: The agency checks what tools the contractor needs (Wrench, Pipes).
3. **Sourcing**: The agency gets those tools for the contractor. (`Dependency Resolution`)
4. **Execution**: The contractor actually fixes the sink. (`Invoke`)
5. **Result**: You have a fixed sink, and you never had to find a wrench yourself.

### For Humans: What This Means (Walkthrough)

It's a 5-step "Plan -> Prepare -> Fetch -> Work -> Done" process that hides all the complexity from you.

## How It Works (Technical)

The `InvocationExecutor` follows an 8-step execution pipeline:

1. **Normalization**: It checks if the target is a "Class@method" string. If so, it resolves the class from the
   container and converts the string into a standard PHP callable array `[$instance, $method]`.
2. **Reflection**: It creates a `ReflectionFunction` or `ReflectionMethod` object. It checks the internal
   `ReflectionCache` first to avoid re-calculating this.
3. **Prototype Generation**: It maps the native PHP `ReflectionParameter` objects into container-aware
   `ParameterPrototype` objects.
4. **Context Creation**: It creates a new `KernelContext` for this specific call, linking it to any parent context for
   circular dependency protection.
5. **Resolution**: It uses the `DependencyResolver` to fill the argument list, matching overrides by name and types by
   container lookup.
6. **Binding Check**: If it’s a method call, it determines if the method is static or requires an object instance (the "
   This" pointer).
7. **Execution**: It calls `invokeArgs()` on the reflection object, passing in the resolved arguments.
8. **Output**: It returns the result of the method/closure back to the caller.

### For Humans: What This Means (Technical)

It is a highly optimized engine that bridges the gap between "Strings/Closures" and "Physical PHP Code". It handles all
the edge cases (static vs instance, union types, etc.) automatically.

## Architecture Role

- **Lives in**: `Features/Actions/Invoke`
- **Role**: Callable Invocation and Argument Sourcing.
- **Dependency**: `DependencyResolver`, `ReflectionCache`.

### For Humans: What This Means (Architecture)

It is the "Verb Execution" specialist of the container.

## Methods

### Method: execute(InvocationContext $context, array $parameters)

#### Technical Explanation: execute

The primary entry point. It orchestrates the entire normalize -> resolve -> invoke flow.

#### For Humans: What This Means

"Run this task and find its dependencies."

### Method: normalizeTarget(InvocationContext $context)

#### Technical Explanation: normalizeTarget

Handles the translation of "Proxy" targets like `Class@method` into executable pairs.

#### For Humans: What This Means

"Translate the instruction into something PHP understands."

### Method: getReflection(mixed $target)

#### Technical Explanation: getReflection

Manages the retrieval and caching of reflection objects.

#### For Humans: What This Means

"Look up the instructions in memory."

## Risks & Trade-offs

- **Security**: Because the executor can resolve and call almost anything, never pass raw USER INPUT (like a string from
  a URL) directly into the `call()` or `execute()` methods. This could allow an attacker to trigger any method in your
  application.
- **Performance**: While cached, the first call to a complex method with many dependencies will always be slower than a
  direct PHP call.
- **Variadic Parameters**: Support for variadic (`...$args`) parameters can be tricky if mixed with positional
  overrides. The executor attempts to handle these, but keep your callables simple for best results.

### For Humans: What This Means (Risks)

It’s a powerful engine—treat it with respect. Never let users decide what function the container runs, and try to keep
your methods focused with only a few dependencies for maximum speed.

## Related Files & Folders

- `InvokeAction.php`: The high-level action that triggers this executor.
- `InvocationContext.php`: The data packet for a single call.
- `DependencyResolver.php`: The help who finds the arguments for the call.

### For Humans: What This Means (Relationships)

The **Action** uses the **Executor** to process the **Context** and find the **Arguments**.
