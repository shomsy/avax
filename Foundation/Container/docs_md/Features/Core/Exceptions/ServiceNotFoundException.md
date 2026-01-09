# ServiceNotFoundException

## Quick Summary
- Specialized container exception thrown when a requested service ID is not registered and cannot be autowired.
- Exists to distinguish “missing service” from other resolution errors.

### For Humans: What This Means
This is the error you get when you asked for a service the container doesn’t know how to provide.

## Terminology
- **Service ID**: The identifier you pass to the container.

### For Humans: What This Means
It’s basically “unknown ID.”

## Think of It
Like calling a phone number that doesn’t exist.

### For Humans: What This Means
The container can’t connect your request to anything.

## Story Example
A developer calls `$container->get('NonExistingService')`. The container throws `ServiceNotFoundException` to indicate the ID is not resolvable.

### For Humans: What This Means
You’ll know you forgot to register something.

## For Dummies
Use this when you need to handle “not registered” differently from “registered but failed to build.”

### For Humans: What This Means
It’s the missing-service error.

## How It Works (Technical)
Extends `ContainerException`.

### For Humans: What This Means
It’s a named subclass.

## Architecture Role
Thrown by resolution paths that cannot find a service definition and cannot autowire.

### For Humans: What This Means
It tells you a binding is missing.

## Methods
_Inherits base exception behavior._

### For Humans: What This Means
Type is the message.

## Risks, Trade-offs & Recommended Practices
- **Practice: Register explicitly**. Use auto-define/autowire intentionally.

### For Humans: What This Means
Don’t rely on magic—register important services.

## Related Files & Folders
- `docs_md/Features/Define/Store/DefinitionStore.md`: Where definitions live.
- `docs_md/Core/Kernel/Steps/EnsureDefinitionExistsStep.md`: Detects missing definitions early.

### For Humans: What This Means
DefinitionStore and kernel guard steps are where “missing service” gets noticed.
