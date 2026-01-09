# ContainerExceptionInterface

## Quick Summary
- Unifies all container exceptions under one contract.
- Exists so callers can catch “any container error” consistently.
- Supports PSR compatibility and consistent exception chaining.

### For Humans: What This Means
It’s the common label that says “this error came from the container.”

## Terminology
- **Exception interface**: A type contract for exceptions.
- **Chaining**: Linking one exception as the cause of another.

### For Humans: What This Means
It lets you catch container errors without knowing the exact class.

## Think of It
Like a category tag on support tickets: “Container Issue.”

### For Humans: What This Means
It groups all container errors together.

## Story Example
A framework integration catches `ContainerExceptionInterface` to handle all container problems uniformly and convert them to HTTP 500 responses.

### For Humans: What This Means
One catch block can handle all container errors.

## For Dummies
Catch `ContainerExceptionInterface` when you want to handle all container exceptions.

### For Humans: What This Means
It’s your umbrella type.

## How It Works (Technical)
Interface extending `Throwable` (directly or indirectly depending on implementation), implemented by container exception classes.

### For Humans: What This Means
It’s just a type label that real exceptions implement.

## Architecture Role
Defines the common error contract across the container component.

### For Humans: What This Means
It keeps error handling consistent.

## Methods
_Inherits from `Throwable`._

### For Humans: What This Means
It behaves like any exception.

## Risks, Trade-offs & Recommended Practices
- **Practice: Still throw specific exceptions**. This is for catching, not for being vague.

### For Humans: What This Means
Use the umbrella to catch, but be specific when throwing.

## Related Files & Folders
- `docs_md/Features/Core/Exceptions/index.md`: Exceptions overview.
- `docs_md/Features/Core/Exceptions/ContainerException.md`: Base implementation.

### For Humans: What This Means
See the base class and the exception folder docs to understand the hierarchy.
