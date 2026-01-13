# DefinitionStore

## Quick Summary

- This file implements the authoritative registry of service definitions, tags, extenders, and contextual rules.
- It exists so the container has one “single source of truth” for all registration metadata.
- It removes the complexity of scattered registration state by centralizing lookups and internal indices.

### For Humans: What This Means (Summary)

This is the container’s **Internal Registry**. When you tell the container "When someone asks for A, give them B", this
class is the one that writes it down and remembers it. It's the central memory that the rest of the system consults
whenever a decision needs to be made.

## Terminology (MANDATORY, EXPANSIVE)

- **Definition Store**: A specialized registry mapping service IDs to their configuration blueprints.
    - In this file: Represented by the `$definitions` array.
    - Why it matters: Without a central store, different parts of the app might get different versions of the same
      service.
- **Service Blueprint (Definition)**: The technical plan for how a service should be built.
    - In this file: Stored as `ServiceDefinition` objects.
    - Why it matters: It contains the "Instructions" (lifetime, concrete class, tags) that the resolver follows.
- **Contextual Inject Rule**: A rule that says "In THIS specific class, use THAT specific dependency".
    - In this file: Managed via `$contextual` and `$wildcardContextual`.
    - Why it matters: It allows you to use different implementations of an interface depending on who is asking for it.
- **Service Tagging**: Grouping services together under a label.
    - In this file: Managed through the `$tags` index.
    - Why it matters: It lets you pull a whole "Collection" of services (like all 'middleware') at once.
- **Extender Callback**: A piece of code that runs after a service is built.
    - In this file: Stored in `$extenders`.
    - Why it matters: It allows you to "Pimp my Ride"—adding extra features or wrappers to an object without changing
      the original class.

### For Humans: What This Means (Terminology)

This store manages **Manuals** (Definitions), **Sticky Notes** (Tags), **Exceptions** (Contextual Rules), and **Upgrades
** (Extenders). It doesn't build the furniture; it's the giant library that holds all the assembly instructions and
special requests from customers.

## Think of It

Think of it as a **Master Librarian** in a massive technical library:

- **Definitions**: The books containing assembly blueprints.
- **Tags**: The library categories (e.g., "Reference", "Fiction").
- **Contextual Rules**: The "Reserved" shelf for specific professors.
- **Extenders**: The book binding service that adds a protective cover before you check out.

### For Humans: What This Means (Analogy)

The Librarian doesn't write the books or build the things in them. The Librarian just makes sure that when you ask for a
book, you get the right edition, with the right cover, based on who you are.

## Story Example

Imagine you are building a complex Logging system. You have a `FileLogger` and a `CloudLogger`. Usually, you want the
`FileLogger`. But for the `PaymentProcessor` class, you want the `CloudLogger` for extra security. You register this
rule in the `DefinitionStore`. When the app starts, the container checks the store and says: "Oh, `PaymentProcessor` is
asking for a Logger? The Librarian says for THIS specific person, I should use the Cloud one."

### For Humans: What This Means (Story)

The store allows your app to have "Smart Memory"—it's not just a dumb list; it understands context and special rules.

## For Dummies

Imagine a giant wall of mailboxes in an apartment building.

1. **Registering**: You put a label on the box saying who lives there and what they need.
2. **Tagging**: You put a blue dot on all the "Staff" boxes.
3. **Special Rule**: You put a note saying "If the Landlord asks for a key, give him the master key, but for everyone
   else, give the standard one."
4. **Extending**: You tell the janitor: "Whenever you deliver a package, also spray it with disinfectant."
5. **Lookup**: When someone comes to the front desk, the clerk (this class) checks the wall and gives instructions.

### For Humans: What This Means (Walkthrough)

It’s the brain of the container. If you want to change how the container works, you change the entries in this store.

## How It Works (Technical)

The `DefinitionStore` maintains several internal indices to make lookups fast:

- It maps `abstract` to `ServiceDefinition`.
- It maintains a reverse index of `tag` to `abstract[]`.
- It performs a specialized search for contextual rules: first checking for a direct match, then checking for wildcard
  patterns (using `fnmatch`), and finally traversing the class hierarchy (parents and interfaces) to find inherited
  rules.
