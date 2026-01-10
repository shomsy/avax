# Features/Core/Exceptions

## What This Folder Represents
Exception types and exception contracts used across the Container component. They exist to make failure modes consistent and PSR-compatible.

### For Humans: What This Means (Represent)
These are the standard error types the container throws so you can handle failures predictably.

## What Belongs Here
- Base container exception types.
- PSR-aligned exception interfaces.
- Specialized exceptions like service-not-found and resolution failures.

### For Humans: What This Means (Belongs)
If the container throws it, it belongs here.

## What Does NOT Belong Here
Application business exceptions.

### For Humans: What This Means (Not Belongs)
Keep it about container failures, not your app’s domain failures.

## How Files Collaborate
Specialized exceptions extend a shared base so they can be caught broadly or narrowly depending on your needs. Interfaces provide compatibility with external tooling.

### For Humans: What This Means (Collaboration)
You can catch “any container error” or “a specific kind of container error,” depending on how precise you want to be.
