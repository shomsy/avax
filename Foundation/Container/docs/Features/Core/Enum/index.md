# Features/Core/Enum

## What This Folder Represents
Enums that encode container-level choices as type-safe values (like service lifetimes). They exist to avoid fragile string constants and to make intent explicit.

### For Humans: What This Means (Represent)
Enums replace magic strings with safe, self-documenting choices.

## What Belongs Here
Enums like `ServiceLifetime`.

### For Humans: What This Means (Belongs)
If it’s a container-wide choice that shouldn’t be a plain string, it belongs here.

## What Does NOT Belong Here
Application domain enums.

### For Humans: What This Means (Not Belongs)
Keep it about container mechanics, not business logic.

## How Files Collaborate
Definitions, lifecycle resolvers, and caching strategies use these enum values to decide how to store and reuse instances.

### For Humans: What This Means (Collaboration)
Lifetime enums influence caching behavior across the container.