- To prevent slow reflection calls during hierarchy traversal, it uses a `$classHierarchyCache`.

### For Humans: What This Means (Technical)

It uses "Fast Shortcuts" (Indices) so that even if you have thousands of services, it can find the right one in a
fraction of a millisecond.

## Architecture Role

- **Lives in**: `Features/Define/Store`
- **Role**: Data Persistence & Indexing.
- **Collaborators**: `Registrar` (writes to it), `Resolver` (reads from it), `ContainerBuilder` (manages its lifecycle).

### For Humans: What This Means (Architecture)

It is the "Database" of the container system. It's where the configuration "rests" until it's needed at runtime.

## Methods

### Method: add(ServiceDefinition $definition)

#### Technical Explanation: add

Registers or replaces a service definition and updates the tag index to ensure consistency. It clears the local
resolution cache to prevent stale data.

#### For Humans: What This Means

It’s like adding a new blueprint to the library. If an old one exists, it throws it away and updates the index labels.

### Method: has(string $abstract)

#### Technical Explanation: has

Checks if a service ID is registered in the internal definitions map.

#### For Humans: What This Means

A quick check: "Do we have a record for this name?"

### Method: get(string $abstract)

#### Technical Explanation: get

Retrieves a single service blueprint or null if not found.

#### For Humans: What This Means

Fetches the "Instruction Manual" for a specific service.

### Method: getTaggedIds(string $tag)

#### Technical Explanation: getTaggedIds

Performs a reverse lookup in the tag index and returns unique service IDs.

#### For Humans: What This Means

"Give me a list of everyone who has a 'Middleware' tag."

### Method: getContextualMatch(string $consumer, string $needs)

#### Technical Explanation: getContextualMatch

The most complex method. It searches for an override value based on the consumer's identity, traversing through patterns
and class inheritance.

#### For Humans: What This Means

The "Special Exception Checker". It looks up if there's a custom rule for a specific class needing a specific tool.

### Method: addContextual(string $consumer, string $needs, mixed $give)

#### Technical Explanation: addContextual

Registers an override rule. Supports string patterns (wildcards) for group-based overrides.

#### For Humans: What This Means

Registers a new exception: "If [Consumer] asks for [Needs], give them [Give]".

### Method: addExtender(string $abstract, Closure $extender)

#### Technical Explanation: addExtender

Appends a callback to the extender stack for a specific service.

#### For Humans: What This Means

Adds a "Decorator" step—extra work to be done after the service is built.

### Method: getExtenders(string $abstract)

#### Technical Explanation: getExtenders

Returns all callbacks currently registered for a service.

#### For Humans: What This Means

Fetches the list of "Upgrades" that need to be applied to a service.

### Method: addTags(string $abstract, string|string[] $tags)

#### Technical Explanation: addTags

Updates an existing definition with new tags and refreshes the reverse index.

#### For Humans: What This Means

Adds more labels to an already registered service.

### Method: getAllDefinitions()

#### Technical Explanation: getAllDefinitions

Returns the raw internal definitions map.

#### For Humans: What This Means

Exposes the entire registry for debugging or advanced analysis.

## Risks & Trade-offs

- **Memory**: Keeping a massive list of definitions and caches in memory can be expensive in very large apps.
- **Mutability**: Since it can be modified after it's been read, there's a risk of "Race Conditions" if registration
  happens too late.
- **Complexity**: Wildcard matching (`App\*`) is powerful but can be hard for developers to trace.

### For Humans: What This Means (Risks)

It's a powerful brain, but if you give it too many confusing "Special Rules" (Wildcards), it might be hard for other
developers to understand why a specific class is getting a specific dependency.

## Related Files & Folders

- `Registrar.php`: The one who usually writes into this store.
- `ServiceDefinition.php`: The data model for the entries in this store.
- `DependencyResolver.php`: The main "Reader" of this store at runtime.

### For Humans: What This Means (Relationships)

If the **Registrar** is the pen, and the **Resolver** is the reader, the **DefinitionStore** is the paper they are both
using.
