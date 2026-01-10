# MethodPrototype

## Quick Summary

- This file defines the "Plan for a Task"—it describes how a single method should be called by the container.
- It exists to group multiple **Parameter Prototypes** under a single method name, providing a complete "Signature" for the container to follow.
- It removes the need for the container to repeatedly analyze a method's parameters at runtime.

### For Humans: What This Means (Summary)

This is the **Job Sheet** for a single task. If the container wants to call your `setDatabase()` method, it looks at this sheet. The sheet says the name of the method and lists every "Ingredient" (Parameter) that needs to be found and passed into that method.

## Terminology (MANDATORY, EXPANSIVE)

- **Method Signature**: The combination of a method's name and its parameters.
  - In this file: Represented by `$name` and `$parameters`.
  - Why it matters: This is the "ID" of the method. It tells the container exactly WHICH task it is performing.
- **Ordered Parameters**: The strict sequence of arguments required by a method.
  - In this file: The `$parameters` array must stay in order.
  - Why it matters: PHP is very strict—if you pass arguments in the wrong order, the code will crash or, worse, do the wrong thing.
- **Composite Model**: A data model made up of other models.
  - In this file: `MethodPrototype` contains an array of `ParameterPrototype` objects.
  - Why it matters: It creates a "Tree" of information that the container can navigate easily.

### For Humans: What This Means (Terminology)

The Method Prototype is a **Composite** (Combined) model that describes the **Method Signature** (The Task) along with its **Ordered Parameters** (The ingredients).

## Think of It

Think of a **Single Recipe Step**:

1. **Task Name**: "Add Dry Ingredients".
2. **Arguments (The Ingredients)**:
    - 2 cups of Flour (Parameter 1).
    - 1 tsp of Salt (Parameter 2).
    - 1 tbsp of Sugar (Parameter 3).

### For Humans: What This Means (Analogy)

The `MethodPrototype` is the description of that one step. It doesn't tell you how to bake the whole cake; it just tells you precisely how to "Add Dry Ingredients".

## Story Example

You have a `Mailer` class. It has a method called `setup(Config $config, Logger $logger)`. When the container prepares to hydrate the `Mailer`, it looks at the **MethodPrototype** for `setup`. It sees there are 2 parameters. It goes to the first one, sees it needs a `Config`, finds one, then moves to the second one, finds a `Logger`, and finally calls the method with both items. Because it used the prototype, it didn't have to look at your source code to figure out those 2 ingredients were needed.

### For Humans: What This Means (Story)

It makes "Setter Injection" fast. You can have many methods marked with `#[Inject]`, and the container will use these "Job Sheets" to fill them all in a fraction of a millisecond.

## For Dummies

Imagine you're a mechanic.

1. **The Job**: "Change Oil Filter". (`$name`)
2. **The Tools**: You need a Wrench and an Oil Pan. (`$parameters`)
3. **The Order**: You must use the Pan first, then the Wrench. (`Array Order`)

### For Humans: What This Means (Walkthrough)

It's a "Task Description". Name + Tools needed + Order of Use.

## How It Works (Technical)

The `MethodPrototype` is a specialized DTO:

1. **Structure**: It holds a string and an array. The array is populated during the `Think` phase (analysis) by the `PrototypeAnalyzer`.
2. **Constructor Injection**: For constructors, the name is always `__construct`. The container uses this prototype to determine the "Shopping List" for the `new` command.
3. **Setter Injection**: For other methods, the name is the actual method name. The container uses this to call `call_user_func_array()` or `$method->invokeArgs()`.
4. **Serialization**: It supports a recursive `toArray()` and `fromArray()` pattern. When you serialize a `ServicePrototype`, it automatically triggers the serialization of every `MethodPrototype` and `ParameterPrototype` inside it.

### For Humans: What This Means (Technical)

It is a "Container of Blueprints". It doesn't do any work itself; it just holds the "Instruction Data" for one specific method so that the `Invoker` or `Instantiator` can do their jobs.

## Architecture Role

- **Lives in**: `Features/Think/Model`
- **Role**: Method-level metadata model.
- **Collaborator**: Contains `ParameterPrototype`, used by `ServicePrototype`.

### For Humans: What This Means (Architecture)

It is the "Middle Tier" of the blueprint system. It’s bigger than a parameter but smaller than a whole class blueprint.

## Methods

### Method: fromArray(array $data)

#### Technical Explanation: fromArray

Rebuilds the method blueprint and all its child parameter blueprints from a raw data map.

#### For Humans: What This Means

"Restore the task instructions from a file."

### Method: toArray()

#### Technical Explanation: toArray

Converts the task description into a simple list that can be saved.

#### For Humans: What This Means

"Save the task instructions for later."

## Risks & Trade-offs

- **Ordering**: If the parameters are added to the list in the wrong order during analysis, the method will be called with the wrong data. The Analyzer must be very careful to maintain the same order as the physical PHP code.
- **Immutability**: You cannot "Add a parameter" to a prototype after it’s built. You have to create a new one.

### For Humans: What This Means (Risks)

"The Order is Critical". If you change the order of your method's variables in your code, you MUST refresh the cache so the prototype's order matches your new code.

## Related Files & Folders

- `ParameterPrototype.php`: The blueprints for the individual arguments.
- `ServicePrototype.php`: The larger manual that contains this method plan.
- `InvocationExecutor.php`: The "Worker" who actually reads this plan and calls the method.

### For Humans: What This Means (Relationships)

The **Method Plan** describes the **Arguments** and is kept inside the **Master Blueprint**.
