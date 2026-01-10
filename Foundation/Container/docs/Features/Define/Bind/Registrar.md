# Registrar

## Quick Summary

- This file serves as the primary gateway for registering services, singletons, and special rules into the container.
- It exists to encapsulate the complex logic of creating `ServiceDefinition` objects and saving them into the `DefinitionStore`.
- It removes the complexity of manual object creation by providing a simple, developer-friendly API (`bind`, `singleton`, `scoped`).

### For Humans: What This Means (Summary)

This is the **Admissions Clerk** for your container. When you want to tell the container about a new class, you talk to the Registrar. You say "Hey, register this as a Singleton," and the Registrar fills out all the paperwork and files it away in the container's memory for you.

## Terminology (MANDATORY, EXPANSIVE)

- **Registrar**: The orchestrator that handles the initial entry of a service into the system.
  - In this file: The `Registrar` class.
  - Why it matters: It ensures that every service starts with a valid, consistent piece of "paperwork" (the definition).
- **Service Identifier (Abstract)**: The name or interface being registered.
  - In this file: The `$abstract` parameter used in almost every method.
  - Why it matters: It’s the "Label" used to find the service later.
- **Service Lifetime**: How long the instance should "Live".
  - In this file: Controlled by which method you call (`bind` for short life, `singleton` for long life).
  - Why it matters: Critical for performance and sharing data between classes.
- **Contextual Entry Point**: Starting a special-case rule.
  - In this file: The `when()` method.
  - Why it matters: It’s the door to saying "In this specific situation, do things differently."

### For Humans: What This Means (Terminology)

The Registrar handles the **Paperwork** (Definitions) and ensures every service has a **Valid ID** (Abstract) and a **Home** (Lifetime) before it’s allowed into the container.

## Think of It

Think of a **School Enrollment Office**:

- **Registrar**: The office staff who help you sign up for classes.
- **Bind**: Signing up for a "One-Day Workshop" (you get a new experience every time).
- **Singleton**: Enrolling in a "Permanent Degree Program" (you stay the same student for years).
- **When**: A special accommodation for a student with specific needs.

### For Humans: What This Means (Analogy)

You don't just "walk into" the classroom (the runtime). You have to go to the Enrollment Office (Registrar) first to get your name on the list and get your student ID.

## Story Example

You are building a complex application with a Cache system. You tell the Registrar: "I want `CacheInterface` to use `RedisCache`, and I want it to be a Singleton so we only connect to Redis once." The Registrar creates a blueprint, marks it as "Singleton," and puts it in the `DefinitionStore`. Later, when 50 different classes ask for the Cache, the container knows exactly what to do because of the Registrar's careful paperwork.

### For Humans: What This Means (Story)

It’s the person who takes your "Wish List" and turns it into a "Solid Plan" that the container can follow.

## For Dummies

Imagine a giant guestbook at a wedding.

1. **Standard Entry**: You write your name and "I'm a guest" (`bind`).
2. **VIP Entry**: You write your name and "I'm the Maid of Honor" (`singleton`).
3. **Special Note**: "If the Photographer asks, I'm the one paying the bill" (`when`).

### For Humans: What This Means (Walkthrough)

If you're in your `bootstrap.php` file and you’re typing `$container->bind(...)`, you are talking to the Registrar. It’s your first point of contact.

## How It Works (Technical)

The `Registrar` holds a reference to the `DefinitionStore`. When you call `bind()`, `singleton()`, or `scoped()`, it internally uses a `register()` helper. This helper creates a new `ServiceDefinition` object, assigns the requested lifetime and concrete implementation, and then sends it to the store. Finally, it returns a `BindingBuilder`, which allows the developer to continue "Refining" the definition (adding tags or arguments) using a fluent interface.

### For Humans: What This Means (Technical)

It’s a "Factory for Blueprints". It builds the basic plan and then lets you pick up a "Markup Pen" (the BindingBuilder) to add more details if you want.

## Architecture Role

- **Lives in**: `Features/Define/Bind`
- **Role**: Entry point orchestrator.
- **Visibility**: Public (via the `Container` or `Application` facade).
- **Dependency**: `DefinitionStore`, `ServiceDefinition`.

### For Humans: What This Means (Architecture)

It is the "Front Desk" of the entire container component.

## Methods

### Method: bind(string $abstract, mixed $concrete = null)

#### Technical Explanation: bind

Registers a service with a `Transient` lifetime. Every request for this service will produce a fresh instance.

#### For Humans: What This Means

"Every time someone asks for this, build a brand new one."

### Method: singleton(string $abstract, mixed $concrete = null)

#### Technical Explanation: singleton

Registers a service with a `Singleton` lifetime. The first resolved instance is saved and shared globally.

#### For Humans: What This Means

"Build this once and remember it forever. Give everyone the same copy."

### Method: scoped(string $abstract, mixed $concrete = null)

#### Technical Explanation: scoped

Registers a service with a `Scoped` lifetime. The instance is shared within a specific lifecycle (like a Request) but destroyed afterwards.

#### For Humans: What This Means

"Share this as long as we're doing the current Task, then forget it."

### Method: instance(string $abstract, object $instance)

#### Technical Explanation: instance

Directly registers a pre-existing object as a singleton.

#### For Humans: What This Means

"I already built this object myself. Here it is—just hold onto it for me."

### Method: extend(string $abstract, callable $closure)

#### Technical Explanation: extend

Adds a decorator/extender to a service in the store.

#### For Humans: What This Means

"After you build this service, also run this function to 'tweak' it."

### Method: when(string $consumer)

#### Technical Explanation: when

Initializes a `ContextBuilder` for defining specialized injection rules.

#### For Humans: What This Means

"I'm about to give you a special exception for [Consumer]..."

## Risks & Trade-offs

- **Strict Order**: You can't call `to()` or `tag()` *before* calling `bind()`. You must follow the Registrar's order of operations.
- **Overwriting**: If you register the same ID twice, the Registrar will overwrite the old one without warning (this is standard behavior but requires care).

### For Humans: What This Means (Risks)

Be careful not to register the same name twice with different rules, or the last one you typed will "Win" and delete the first one!

## Related Files & Folders

- `BindingBuilder.php`: The "Markup Pen" you get back after registering.
- `DefinitionStore.php`: The "Filing Cabinet" the Registrar puts the paperwork in.
- `ContextBuilder.php`: The tool used for the "Special Notes" (Context).

### For Humans: What This Means (Relationships)

The **Registrar** creates the file, the **Builder** edits the file, and the **Store** holds the file.
