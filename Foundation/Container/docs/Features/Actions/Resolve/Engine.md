# Engine

## Quick Summary

- This file serves as the core "Resolution Engine" that determines how a service identifier is transformed into a real instance.
- It exists to manage the priority of bindings—handling contextual overrides, global definitions, and fallback autowiring.
- It removes the complexity of manual object creation by coordinating between the `DefinitionStore` and the `Instantiator`.

### For Humans: What This Means (Summary)

This is the **Builder Brain** of the container. When you say "I want an object," the Engine is the one that looks at the rules, decides which version of the object you should get (e.g., a special one for a certain class), and then tells the assembly line to build it.

## Terminology (MANDATORY, EXPANSIVE)

- **Concrete Evaluation**: The process of taking a "blueprint" (like a class name or a function) and executing it to get a result.
  - In this file: The `evaluateConcrete()` method.
  - Why it matters: It ensures that whether you provide a class name, a function, or a pre-built object, the Engine knows how to handle it.
- **Contextual Delegation**: Handling "Special Exceptions" where a dependency's implementation depends on who is asking for it.
  - In this file: Checked via `getContextualMatch()` in `resolveFromBindings()`.
  - Why it matters: Vital for complex apps where two classes might need different versions of the same shared interface.
- **Autowiring Fallback**: Automatically building a class even if it hasn't been explicitly registered.
  - In this file: The `class_exists()` check at the end of resolution.
  - Why it matters: Saves developers massive amounts of time by "guessing" the dependencies of standard classes.
- **Resolution Cycle**: A single pass through the engine to resolve one specific identifier.
  - In this file: The `resolve()` method.
  - Why it matters: This cycle can trigger *more* cycles (recursion) if the class it builds has its own dependencies.

### For Humans: What This Means (Terminology)

The Engine uses **Concrete Evaluation** to build things, **Contextual Delegation** to handle special cases, and **Autowiring** to save you work. Every time it runs, it’s a **Resolution Cycle**.

## Think of It

Think of a **Custom Pizza Shop**:

- **Customer**: Your application asking for a "Pepperoni Pizza" (Service ID).
- **Engine**: The Lead Chef who checks the order.
- **Contextual Rule**: "This customer has a gluten allergy" (Contextual Binding). The Lead Chef sees this and tells the kitchen to use a different crust.
- **Definition Store**: The menu and the secret recipes.
- **Instantiator**: The actual oven and prep area.

### For Humans: What This Means (Analogy)

The Chef (Engine) doesn't just grab any pizza; they look at exactly who is ordering and what the rules are before they start the oven.

## Story Example

You ask the container for a `PaymentProcessor`. The **Engine** checks the rules. It sees that your application is currently in "Testing Mode" (managed via a contextual binding). The Engine says: "Normally I'd build the real CreditCardProcessor, but because we are in testing, I'll build the `MockProcessor` instead." It communicates this to the `Instantiator`, which builds the mock, and the Engine hands it back to you. Your code never knew the difference—the Engine handled all the logic.

### For Humans: What This Means (Story)

It allows your application to "Change its mind" about which objects to use based on the environment or situation, without you having to write complex `if` statements in your code.

## For Dummies

Imagine you're at a library.

1. You give the librarian a book title (Service ID).
2. The librarian checks if someone left a "Special Note" for YOU specifically (Contextual Binding).
3. If not, they check the main catalog (Global Definition).
4. If the book isn't in the catalog but they see it on the "New Arrivals" shelf (Class exists), they go grab it (Autowire).
5. If they find it, they hand it to you. If not, they tell you they can't find it (NotFoundException).

### For Humans: What This Means (Walkthrough)

The Engine is the series of "Checks" that happen between you asking for a service and receiving an object.

## How It Works (Technical)

The `Engine` implements a prioritized resolution algorithm. When `resolve()` is called:

1. It first checks if there is a **Contextual Binding** for the current requester (parent).
2. If not, it looks for an **Explicit Binding** in the `DefinitionStore`.
3. If still not found, it checks if the identifier is a valid **Class String**.
4. If it's a class, it uses the `Instantiator` to trigger autowiring.
5. Resolution is **Recursive**: If a class constructor needs another object, the process restarts for that new ID, building a "Context Chain" as it goes to prevent circular dependencies and allow deep contextual matching.

### For Humans: What This Means (Technical)

It's a smart "Decision Tree". It looks for the most specific rule first, then the general rule, and finally tries to figure it out itself.

## Architecture Role

- **Lives in**: `Features/Actions/Resolve`
- **Role**: Core Resolver Implementation.
- **Primary Collaborators**: `DefinitionStore`, `ScopeRegistry`, `Instantiator`.

### For Humans: What This Means (Architecture)

It is the "Thinking Center" of the resolution process.

## Methods

### Method: setContainer(ContainerInternalInterface $container)

#### Technical Explanation: setContainer

Injects the container facade into the engine. This is necessary because the engine often needs to "ask the container" to resolve nested dependencies.

#### For Humans: What This Means

Plugs the Engine into the "Central Motherboard" of the system.

### Method: resolve(KernelContext $context)

#### Technical Explanation: resolve

The main entry point. It takes a context (which includes the service ID and any manual overrides) and returns a result.

#### For Humans: What This Means

"Execute a search and build".

### Method: hasInternals()

#### Technical Explanation: hasInternals

Safety check used by the Kernel to ensure the engine is fully wired before use.

#### For Humans: What This Means

"Are you ready to work?"

## Risks & Trade-offs

- **Performance**: Deeply nested resolution trees (A needs B needs C needs D...) can be slow. Use Singletons where possible to cache results.
- **Ambiguity**: If you have a class named `Logger` and a binding named `Logger`, the binding takes priority. This can sometimes lead to "Surprise" results if you aren't careful with naming.

### For Humans: What This Means (Risks)

The more complex your dependencies, the harder the Engine has to work. If you find your app is slow, check if you're building too many things from scratch instead of sharing them (as singletons).

## Related Files & Folders

- `DependencyResolver.php`: The helper that finds the "parts" (arguments) for classes.
- `Instantiator.php`: The "Oven" that actually creates the objects.
- `DefinitionStore.php`: The "Recipe Book".

### For Humans: What This Means (Relationships)

The **Engine** makes the plan, the **Store** provides the rules, and the **Instantiator** does the physical building.
