# Features/Actions/Inject

## What This Folder Represents

This folder contains the "Hydration Layer" of the container—the specialized logic that fills an object with life *after*
its physical body has been created.

Technically, `Features/Actions/Inject` is responsible for post-construction dependency injection. While the
`Instantiator` handles constructors, this folder handles properties (using attributes/annotations) and setter methods.
It ensures that even if a dependency wasn't passed via the constructor, it still arrives at its destination safely using
Reflection. This separation allows the container to support diverse coding styles while keeping construction logic clean
and focused.

### For Humans: What This Means (Represent)

This is the **Interior Design Team**. If the `Instantiator` built the walls and roof of your house, this folder is where
the team comes in to install the lights, the plumbing, and the furniture.

## Terminology (MANDATORY, EXPANSIVE)

- **Hydration**: The act of "Loading" an object with its required external services.
    - In this folder: Orchestrated by `InjectDependencies`.
    - Why it matters: It ensures that objects are fully functional before your application ever sees them.
- **Injection Point**: A specific spot in a class (a variable or a method) that the container is allowed to "Inject"
  something into.
    - In this folder: Discovered via the `ServicePrototype`.
    - Why it matters: Prevents the container from touching parts of your object that you didn't explicitly permit.
- **Property Injection**: Assigning values directly to class variables, bypassing their visibility (private/protected).
    - In this folder: Handled by `PropertyInjector`.
    - Why it matters: Great for "Optional" dependencies like Loggers that would otherwise clutter your constructor.
- **Setter Injection**: Calling a method (like `setDatabase()`) to provide a dependency.
    - In this folder: Handled via the `DependencyResolver` inside the injection flow.
    - Why it matters: A clean, standard PHP way to provide dependencies without force-feeding them through the
      constructor.

### For Humans: What This Means (Terminology)

**Hydration** is "Setting it up". **Injection Points** are "The Plugs". **Property Injection** is "Direct Wiring", and *
*Setter Injection** is "Plugging into a port".

## Think of It

Think of a **New Smartphone**:

1. **Instantiator**: You have the phone in your hand. It's built, but it has no data.
2. **Inject (Hydration)**: You plug it in, and it starts downloading your apps, contacts, and photos to their specific
   folders and apps.

### For Humans: What This Means (Analogy)

The phone (The Object) existed before you plugged it in, but it wasn't *your* phone until the data (The Dependencies)
was injected into it.

## Story Example

You have a `ReportController`. You don't want to pass a `Logger`, a `Cache`, a `Database`, and a `TemplateEngine`
through the constructor because that would be 4 arguments! Instead, you add `#[Inject]` to those 4 properties. When the
container builds your controller, it first creates the object, then hands it to **InjectDependencies**. The hydrator
finds those 4 "Injection Points", looks up the services in the container, and "Fills" them for you. Now your controller
is ready to go, and your constructor stays empty and clean.

### For Humans: What This Means (Story)

It allows you to build complex objects without making your code looks like "Plate of Spaghetti" filled with constructor
arguments.

## For Dummies

If you're wondering "How do my private properties get filled by the container?", this is where it happens.

1. **Find the Plugs**: Look for `#[Inject]` attributes or marked setter methods.
2. **Get the Data**: Ask the container for the services that match the types on those plugs.
3. **Plug it in**: Set the property value or call the method.
4. **Finish**: The object is now "Hydrated".

### For Humans: What This Means (Walkthrough)

It's like a 3-step "Find, Fetch, and Fill" process for your objects.

## How It Works (Technical)

The hydration process is governed by the `ServicePrototype`, which acts as the blueprint of all injection needs for a
class. `InjectDependencies` first iterates through `injectedProperties`, delegating the resolution to
`PropertyInjector`. The `PropertyInjector` checks for overrides, then uses the container to find matching types, finally
returning a `PropertyResolution` DTO. If successful, the hydrator uses PHP Reflection to set the property value.
Afterward, it repeats the process for `injectedMethods`, resolving their parameters via the standard
`DependencyResolver` and invoking the methods.

### For Humans: What This Means (Technical)

It uses "Blueprints" to find the work, "Specialists" to find the values, and "Reflection tools" to bypass access limits
and set the data.

## Architecture Role

- **Lives in**: `Features/Actions/Inject`
- **Role**: Post-Construction Object Hydration.
- **Order**: Runs immediately after `Instantiate` but before the object is returned to the user.

### For Humans: What This Means (Architecture)

It is the "Final Wiring" phase.

## What Belongs Here

- The main `InjectDependencies` orchestrator.
- The `PropertyInjector` specialist.
- DTOs like `PropertyResolution` that move data across the injection layer.

### For Humans: What This Means (Belongs)

The "Installers" live here.

## What Does NOT Belong Here

- **Building the object in the first place**: (lives in `Actions/Instantiate`).
- **Deciding WHICH objects to build**: (lives in `Actions/Resolve`).
- **Running a task/function**: (lives in `Actions/Invoke`).

### For Humans: What This Means (Not Belongs)

This folder only **Hydrates existing objects**. It doesn't create them, and it doesn't execute their "Main Task".

## How Files Collaboration

`InjectDependencies` is the manager. It gives the list of properties to the `PropertyInjector` and the list of methods
to the `DependencyResolver`. Once both are finished, the object is considered hydrated.

### For Humans: What This Means

The "Lead Electrician" (InjectDependencies) tells the "Wire Puller" (PropertyInjector) and the "Port Specialist" (
DependencyResolver) what to do.
