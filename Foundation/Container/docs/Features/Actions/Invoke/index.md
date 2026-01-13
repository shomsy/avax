# Features/Actions/Invoke

## What This Folder Represents

This folder contains the "Action Engine" of the container—the machinery that allows you to execute any dynamic task (
functions, methods, closures) with full dependency injection.

Technically, `Features/Actions/Invoke` is the "Verbal" side of the container. While other folders focus on "Nouns" (
Objects/Services), this folder focuses on executing logic. It handles the complexity of "Callable Resolution"—taking a
variety of PHP formats (like strings, arrays, or objects) and ensuring they are executed with the correct arguments. It
is the primary tool for features like Route dispatching, Middleware execution, and deferred Task handling.

### For Humans: What This Means (Represent)

This is the **Execution Command Center**. If the container was a computer, this folder would be the **Processor (CPU)**
that actually runs the code you give it.

## Terminology (MANDATORY, EXPANSIVE)

- **Invocation**: The act of "Triggering" or "Running" a piece of code.
    - In this folder: Handled by the `InvocationExecutor`.
    - Why it matters: It turns static definitions into active runtime results.
- **Callable**: Any reference to a piece of code that can be "called" by PHP (e.g., a function name, an anonymous
  function, or an object with an `__invoke` method).
    - In this folder: The target of the `execute()` method.
    - Why it matters: It’s the "What" that defines the task.
- **Normalization**: Converting shorthand descriptions of tasks (like `"Controller@action"`) into physical PHP
  references (`[$controller, 'action']`).
    - In this folder: Performed inside the `InvocationExecutor`.
    - Why it matters: Allows developers to use clean strings instead of complex object setup code.
- **Reflection Caching**: Remembering the structure of a function after the first time we see it.
    - In this folder: Handled by the `ReflectionCache`.
    - Why it matters: Makes repeated calls to the same function nearly instantaneous.

### For Humans: What This Means (Terminology)

**Invocation** is "Running it". A **Callable** is "The Task". **Normalization** is "Translating the order", and *
*Reflection Caching** is "Remembering the instructions".

## Think of It

Think of a **Virtual Assistant (like a Smart Home Hub)**:

1. **Request**: "Alexa, play 'Classic Rock' in the 'Kitchen'."
2. **Normalization**: The hub translates "Classic Rock" into a specific Spotify playlist ID and "Kitchen" into a
   specific IP address for a speaker.
3. **Resolution**: The hub checks if it has the Spotify app and if the speaker is turned on.
4. **Invocation**: The hub starts the music playing.

### For Humans: What This Means (Analogy)

You don't care how the signal gets to the speaker; you just want to hear music. This folder handles all the "Wiring"
and "Signaling" so you can just give the command.

## Story Example

You have a scheduled task that runs every hour. You define it as a string: `App\Tasks\CleanDatabase@run`. When the timer
goes off, the system hands that string to the **Invoke** folder. The system doesn't know what `CleanDatabase` is, so the
Invoke system asks the container to build it, then it looks at the `run` method, sees it needs a `DatabaseConnection`,
gets that connection, and finally "Invokes" the task. The database is cleaned, and you never had to write a single line
of bootstrap code.

### For Humans: What This Means (Story)

It makes your application feel "Magic". You can run code from anywhere, and the system will piece together everything
needed to make that code work.

## For Dummies

If you're wondering "How does my controller method get its variables?", this is the answer.

1. **Translate**: Figure out what is actually being called.
2. **Analyze**: Look at what variables that task needs.
3. **Find**: Get those variables from the container.
4. **Run**: Execute the task with those variables.
5. **Return**: Give the result back to the app.

### For Humans: What This Means (Walkthrough)

It's a "Find and Run" engine for your code.

## How It Works (Technical)

The invocation flow starts with the `InvokeAction`, which creates an `InvocationContext`. This context is handed to the
`InvocationExecutor`. The executor uses a `ReflectionCache` to quickly generate or retrieve the
`ReflectionFunctionAbstract` for the target. It then normalizes the target (resolving class strings via the container if
needed). It generates `ParameterPrototypes` for all parameters and passes them to the `DependencyResolver`. Finally, it
uses `invokeArgs()` to execute the callable. This entire process is "Context-Aware," meaning it can detect if a function
call triggers another function call that would lead to an infinite loop.

### For Humans: What This Means (Technical)

It uses a "Stateless Pipeline". Each request is transformed from a "Request" into a "Blueprint" and then into "
Execution" using specialized helpers at each step.

## Architecture Role

- **Lives in**: `Features/Actions/Invoke`
- **Role**: Action and Task Execution.
- **Collaborators**: `DependencyResolver`, `ContainerKernel`.

### For Humans: What This Means (Architecture)

It is the "Verb Execution" layer of the container.

## What Belongs Here

- The high-level `InvokeAction`.
- The low-level `InvocationExecutor`.
- The `InvocationContext` and `ReflectionCache` helpers.

### For Humans: What This Means (Belongs)

Anything related to "Running Code" lives here.

## What Does NOT Belong Here

- **Building an object's constructor**: (lives in `Actions/Instantiate`).
- **Filling an object's properties**: (lives in `Actions/Inject`).
- **Deciding WHICH object to build for a type**: (lives in `Actions/Resolve`).

### For Humans: What This Means (Not Belongs)

This folder only **Runs**. It doesn't create or store objects.

## How Files Collaboration

`InvokeAction` is the boss. It manages the `InvocationExecutor`. The `InvocationExecutor` depends on the
`DependencyResolver` to find its arguments and the `ReflectionCache` to keep things fast. The `InvocationContext` acts
as the document that passes information between them.

### For Humans: What This Means

The "Manager" (Action) gives a "Job Sheet" (Context) to the "Runner" (Executor), who uses "Quick Memory" (Cache) to get
the job done fast.
