# Features/Actions/Instantiate

## What This Folder Represents
This folder contains the “instance creation” action(s): code that takes a class name plus optional overrides and produces a real object instance. It exists to keep constructor-based creation logic centralized, consistent, and easy to test.

### For Humans: What This Means
This is the part of the container that actually creates new objects from class names, using the container to fill constructor parameters.

## What Belongs Here
- `Instantiator`: The action that builds objects using reflection and constructor prototypes.

### For Humans: What This Means
If it’s responsible for turning a class name into a fresh object, it belongs here.

## What Does NOT Belong Here
- Property/method injection (that’s in `Features/Actions/Inject`).
- Lifetime caching (that’s kernel lifecycle handling).
- Definition lookup (that’s Define/Store).

### For Humans: What This Means
This folder is about “create the object,” not “inject extras” or “store it for reuse.”

## How Files Collaborate
`Instantiator` uses a prototype factory to understand constructor parameters and a dependency resolver to turn those parameters into actual argument values using the container. The kernel pipeline provides context (and sometimes a precomputed prototype) to avoid repeated analysis.

### For Humans: What This Means
One tool reads the constructor blueprint, one tool resolves the arguments, and then the object is created.
