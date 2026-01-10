# Features/Core/Attribute

## What This Folder Represents
PHP attributes used by the container to express wiring intent directly in code. They exist to make dependency injection metadata explicit and discoverable through reflection.

### For Humans: What This Means (Represent)
These are little “sticky notes” you can attach to classes, properties, methods, or parameters to tell the container what you mean.

## What Belongs Here
Attributes like `Inject` and `Singleton` that change how the container analyzes or treats code.

### For Humans: What This Means (Belongs)
If it’s an attribute that the container reads during reflection, it belongs here.

## What Does NOT Belong Here
Non-container attributes or application-level annotations.

### For Humans: What This Means (Not Belongs)
Keep this folder focused on attributes the container itself understands.

## How Files Collaborate
Prototype analyzers and injectors scan your code for these attributes, then use them to decide where to inject dependencies and what lifetime rules to apply.

### For Humans: What This Means (Collaboration)
Other parts of the container read these markers and change behavior accordingly.
