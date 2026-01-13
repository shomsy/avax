# Features/Actions/Instantiate

## What This Folder Represents

This folder contains the "Construction Yard" of the container—the specialized logic that physically creates new objects
from class names.

Technically, `Features/Actions/Instantiate` is the boundary between "Planning" and "Existing". While other folders
handle rules and recipes, this folder is where the PHP `new` operator (via Reflection) is actually invoked. It
centralizes the complexity of constructor injection, ensuring that every object is built with the correct arguments and
that errors are caught and explained clearly.

### For Humans: What This Means (Represent)

This is the **Lego Building Table**. This is where all the bricks (Dependencies) are finally clicked together to create
the toy (The Object).

## Terminology (MANDATORY, EXPANSIVE)

- **Instantiation**: The act of creating a living copy (instance) of a class in memory.
    - In this folder: Handled by the `Instantiator`.
    - Why it matters: It is the moment a "Blueprint" becomes a "Tool".
- **Reflection Construction**: Using PHP's internal tools to build an object without typing `new ClassName()`.
    - In this folder: Used inside `Instantiator::build()`.
    - Why it matters: It allows the container to build ANY class dynamically at runtime.
- **Assembly Strategy**: The specific order of events used to build an object (Check rules -> Resolve parts -> Build).
    - In this folder: The logic flow of the `build()` method.
    - Why it matters: Ensures that we never try to build a class without its required parts.

### For Humans: What This Means (Terminology)

**Instantiation** is "Making it real". **Reflection Construction** is "Building it automatically", and **Assembly
Strategy** is "The instruction manual".

## Think of It

Think of a **Subway Sandwich Shop**:

1. **Definitions**: The menu on the wall.
2. **Engine**: The person taking your order.
3. **Instantiator**: The person actually putting the bread, meat, and cheese together into a sandwich.

### For Humans: What This Means (Analogy)

The person at the counter (Instantiator) doesn't care *why* you wanted extra pickles; they just care about getting the
sandwich built according to the order.

## Story Example

You write `$container->get(UserMailer::class)`. The container finds the rule, checks the dependencies, and then hands
the "Job" to the **Instantiator**. The Instantiator looks at `UserMailer`, sees it needs a `SmtpTransport`, gets that
transport from the container, and then performs the final "Act of Creation". It hands back a finished, working
`UserMailer` ready to send emails.

### For Humans: What This Means (Story)

It’s the "Home Stretch" of the container's work. It’s what makes the container more than just a list of rules—it’s what
makes it a **Factory**.

## For Dummies

If you're wondering "Where does the `new` keyword happen?", this is it.

1. **Check**: Can I build this?
2. **Prepare**: Get the ingredients ready.
3. **Build**: Click it all together.
4. **Ship**: Hand the finished object to the app.

### For Humans: What This Means (Walkthrough)

It’s the four-step "Build and Ship" process.

## How It Works (Technical)

The `Instantiator` uses a combination of pre-analyzed metadata (`ServicePrototype`) and runtime resolution (
`DependencyResolver`). It creates a `ReflectionClass` for the target service and checks its `isInstantiable()` status.
If the class has a constructor, it resolves the parameters into an ordered array of arguments. Finally, it uses
`newInstanceArgs()` to trigger the creation. This entire process is wrapped in dedicated error handling to capture and
report construction failures (like missing classes or private constructors).

### For Humans: What This Means (Technical)

It uses "Eyes" (Reflection) to see how to build the object and "Hands" (`newInstanceArgs`) to put it together.

## Architecture Role

- **Lives in**: `Features/Actions/Instantiate`
- **Role**: Object Creation.
- **Collaborators**: `DependencyResolver`, `ServicePrototypeFactory`.

### For Humans: What This Means (Architecture)

It is the "Final Execution" layer of the resolution process.

## What Belongs Here

- The `Instantiator` class.
- Any specialized factories that focus purely on creating raw objects.

### For Humans: What This Means (Belongs)

The "Builders" live here.

## What Does NOT Belong Here

- **Resolving which class to build**: (lives in `Actions/Resolve`).
- **Storing objects for later**: (lives in `Operate/Scope`).
- **Injecting after constructor finishes**: (lives in `Actions/Inject`).

### For Humans: What This Means (Not Belongs)

This folder only **Builds**. It doesn't decide what to build, and it doesn't store what it built.

## How Files Collaboration

The `index.md` acts as the map for the constructionyard. The `Instantiator` uses the `DependencyResolver` to get its
supplies and the `PrototypeFactory` to understand its blueprints.

### For Humans: What This Means

The "Construction Crew" works together using the "Blueprint" and the "Materials" to build your objects.
