# ResolutionException

## Quick Summary
- Specialized container exception thrown when resolution fails.
- Exists to distinguish “can’t build it” from other container errors.

### For Humans: What This Means (Summary)
This is the error you get when the container tried to build something and couldn’t.

## Terminology (MANDATORY, EXPANSIVE)- **Resolution**: The act of turning a service ID into an instance/value.

### For Humans: What This Means
Resolution is “building or retrieving the service.”

## Think of It
Like a recipe failure: you have the recipe, but you can’t complete it because ingredients are missing.

### For Humans: What This Means (Think)
The container couldn’t complete the build.

## Story Example
A required property injection fails because no service is registered for a type. The injector throws `ResolutionException` so the caller knows resolution failed.

### For Humans: What This Means (Story)
You get a specific exception type that points to a resolution problem.

## For Dummies
Catch this when you want to specifically handle “service build failed.”

### For Humans: What This Means (Dummies)
It’s a more precise error than generic container exception.

## How It Works (Technical)
Extends `ContainerException`.

### For Humans: What This Means (How)
It’s a named subclass used for clarity.

## Architecture Role
Used across resolution, injection, and kernel guard steps.

### For Humans: What This Means (Role)
It marks errors that are directly about building services.

## Methods _Inherits base exception behavior._

### For Humans: What This Means
It behaves like an exception; the value is in its type.

## Risks, Trade-offs & Recommended Practices
- **Practice: Include context**. Put service IDs and paths into messages.

### For Humans: What This Means (Risks)
Make the error message tell you what failed and where.

## Related Files & Folders
- `docs_md/Features/Actions/Resolve/Engine.md`: Where many resolution failures originate.
- `docs_md/Core/Kernel/Steps/CircularDependencyStep.md`: Another source of resolution failures.

### For Humans: What This Means (Related)
Resolution errors usually come from the engine or guard steps.
