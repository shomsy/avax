# DependencyResolver

## Quick Summary

- This file defines the logic for "Filling in the Blanks" of a class constructor or a method.
- It exists to automate the process of finding the objects and values that a specific task requires to run.
- It removes the complexity of manual dependency management by following a strict priority list to find the best value
  for every parameter.

### For Humans: What This Means (Summary)

This is the **Sourcing Agent** for the container. When the container decides to build a class, the Resolver is the one
that looks at the "Shopping List" (the constructor parameters) and goes out to find everything on that list—checking
your bag first, then the store, then the clearance rack.

## Terminology (MANDATORY, EXPANSIVE)

- **Parameter Prototype**: A data-only description of what a specific variable needs (e.g., "Must be a string", "
  Defaults to 'localhost'").
    - In this file: The `$parameter` object.
    - Why it matters: It gives the Resolver a clear "Instruction Set" for every single argument.
- **Explicit Override**: A value provided by the developer during a `make()` or `call()` request.
    - In this file: Checked via the `$overrides` array.
    - Why it matters: This is your "Override Switch"—it lets you force a specific value even if the container thinks it
      knows of a better one.
- **Context-Aware Resolution**: Resolving a dependency while remembering "Who asked for it".
    - In this file: The `resolveContext()` call.
    - Why it matters: Essential for preventing "Infinite Loops" (e.g., A needs B, and B needs A). It also allows
      contextual bindings to function correctly deep within an object graph.
- **Fallbacks (Default/Null)**: What to do when no value can be found in the container.
    - In this file: `hasDefault` and `allowsNull` checks.
    - Why it matters: Prevents the app from crashing if a parameter isn't strictly necessary.

### For Humans: What This Means (Terminology)

The Resolver uses a **Prototype** (Blueprint) to handle **Overrides** (Manual settings), **Context** (Memory of the
task), and **Fallbacks** (Safety nets) to make sure every requirement is met.

## Think of It

Think of an **Actor preparing for a scene**:

- **The Scene**: A constructor or method.
- **The Script**: The Parameter Prototypes.
- **Your Own Props**: Overrides. If you brought your own sword, you use that.
- **The Prop Room**: The Container. If you didn't bring a sword, you ask the Prop Room for one.
- **Emergency Backup**: If the store is out of swords, you check if the script says "You can just use a stick" (Default)
  or "You don't actually need a sword" (Nullable).

### For Humans: What This Means (Analogy)

The Resolver is the "Prop Master" who makes sure the actor has everything they need before the director shouts "
Action!".

## Story Example

You have a class `DatabaseLogger` that needs a `Connection` object and a `string $tableName`. You register the
`Connection` in the container, but you want to provide the table name manually. You call
`$container->make(DatabaseLogger::class, ['tableName' => 'audit_logs'])`. The **DependencyResolver** kicks in. It sees
`Connection` is a class, so it asks the container to build it. It sees `tableName` is in your manual list, so it uses '
audit_logs'. It combines them and hands them to the engine to build your logger.

### For Humans: What This Means (Story)

It allows you to mix "Automatic" stuff (like complex database objects) with "Manual" stuff (like simple strings) in the
same class without any extra code.

## For Dummies

Imagine you're making a cake.

1. **Check your counter**: Do you have any ingredients already out? (`Overrides`)
2. **Check the fridge**: Is there an ingredient in the fridge that matches what the recipe requires? (
   `Container Resolution`)
3. **Check the pantry**: Is there a "Backup" ingredient mentioned in the recipe book? (`Default Values`)
4. **Give up?**: If you can't find it, is it okay to leave it out? (`Nullable`)
5. **Fail**: If you still don't have it and it's required, you can't make the cake. (`ServiceNotFoundException`)

### For Humans: What This Means (Walkthrough)

It’s a "First-Match-Wins" system. Once it finds a valid value, it stops looking and moves to the next ingredient.

## How It Works (Technical)

The `DependencyResolver` implements a 5-step priority strategy for every parameter:

1. **Override Check**: If the parameter name exists in the provided `$overrides` array, that value is returned
   immediately.
2. **Type Resolution**: If the parameter has a resolvable type (Class/Interface), it's looked up in the container. If a
   `KernelContext` is present, it uses `resolveContext()` to maintain the resolution chain (important for circular
   dependency detection).
3. **Default Values**: If no type match is found (or it's a simple string/int), it checks if the parameter has a
   hard-coded default value.
4. **Nullability**: If all else fails, it checks if the parameter is explicitly marked as `?Type` or `null`.
5. **Exception**: If none of the above work, it throws a `ServiceNotFoundException`.

### For Humans: What This Means (Technical)

It protects your app from "Missing Piece" errors by constantly looking for safe alternatives before finally giving up
and throwing an error.

## Architecture Role

- **Lives in**: `Features/Actions/Resolve`
- **Role**: Argument Fulfillment logic.
- **Collaborator**: Used by `Engine`, `Instantiator`, and `InvocationExecutor`.

### For Humans: What This Means (Architecture)

It is the "Utility Specialist" for anything related to filling parameters.

## Methods

### Method: resolveParameters(...)

#### Technical Explanation: resolveParameters

Orchestrates the resolution of an entire list of parameters. It returns an indexed array where each value corresponds to
the correct position for the method call.

#### For Humans: What This Means

"Get me EVERYTHING on the list in the right order."

### Method: resolveParameter(...)

#### Technical Explanation: resolveParameter

The internal heart of the class. It performs the 5-step priority check for a single item.

#### For Humans: What This Means

"Handle one specific requirement."

## Risks & Trade-offs

- **Naming Sensitivity**: Overrides are matched by **Name** (e.g., `['db' => ...]`). If you rename the variable in your
  PHP class from `$db` to `$connection`, your overrides will stop working and you'll get an error.
- **Circular Dependencies**: If Class A needs Class B, and Class B needs Class A, the Resolver will keep going in
  circles forever unless it's given a `KernelContext` to track its progress and "Guard" against loops.

### For Humans: What This Means (Risks)

Always name your variables carefully and consistently. And ALWAYS make sure you're using the "Smart" version of the
container that tracks its path, so it doesn't get stuck in an infinite loop.

## Related Files & Folders

- `ParameterPrototype.php`: The "Question" that the Resolver tries to answer.
- `Engine.php`: The "Boss" who calls this resolver.
- `Instantiator.php`: The "Builder" who uses the results from this resolver to call `new Class()`.

### For Humans: What This Means (Relationships)

The **Prototype** describes the need, the **Resolver** finds the value, and the **Instantiator** uses the value.
