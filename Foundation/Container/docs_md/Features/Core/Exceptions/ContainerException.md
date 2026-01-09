# ContainerException

## Quick Summary
- Base runtime exception type for container failures.
- Implements PSR container exception interface for interoperability.
- Exists so the container can throw one consistent “container error” type.

### For Humans: What This Means
It’s the generic “something went wrong in the container” exception.

## Terminology
- **PSR-11**: Standard interface for containers; defines exception interfaces.
- **RuntimeException**: Standard PHP exception type for runtime failures.

### For Humans: What This Means
This exception fits PSR expectations so other tooling can understand container errors.

## Think of It
Like a general “engine fault” light: not the exact problem, but the category.

### For Humans: What This Means
It’s the baseline error you can catch.

## Story Example
A service cannot be resolved due to missing dependencies. The container wraps the failure as a `ContainerException` so callers can catch a consistent type.

### For Humans: What This Means
Instead of many random exception types, you can catch one container-focused error.

## For Dummies
Catch `ContainerException` when you want to handle any container failure.

### For Humans: What This Means
One catch for “container problems.”

## How It Works (Technical)
Extends `RuntimeException` and implements `Psr\Container\ContainerExceptionInterface`.

### For Humans: What This Means
It’s a runtime exception that follows PSR container rules.

## Architecture Role
Base exception used across container subsystems.

### For Humans: What This Means
It’s the container’s default error type.

## Methods

_Inherits exception behavior from PHP base classes._

### For Humans: What This Means
You use it like any other exception.

## Risks, Trade-offs & Recommended Practices
- **Practice: Throw specific exceptions when possible**. Use specialized exceptions for clarity.

### For Humans: What This Means
Use this as the umbrella, but still be specific when you can.

## Related Files & Folders
- `docs_md/Features/Core/Exceptions/ResolutionException.md`: Resolution failures.
- `docs_md/Features/Core/Exceptions/ServiceNotFoundException.md`: Missing service failures.

### For Humans: What This Means
Specialized exceptions tell you what kind of container problem occurred.
