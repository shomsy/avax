# Features/Actions/Inject

## What This Folder Represents
This folder contains the “injection phase” of the container: code that takes a partially-constructed object and fills in additional dependencies through property injection and method injection. It exists because not every dependency fits naturally into a constructor, and because the container needs a consistent, testable place to apply injection rules.

### For Humans: What This Means
Think of this folder as the part of the container that finishes wiring an object after it’s created—like connecting cables to ports after you’ve assembled a machine.

## What Belongs Here
- The `InjectDependencies` action that orchestrates injection.
- A `PropertyInjector` implementation that resolves values for injectable properties.
- Resolver/result objects such as `PropertyResolution`.
- Contracts that allow injection logic to be swapped.

### For Humans: What This Means
If a class helps the container fill properties or call injection methods, it belongs here.

## What Does NOT Belong Here
- Constructor autowiring and instance creation (that’s resolution/building, not injection).
- Definition storage and lifecycle caching.
- Application-level “setter injection” conventions that aren’t part of container rules.

### For Humans: What This Means
This folder is about finishing an object, not deciding which object to build or where to cache it.

## How Files Collaborate
`InjectDependencies` uses the prototype factory to discover injection points, then delegates property injection to `PropertyInjectorInterface` and method-parameter resolution to a dependency resolver. The output is the same target object, now with dependencies injected.

### For Humans: What This Means
One class coordinates; one class resolves property values; one resolver figures out method parameters. Together they wire the object.
