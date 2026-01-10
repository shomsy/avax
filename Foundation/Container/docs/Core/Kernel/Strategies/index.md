# Strategies

## What This Folder Represents
Lifecycle strategy implementations that decide how resolved services are cached or not (singleton, scoped, transient). They exist to give the kernel pluggable lifecycle policies.

### For Humans: What This Means (Represent)
These files set the rules for whether services are kept, scoped, or rebuilt every time.

## What Belongs Here
Concrete lifecycle strategies (`SingletonLifecycleStrategy`, `ScopedLifecycleStrategy`, `TransientLifecycleStrategy`) that implement caching policies.

### For Humans: What This Means (Belongs)
Only the specific caching behaviors live here—one for global reuse, one for scoped reuse, one for no reuse.

## What Does NOT Belong Here
General kernel logic, pipeline steps, or contracts (they belong in their folders). Non-lifecycle helpers should stay out.

### For Humans: What This Means (Not Belongs)
Only caching policy implementations go here, not other kernel pieces.

## How Files Collaborate
All strategies implement `LifecycleStrategy` so the kernel can swap them per service lifetime. `ScopeManager` backs singleton and scoped storage; transient is stateless.

### For Humans: What This Means (Collaboration)
The kernel plugs in one of these policies based on lifetime; scoped/singleton use the scope manager, transient skips storage.
